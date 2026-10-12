<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use MauticPlugin\MauticWhatsAppBundle\Form\Type\ConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConfigSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event): void
    {
        $event->addForm([
            'bundle'     => 'MauticWhatsAppBundle',
            'formAlias'  => 'whatsappconfig',
            'formType'   => ConfigType::class,
            'formTheme'  => '@MauticWhatsApp/FormTheme/Config/_config_whatsappconfig_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('MauticWhatsAppBundle'),
        ]);
    }
}
