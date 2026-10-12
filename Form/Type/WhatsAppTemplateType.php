<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Form\Type;

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<WhatsAppTemplate>
 */
final class WhatsAppTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber([
            'name'         => 'string',
            'templateName' => 'string',
            'language'     => 'string',
            'headerText'   => 'string',
            'body'         => 'string',
            'footer'       => 'string',
            'buttonText'   => 'string',
            'buttonUrl'    => 'string',
            'examples'     => 'string',
            'description'  => 'string',
        ]));

        $builder->add('name', TextType::class, [
            'label'      => 'mautic.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'mautic.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control', 'rows' => 2],
            'required'   => false,
        ]);

        $builder->add('templateName', TextType::class, [
            'label'      => 'mautic.whatsapp.template_name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mautic.whatsapp.template_name.tooltip',
            ],
            'required' => false,
        ]);

        $builder->add('language', TextType::class, [
            'label'      => 'mautic.core.language',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control', 'placeholder' => 'en_US'],
        ]);

        $builder->add('category', ChoiceType::class, [
            'label'      => 'mautic.whatsapp.category',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'choices'    => [
                'mautic.whatsapp.category.marketing' => 'MARKETING',
                'mautic.whatsapp.category.utility'   => 'UTILITY',
            ],
        ]);

        $builder->add('headerText', TextType::class, [
            'label'      => 'mautic.whatsapp.header',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'required'   => false,
        ]);

        $builder->add('body', TextareaType::class, [
            'label'      => 'mautic.whatsapp.body',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'rows'    => 8,
                'tooltip' => 'mautic.whatsapp.body.tooltip',
            ],
        ]);

        $builder->add('examples', TextareaType::class, [
            'label'      => 'mautic.whatsapp.examples',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'rows'    => 3,
                'tooltip' => 'mautic.whatsapp.examples.tooltip',
            ],
            'required' => false,
        ]);

        $builder->add('footer', TextType::class, [
            'label'      => 'mautic.whatsapp.footer',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'required'   => false,
        ]);

        $builder->add('buttonText', TextType::class, [
            'label'      => 'mautic.whatsapp.button_text',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'required'   => false,
        ]);

        $builder->add('buttonUrl', TextType::class, [
            'label'      => 'mautic.whatsapp.button_url',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control', 'placeholder' => 'https://'],
            'required'   => false,
        ]);

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'label' => 'mautic.core.form.published',
        ]);

        $builder->add('buttons', FormButtonsType::class, [
            'apply_text' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WhatsAppTemplate::class,
        ]);
    }
}
