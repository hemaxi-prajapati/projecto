<?php

namespace App\Form\GlobleTimer;

use Doctrine\DBAL\Types\DateType as TypesDateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFilterInGlobleTimer extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('from', DateType::class, [
            //     'data' => new \DateTime('-1day'),
            // ])
            ->add('from', DateType::class, [
                'data' => new \DateTime('-1day'),
                'widget'=>'single_text'
                ])
            ->add('to', DateType::class, ['widget'=>'single_text','data' => new \DateTime('now')])
            ->setMethod('GET');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
