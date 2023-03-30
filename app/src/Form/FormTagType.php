<?php

namespace App\Form;

use App\Entity\Tag;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class FormTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tags', EntityType::class, [
            'class' => Tag::class,
            'choice_label' => 'label',
            'multiple' => true,
            'expanded' => true,
            'label' => 'Les tags',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Tag::class,
        ));
    }
}