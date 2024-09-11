<?php

namespace App\Form;

use App\Entity\Posts;
use App\Entity\ListPosts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;



class ListPostsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
                'attr' => [
                    'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'max 170 caractères',
                    'maxlength' => '170',
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'max 5000 caractères',
                    'maxlength' => '5000',
                ]
            ])
            ->add('linkSubtitle', TextType::class, [
                'label' => 'Sous-titre du lien',
                'required' => false,
                'attr' => [
                    'class' => 'font-black block p-2.5 w-full text-lg  bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'Sous-titre du lien (max 255 caractères)',
                    'maxlength' => '255',
                    ]
        ])
        ->add('linkPostSelect', EntityType::class, [
                'class' => Posts::class,
                'choice_label' => 'title',
                'label' => 'Lien vers un autre article',
                'required' => false,
                'choice_value' => 'id',
                'placeholder' => 'Choisir un article',
                'attr' => [
                    'class' => 'font-bold'
                    ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ListPosts::class,
        ]);
    }
}
