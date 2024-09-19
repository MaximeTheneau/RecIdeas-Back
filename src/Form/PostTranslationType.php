<?php

namespace App\Form;

use App\Entity\PostsTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PostTranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('locale', TextType::class, [
                'label' => 'Language',
                'disabled' => true, // locale should not be editable by the user
            ])
            ->add('heading', TextType::class, [
                'label' => 'Heading',
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
            ])
            ->add('metaDescription', TextareaType::class, [
                'label' => 'Meta Description',
            ])
            ->add('contents', TextareaType::class, [
                'label' => 'Contents',
            ])

            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostsTranslation::class,
        ]);
    }
}
