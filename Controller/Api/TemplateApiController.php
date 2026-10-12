<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use Mautic\ApiBundle\Controller\CommonApiController;
use Mautic\ApiBundle\Helper\EntityResultHelper;
use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\CoreBundle\Helper\AppVersion;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplate;
use MauticPlugin\MauticWhatsAppBundle\Model\WhatsAppTemplateModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<WhatsAppTemplate>
 */
final class TemplateApiController extends CommonApiController
{
    protected $model;

    public function __construct(
        CorePermissions $security,
        Translator $translator,
        EntityResultHelper $entityResultHelper,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        AppVersion $appVersion,
        RequestStack $requestStack,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        EventDispatcherInterface $dispatcher,
        CoreParametersHelper $coreParametersHelper,
        WhatsAppTemplateModel $model,
        private readonly LeadModel $leadModel,
    ) {
        $this->model            = $model;
        $this->entityClass      = WhatsAppTemplate::class;
        $this->entityNameOne    = 'whatsappTemplate';
        $this->entityNameMulti  = 'whatsappTemplates';
        $this->permissionBase   = 'whatsapp:templates';
        $this->serializerGroups = ['whatsappTemplateDetails', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    public function submitAction($id): Response
    {
        $entity = $this->editableTemplate($id);
        if ($entity instanceof Response) {
            return $entity;
        }

        return $this->metaResponse(fn () => $this->model->submit($entity));
    }

    public function syncAction($id): Response
    {
        $entity = $this->editableTemplate($id);
        if ($entity instanceof Response) {
            return $entity;
        }

        return $this->metaResponse(fn () => $this->model->sync($entity));
    }

    public function sendAction(Request $request, $id): Response
    {
        $entity = $this->model->getEntity($id);
        if (!$entity || !$entity->getId() || !$this->checkEntityAccess($entity, 'view')) {
            return $this->accessDenied();
        }

        $payload    = json_decode($request->getContent(), true);
        $payload    = is_array($payload) ? $payload : [];
        $parameters = array_values(array_map('strval', $payload['parameters'] ?? []));
        $contact    = null;
        $to         = (string) ($payload['to'] ?? '');

        if (!empty($payload['contactId'])) {
            $contact = $this->leadModel->getEntity((int) $payload['contactId']);
            if (null === $contact || !$contact->getId()) {
                return $this->notFound('Contact not found.');
            }
            if ('' === $to) {
                $fields = $contact->getProfileFields();
                $to     = (string) ($fields['mobile'] ?? $fields['phone'] ?? '');
            }
        }

        try {
            $result = $this->model->send($entity, $to, $parameters, $contact);
        } catch (\Throwable $exception) {
            return $this->returnError($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $view = $this->view([
            'success' => true,
            'wamid'   => $result['wamid'],
            'status'  => $result['status'],
            'to'      => preg_replace('/\D+/', '', $to),
        ], Response::HTTP_OK);

        return $this->handleView($view);
    }

    private function editableTemplate($id): WhatsAppTemplate|Response
    {
        $entity = $this->model->getEntity($id);
        if (!$entity || !$entity->getId()) {
            return $this->notFound();
        }
        if (!$this->checkEntityAccess($entity, 'edit')) {
            return $this->accessDenied();
        }

        return $entity;
    }

    /**
     * @param callable(): WhatsAppTemplate $operation
     */
    private function metaResponse(callable $operation): Response
    {
        try {
            $entity = $operation();
        } catch (\Throwable $exception) {
            return $this->returnError($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $view = $this->view([
            'whatsappTemplate' => $entity,
        ], Response::HTTP_OK);

        return $this->handleView($view);
    }
}
