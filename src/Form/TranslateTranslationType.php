<?php

namespace App\Form;

use App\Entity\Translate;
use App\Entity\TranslateTranslation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslateTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translation')
            ->add('locale')
            // ->add('translate', EntityType::class, [
            //     'class' => Translate::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TranslateTranslation::class,
        ]);
    }
}
