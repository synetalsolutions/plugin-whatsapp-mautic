<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Mautic\CoreBundle\Controller\CommonController;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Service\FlashBag;
use Mautic\CoreBundle\Translation\Translator;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppMessageRepository;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplateRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class PublicController extends CommonController
{
    public function __construct(
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        UserHelper $userHelper,
        CoreParametersHelper $coreParametersHelper,
        EventDispatcherInterface $dispatcher,
        Translator $translator,
        FlashBag $flashBag,
        RequestStack $requestStack,
        CorePermissions $security,
        private readonly WhatsAppTemplateRepository $templateRepository,
        private readonly WhatsAppMessageRepository $messageRepository,
    ) {
        parent::__construct($doctrine, $modelFactory, $userHelper, $coreParametersHelper, $dispatcher, $translator, $flashBag, $requestStack, $security);
    }

    public function webhookAction(Request $request): Response
    {
        if (Request::METHOD_GET === $request->getMethod()) {
            $verify    = (string) $this->coreParametersHelper->get('whatsapp_webhook_verify_token', '');
            $mode      = (string) ($request->query->get('hub_mode') ?? $request->query->get('hub.mode') ?? '');
            $token     = (string) ($request->query->get('hub_verify_token') ?? $request->query->get('hub.verify_token') ?? '');
            $challenge = (string) ($request->query->get('hub_challenge') ?? $request->query->get('hub.challenge') ?? '');
            if ('subscribe' === $mode && '' !== $verify && hash_equals($verify, $token)) {
                return new Response($challenge);
            }

            return new Response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $secret = (string) $this->coreParametersHelper->get('whatsapp_app_secret', '');
        $raw    = $request->getContent();
        if ('' !== $secret) {
            $expected = 'sha256='.hash_hmac('sha256', $raw, $secret);
            $given    = (string) $request->headers->get('X-Hub-Signature-256');
            if (!hash_equals($expected, $given)) {
                return new Response('Invalid signature', Response::HTTP_FORBIDDEN);
            }
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return new Response('OK');
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $field = $change['field'] ?? '';
                $value = $change['value'] ?? [];
                if ('messages' === $field) {
                    $this->applyStatuses($value['statuses'] ?? []);
                }
                if ('message_template_status_update' === $field) {
                    $this->applyTemplateStatus($value);
                }
            }
        }

        return new Response('OK');
    }

    /**
     * @param list<array<string, mixed>> $statuses
     */
    private function applyStatuses(array $statuses): void
    {
        foreach ($statuses as $status) {
            $wamid = (string) ($status['id'] ?? '');
            if ('' === $wamid) {
                continue;
            }
            $message = $this->messageRepository->findOneByWamid($wamid);
            if (null === $message) {
                continue;
            }
            $message->setStatus((string) ($status['status'] ?? $message->getStatus()));
            $error = $status['errors'][0]['title'] ?? $status['errors'][0]['message'] ?? null;
            if (null !== $error) {
                $message->setError((string) $error);
            }
        }
        $this->doctrine->getManager()->flush();
    }

    /**
     * @param array<string, mixed> $value
     */
    private function applyTemplateStatus(array $value): void
    {
        $metaId = (string) ($value['message_template_id'] ?? '');
        $event  = strtoupper((string) ($value['event'] ?? ''));
        if ('' === $metaId || '' === $event) {
            return;
        }

        $template = $this->templateRepository->findOneBy(['metaId' => $metaId]);
        if (null === $template) {
            return;
        }

        $template->setMetaStatus($event);
        if (isset($value['reason'])) {
            $template->setMetaError((string) $value['reason']);
        }
        $this->doctrine->getManager()->flush();
    }
}
