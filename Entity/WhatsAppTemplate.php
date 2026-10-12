<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class WhatsAppTemplate extends FormEntity
{
    public const ENTITY_NAME = 'whatsapp';

    public const TABLE_NAME = 'whatsapp_templates';

    public const STATUS_DRAFT    = 'DRAFT';
    public const STATUS_PENDING  = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    private ?int $id = null;

    private ?string $name = null;

    private ?string $description = null;

    private ?string $templateName = null;

    private string $language = 'en_US';

    private string $category = 'MARKETING';

    private ?string $headerText = null;

    private ?string $body = null;

    private ?string $footer = null;

    private ?string $buttonText = null;

    private ?string $buttonUrl = null;

    private ?string $examples = null;

    private ?string $metaId = null;

    private string $metaStatus = self::STATUS_DRAFT;

    private ?string $metaError = null;

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(self::TABLE_NAME)
            ->setCustomRepositoryClass(WhatsAppTemplateRepository::class);

        $builder->addIdColumns();

        $builder->createField('templateName', Types::STRING)
            ->columnName('template_name')
            ->length(512)
            ->nullable()
            ->build();

        $builder->createField('language', Types::STRING)
            ->length(16)
            ->build();

        $builder->createField('category', Types::STRING)
            ->length(32)
            ->build();

        $builder->createField('headerText', Types::STRING)
            ->columnName('header_text')
            ->length(60)
            ->nullable()
            ->build();

        $builder->createField('body', Types::TEXT)
            ->nullable()
            ->build();

        $builder->createField('footer', Types::STRING)
            ->length(60)
            ->nullable()
            ->build();

        $builder->createField('buttonText', Types::STRING)
            ->columnName('button_text')
            ->length(25)
            ->nullable()
            ->build();

        $builder->createField('buttonUrl', Types::STRING)
            ->columnName('button_url')
            ->length(2000)
            ->nullable()
            ->build();

        $builder->createField('examples', Types::TEXT)
            ->nullable()
            ->build();

        $builder->createField('metaId', Types::STRING)
            ->columnName('meta_id')
            ->length(64)
            ->nullable()
            ->build();

        $builder->createField('metaStatus', Types::STRING)
            ->columnName('meta_status')
            ->length(32)
            ->build();

        $builder->createField('metaError', Types::TEXT)
            ->columnName('meta_error')
            ->nullable()
            ->build();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('name', new NotBlank());
        $metadata->addPropertyConstraint('body', new NotBlank());
        $metadata->addPropertyConstraint('language', new NotBlank());
        $metadata->addPropertyConstraint('category', new Choice(['choices' => ['MARKETING', 'UTILITY']]));
    }

    public static function loadApiMetadata(ApiMetadataDriver $metadata): void
    {
        $metadata->setGroupPrefix('whatsappTemplate')
            ->addListProperties([
                'id',
                'name',
                'language',
                'category',
                'metaStatus',
            ])
            ->addProperties([
                'description',
                'templateName',
                'headerText',
                'body',
                'footer',
                'buttonText',
                'buttonUrl',
                'examples',
                'metaId',
                'metaError',
            ])
            ->build();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->isChanged('description', $description);
        $this->description = $description;

        return $this;
    }

    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(?string $templateName): self
    {
        $this->isChanged('templateName', $templateName);
        $this->templateName = $templateName;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->isChanged('language', $language);
        $this->language = $language;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        if ($this->category !== $category) {
            $this->addChange('category', [$this->category, $category]);
        }
        $this->category = $category;

        return $this;
    }

    public function getHeaderText(): ?string
    {
        return $this->headerText;
    }

    public function setHeaderText(?string $headerText): self
    {
        $this->isChanged('headerText', $headerText);
        $this->headerText = $headerText;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->isChanged('body', $body);
        $this->body = $body;

        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): self
    {
        $this->isChanged('footer', $footer);
        $this->footer = $footer;

        return $this;
    }

    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    public function setButtonText(?string $buttonText): self
    {
        $this->isChanged('buttonText', $buttonText);
        $this->buttonText = $buttonText;

        return $this;
    }

    public function getButtonUrl(): ?string
    {
        return $this->buttonUrl;
    }

    public function setButtonUrl(?string $buttonUrl): self
    {
        $this->isChanged('buttonUrl', $buttonUrl);
        $this->buttonUrl = $buttonUrl;

        return $this;
    }

    public function getExamples(): ?string
    {
        return $this->examples;
    }

    public function setExamples(?string $examples): self
    {
        $this->isChanged('examples', $examples);
        $this->examples = $examples;

        return $this;
    }

    public function getMetaId(): ?string
    {
        return $this->metaId;
    }

    public function setMetaId(?string $metaId): self
    {
        $this->isChanged('metaId', $metaId);
        $this->metaId = $metaId;

        return $this;
    }

    public function getMetaStatus(): string
    {
        return $this->metaStatus;
    }

    public function setMetaStatus(string $metaStatus): self
    {
        $this->isChanged('metaStatus', $metaStatus);
        $this->metaStatus = $metaStatus;

        return $this;
    }

    public function getMetaError(): ?string
    {
        return $this->metaError;
    }

    public function setMetaError(?string $metaError): self
    {
        $this->isChanged('metaError', $metaError);
        $this->metaError = $metaError;

        return $this;
    }
}
