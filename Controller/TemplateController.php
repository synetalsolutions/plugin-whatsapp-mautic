<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use MauticPlugin\MauticWhatsAppBundle\Model\WhatsAppTemplateModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TemplateController extends AbstractStandardFormController
{
    protected function getModelName(): string
    {
        return 'whatsapp';
    }

    protected function getTemplateBase(): string
    {
        return '@MauticWhatsApp/Template';
    }

    public function indexAction(Request $request, $page = 1): Response
    {
        return parent::indexStandard($request, $page);
    }

    public function newAction(Request $request): Response
    {
        return parent::newStandard($request);
    }

    public function editAction(Request $request, $objectId, $ignorePost = false): Response
    {
        return parent::editStandard($request, $objectId, $ignorePost);
    }

    public function viewAction(Request $request, $objectId): Response
    {
        return parent::viewStandard($request, $objectId, null, null, null, 'item');
    }

    public function deleteAction(Request $request, $objectId): Response
    {
        return parent::deleteStandard($request, $objectId);
    }

    public function batchDeleteAction(Request $request): Response
    {
        return parent::batchDeleteStandard($request);
    }

    public function submitAction(Request $request, $objectId): Response
    {
        return $this->runMetaAction($request, (int) $objectId, 'submit');
    }

    public function syncAction(Request $request, $objectId): Response
    {
        return $this->runMetaAction($request, (int) $objectId, 'sync');
    }

    private function runMetaAction(Request $request, int $objectId, string $action): Response
    {
        $model  = $this->whatsAppModel();
        $entity = $model->getEntity($objectId);
        $page   = $request->getSession()->get('mautic.whatsapp.page', 1);

        if (null === $entity || !$entity->getId()) {
            return $this->postActionRedirect([
                'returnUrl'       => $this->generateUrl('mautic_whatsapp_index', ['page' => $page]),
                'viewParameters'  => ['page' => $page],
                'contentTemplate' => 'MauticPlugin\MauticWhatsAppBundle\Controller\TemplateController::indexAction',
                'passthroughVars' => ['mauticContent' => 'whatsapp'],
                'flashes'         => [['type' => 'error', 'msg' => 'mautic.whatsapp.not_found']],
            ]);
        }

        try {
            $action === 'submit' ? $model->submit($entity) : $model->sync($entity);
            $flash = ['type' => 'notice', 'msg' => 'mautic.whatsapp.'.$action.'.success', 'msgVars' => ['%status%' => $entity->getMetaStatus()]];
        } catch (\Throwable $exception) {
            $entity->setMetaError($exception->getMessage());
            $model->saveEntity($entity);
            $flash = ['type' => 'error', 'msg' => $exception->getMessage()];
        }

        return $this->postActionRedirect([
            'returnUrl'       => $this->generateUrl('mautic_whatsapp_action', ['objectAction' => 'view', 'objectId' => $entity->getId()]),
            'viewParameters'  => ['objectId' => $entity->getId()],
            'contentTemplate' => 'MauticPlugin\MauticWhatsAppBundle\Controller\TemplateController::viewAction',
            'passthroughVars' => ['mauticContent' => 'whatsapp'],
            'flashes'         => [$flash],
        ]);
    }

    private function whatsAppModel(): WhatsAppTemplateModel
    {
        $model = $this->getModel('whatsapp');
        if (!$model instanceof WhatsAppTemplateModel) {
            throw new \RuntimeException('WhatsApp model is not available.');
        }

        return $model;
    }
}
