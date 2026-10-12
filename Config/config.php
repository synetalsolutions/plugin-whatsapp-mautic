<?php

declare(strict_types=1);

return [
    'name'        => 'WhatsApp',
    'description' => 'Create WhatsApp templates like emails and manage them through the API and Meta.',
    'version'     => '1.0.0',
    'author'      => 'Synetal Solutions',

    'routes' => [
        'main' => [
            'mautic_whatsapp_index' => [
                'path'       => '/whatsapp/{page}',
                'controller' => 'MauticPlugin\MauticWhatsAppBundle\Controller\TemplateController::indexAction',
            ],
            'mautic_whatsapp_action' => [
                'path'       => '/whatsapp/{objectAction}/{objectId}',
                'controller' => 'MauticPlugin\MauticWhatsAppBundle\Controller\TemplateController::executeAction',
            ],
        ],
        'public' => [
            'mautic_whatsapp_webhook' => [
                'path'       => '/whatsapp/webhook',
                'controller' => 'MauticPlugin\MauticWhatsAppBundle\Controller\PublicController::webhookAction',
            ],
        ],
        'api' => [
            'mautic_api_whatsapptemplates_standard' => [
                'standard_entity' => true,
                'name'            => 'whatsapptemplates',
                'path'            => '/whatsapptemplates',
                'controller'      => MauticPlugin\MauticWhatsAppBundle\Controller\Api\TemplateApiController::class,
            ],
            'mautic_api_whatsapptemplates_submit' => [
                'path'       => '/whatsapptemplates/{id}/submit',
                'controller' => 'MauticPlugin\MauticWhatsAppBundle\Controller\Api\TemplateApiController::submitAction',
                'method'     => 'POST',
            ],
            'mautic_api_whatsapptemplates_sync' => [
                'path'       => '/whatsapptemplates/{id}/sync',
                'controller' => 'MauticPlugin\MauticWhatsAppBundle\Controller\Api\TemplateApiController::syncAction',
                'method'     => 'POST',
            ],
            'mautic_api_whatsapptemplates_send' => [
                'path'       => '/whatsapptemplates/{id}/send',
                'controller' => 'MauticPlugin\MauticWhatsAppBundle\Controller\Api\TemplateApiController::sendAction',
                'method'     => 'POST',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'mautic.whatsapp.templates' => [
                    'route'    => 'mautic_whatsapp_index',
                    'access'   => ['whatsapp:templates:viewown', 'whatsapp:templates:viewother'],
                    'parent'   => 'mautic.core.channels',
                    'priority' => 65,
                ],
            ],
        ],
    ],

    'parameters' => [
        'whatsapp_enabled'              => false,
        'whatsapp_access_token'         => '',
        'whatsapp_waba_id'              => '',
        'whatsapp_phone_number_id'      => '',
        'whatsapp_api_version'          => 'v23.0',
        'whatsapp_webhook_verify_token' => '',
        'whatsapp_app_secret'           => '',
    ],
];
