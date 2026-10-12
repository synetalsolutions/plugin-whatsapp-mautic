<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
final class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ([
            'whatsapp_access_token'         => 'mautic.whatsapp.config.access_token',
            'whatsapp_waba_id'              => 'mautic.whatsapp.config.waba_id',
            'whatsapp_phone_number_id'      => 'mautic.whatsapp.config.phone_number_id',
            'whatsapp_api_version'          => 'mautic.whatsapp.config.api_version',
            'whatsapp_webhook_verify_token' => 'mautic.whatsapp.config.verify_token',
            'whatsapp_app_secret'           => 'mautic.whatsapp.config.app_secret',
        ] as $field => $label) {
            $builder->add($field, TextType::class, [
                'label'      => $label,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'whatsappconfig';
    }
}
