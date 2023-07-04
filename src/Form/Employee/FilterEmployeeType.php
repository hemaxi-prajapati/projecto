<?php

namespace App\Form\Employee;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterEmployeeType extends AbstractType
{
 
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('searchBy', ChoiceType::class, [
                'choices' => [
                    'Name' => 'name',
                    'Email' => 'email'
                    ]
            ])
            ->add('value',TextType::class,[

            ])
            ->setMethod('GET')
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
