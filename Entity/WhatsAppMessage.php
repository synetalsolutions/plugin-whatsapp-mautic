<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

class WhatsAppMessage
{
    public const TABLE_NAME = 'whatsapp_messages';

    private ?int $id = null;

    private ?int $templateId = null;

    private ?int $contactId = null;

    private ?string $phone = null;

    private ?string $wamid = null;

    private string $status = 'accepted';

    private ?string $error = null;

    private ?\DateTimeInterface $dateSent = null;

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(self::TABLE_NAME)
            ->setCustomRepositoryClass(WhatsAppMessageRepository::class)
            ->addIndex(['wamid'], 'whatsapp_message_wamid')
            ->addIndex(['template_id'], 'whatsapp_message_template');

        $builder->addId();

        $builder->createField('templateId', Types::INTEGER)
            ->columnName('template_id')
            ->nullable()
            ->build();

        $builder->createField('contactId', Types::INTEGER)
            ->columnName('contact_id')
            ->nullable()
            ->build();

        $builder->createField('phone', Types::STRING)
            ->length(32)
            ->nullable()
            ->build();

        $builder->createField('wamid', Types::STRING)
            ->length(255)
            ->nullable()
            ->build();

        $builder->createField('status', Types::STRING)
            ->length(32)
            ->build();

        $builder->createField('error', Types::TEXT)
            ->nullable()
            ->build();

        $builder->createField('dateSent', Types::DATETIME_MUTABLE)
            ->columnName('date_sent')
            ->nullable()
            ->build();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    public function setTemplateId(?int $templateId): self
    {
        $this->templateId = $templateId;

        return $this;
    }

    public function getContactId(): ?int
    {
        return $this->contactId;
    }

    public function setContactId(?int $contactId): self
    {
        $this->contactId = $contactId;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWamid(): ?string
    {
        return $this->wamid;
    }

    public function setWamid(?string $wamid): self
    {
        $this->wamid = $wamid;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getDateSent(): ?\DateTimeInterface
    {
        return $this->dateSent;
    }

    public function setDateSent(?\DateTimeInterface $dateSent): self
    {
        $this->dateSent = $dateSent;

        return $this;
    }
}
