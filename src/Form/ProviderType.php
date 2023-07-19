<?php

namespace App\Form;

use App\Entity\Provider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProviderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 30
                ],
            ])
            ->add('rfc', TextType::class, [
                'label' => 'RFC',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Dirección',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 80
                ],
            ])
            ->add('cp', TextType::class, [
                'label' => 'Código Postal',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 5,
                    'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                ],
            ])
            ->add('state', TextType::class, [
                'label' => 'Estado',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ciudad',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 20
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Provider::class,
        ]);
    }
}
