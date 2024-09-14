<?php

namespace App\Form;

use App\Entity\Translate;
use App\Form\TranslateTranslationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TranslateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('translate')
            ->add('translateTranslations', CollectionType::class, [
                'entry_type' => TranslateTranslationType::class,
                'allow_add' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => false,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Translate::class,
        ]);
    }
}
