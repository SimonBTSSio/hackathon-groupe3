<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use App\Form\ReponseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextType::class, [
                'label' => 'Question',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Question'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de question',
                'choices' => [
                    'Unique' => 'unique',
                    'Multiple' => 'multiple',
                ],
                //by default, the first choice is selected
                'data' => 'unique',
                'expanded' => true,
            ])
            ->add('reponses', CollectionType::class, [
                'entry_type' => ReponseType::class,
                'allow_add' => true,
                'allow_delete' => false,
                'prototype' => true,
                'by_reference' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Ajouter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
