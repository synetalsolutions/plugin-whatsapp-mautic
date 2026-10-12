<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Security\Permissions;

use Mautic\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

final class WhatsAppPermissions extends AbstractPermissions
{
    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->addExtendedPermissions('templates');
    }

    public function getName(): string
    {
        return 'whatsapp';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data): void
    {
        $this->addExtendedFormFields('whatsapp', 'templates', $builder, $data);
    }
}
