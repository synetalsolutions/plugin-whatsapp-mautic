<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<WhatsAppMessage>
 */
class WhatsAppMessageRepository extends CommonRepository
{
    public function findOneByWamid(string $wamid): ?WhatsAppMessage
    {
        return $this->findOneBy(['wamid' => $wamid]);
    }
}
