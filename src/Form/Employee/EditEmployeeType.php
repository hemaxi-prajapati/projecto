<?php

namespace App\Form\Employee;

use App\Entity\Department;
use App\Entity\User;
use Doctrine\DBAL\Types\SimpleArrayType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditEmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    User::ROLE_PROJECT_MANAGER => User::ROLE_PROJECT_MANAGER,
                    User::ROLE_USER => User::ROLE_USER,
                    User::ROLE_TEAM_MANAGER => User::ROLE_TEAM_MANAGER,
                ],
                'multiple' => true,
            ])
            ->add('Firstname')
            ->add('LastName', null,)
            ->add('ContactNumber')
            ->add('exprience')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    User::USER_STATUS_ACTIIVE => User::USER_STATUS_ACTIIVE,
                    User::USER_STATUS_INACTIIVE => User::USER_STATUS_INACTIIVE
                ],
            ])
            ->add('Department', EntityType::class, [
                'class' => Department::class
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
