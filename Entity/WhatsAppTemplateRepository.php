<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<WhatsAppTemplate>
 */
class WhatsAppTemplateRepository extends CommonRepository
{
    public function getTableAlias(): string
    {
        return 'e';
    }

    protected function getDefaultOrder(): array
    {
        return ['e.name' => 'ASC'];
    }
}
