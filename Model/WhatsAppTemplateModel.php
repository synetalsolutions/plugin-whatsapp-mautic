<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppMessage;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppMessageRepository;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplate;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplateRepository;
use MauticPlugin\MauticWhatsAppBundle\Form\Type\WhatsAppTemplateType;
use MauticPlugin\MauticWhatsAppBundle\Service\GraphClient;
use MauticPlugin\MauticWhatsAppBundle\Service\TemplatePayloadFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends FormModel<WhatsAppTemplate>
 */
class WhatsAppTemplateModel extends FormModel
{
    public function __construct(
        EntityManagerInterface $em,
        CorePermissions $security,
        EventDispatcherInterface $dispatcher,
        UrlGeneratorInterface $router,
        Translator $translator,
        UserHelper $userHelper,
        LoggerInterface $mauticLogger,
        CoreParametersHelper $coreParametersHelper,
        private readonly WhatsAppTemplateRepository $templateRepository,
        private readonly WhatsAppMessageRepository $messageRepository,
        private readonly GraphClient $graphClient,
        private readonly TemplatePayloadFactory $payloadFactory,
    ) {
        parent::__construct($em, $security, $dispatcher, $router, $translator, $userHelper, $mauticLogger, $coreParametersHelper);
    }

    public function getRepository(): WhatsAppTemplateRepository
    {
        return $this->templateRepository;
    }

    public function getPermissionBase(): string
    {
        return 'whatsapp:templates';
    }

    public function getActionRouteBase(): string
    {
        return 'whatsapp';
    }

    public function getNameGetter(): string
    {
        return 'getName';
    }

    /**
     * @param WhatsAppTemplate $entity
     * @param array<string, mixed> $options
     */
    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): FormInterface
    {
        if (!$action) {
            $action = $this->router->generate(
                'mautic_whatsapp_action',
                [
                    'objectAction' => $entity->getId() ? 'edit' : 'new',
                    'objectId'     => $entity->getId(),
                ]
            );
        }

        return $formFactory->create(WhatsAppTemplateType::class, $entity, ['action' => $action] + $options);
    }

    public function getEntity($id = null): ?object
    {
        if (null === $id) {
            return new WhatsAppTemplate();
        }

        return parent::getEntity($id);
    }

    /**
     * @param WhatsAppTemplate $entity
     */
    public function saveEntity($entity, $unlock = true): void
    {
        $entity->setTemplateName($this->normalizeTemplateName($entity->getTemplateName() ?: $entity->getName()));
        parent::saveEntity($entity, $unlock);
    }

    public function submit(WhatsAppTemplate $template): WhatsAppTemplate
    {
        $response = $this->graphClient->submitTemplate($template);
        if (isset($response['id'])) {
            $template->setMetaId((string) $response['id']);
        }
        $status = strtoupper((string) ($response['status'] ?? WhatsAppTemplate::STATUS_PENDING));
        $template->setMetaStatus('' === $status ? WhatsAppTemplate::STATUS_PENDING : $status);
        $template->setMetaError(null);
        $this->saveEntity($template);

        return $template;
    }

    public function sync(WhatsAppTemplate $template): WhatsAppTemplate
    {
        if (null === $template->getMetaId() || '' === $template->getMetaId()) {
            throw new \RuntimeException('This template has not been submitted to WhatsApp yet.');
        }

        $response = $this->graphClient->fetchTemplate($template->getMetaId());
        if (isset($response['status'])) {
            $template->setMetaStatus(strtoupper((string) $response['status']));
        }
        $template->setMetaError(isset($response['rejected_reason']) ? (string) $response['rejected_reason'] : null);
        $this->saveEntity($template);

        return $template;
    }

    /**
     * @param list<string> $parameters
     *
     * @return array{success: bool, wamid: ?string, status: string, message: WhatsAppMessage}
     */
    public function send(WhatsAppTemplate $template, string $to, array $parameters, ?Lead $contact = null): array
    {
        if (WhatsAppTemplate::STATUS_APPROVED !== $template->getMetaStatus()) {
            throw new \RuntimeException('Only an APPROVED WhatsApp template can be sent. Current status is '.$template->getMetaStatus().'.');
        }

        $to = preg_replace('/\D+/', '', $to) ?? '';
        if ('' === $to) {
            throw new \RuntimeException('A recipient phone number is required.');
        }

        if ([] === $parameters && null !== $contact) {
            $parameters = $this->parametersFromContact($template, $contact);
        }

        $response = $this->graphClient->sendTemplate($template, $to, $parameters);
        $wamid    = $response['messages'][0]['id'] ?? null;

        $message = new WhatsAppMessage();
        $message->setTemplateId($template->getId());
        $contactId = $contact?->getId();
        $message->setContactId(null !== $contactId ? (int) $contactId : null);
        $message->setPhone($to);
        $message->setWamid(is_string($wamid) ? $wamid : null);
        $message->setStatus('accepted');
        $message->setDateSent(new \DateTime());
        $this->em->persist($message);
        $this->em->flush();

        return [
            'success' => true,
            'wamid'   => $message->getWamid(),
            'status'  => $message->getStatus(),
            'message' => $message,
        ];
    }

    public function normalizeTemplateName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9_]+/', '_', $name) ?? '';
        $name = trim($name, '_');

        return '' === $name ? 'template' : substr($name, 0, 512);
    }

    /**
     * @return list<string>
     */
    private function parametersFromContact(WhatsAppTemplate $template, Lead $contact): array
    {
        $built = $this->payloadFactory->build($template);
        $out   = [];
        foreach ($built['tokens'] as $token) {
            $out[] = $this->resolveToken($token, $contact);
        }

        return $out;
    }

    private function resolveToken(string $token, Lead $contact): string
    {
        $default = '';
        if (str_contains($token, '|')) {
            [$token, $modifier] = explode('|', $token, 2);
            if (str_starts_with($modifier, 'default=')) {
                $default = substr($modifier, 8);
            }
        }

        $alias = $token;
        if (str_starts_with($token, 'contactfield=')) {
            $alias = substr($token, 13);
        }

        $profile = $contact->getProfileFields();
        $value   = $profile[$alias] ?? null;
        if (null === $value || '' === (string) $value) {
            return $default;
        }

        return (string) $value;
    }
}
