<?php

namespace App\Form\Task;

use App\Entity\TaskWithProject;
use App\Form\AddUserToTAskType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $Task = $builder->getData();
        $dateOption = ['widget' => 'single_text'];
        if (!$Task->getId()) {
            $dateOption['data'] = new \DateTime('now');
        }
        $builder
            ->add('Title')
            ->add('Description')
            ->add('Priority', ChoiceType::class, [
                'choices'  => [
                    TaskWithProject::TASK_PRIORITY_HIGH => TaskWithProject::TASK_PRIORITY_HIGH,
                    TaskWithProject::TASK_PRIORITY_LOW => TaskWithProject::TASK_PRIORITY_LOW,
                    TaskWithProject::TASK_PRIORITY_MEDIUM => TaskWithProject::TASK_PRIORITY_MEDIUM,
                ],
            ])
            ->add('Status', ChoiceType::class, [
                'choices'  => [
                    TaskWithProject::TASK_STATUS_OPEN => TaskWithProject::TASK_STATUS_OPEN,
                    TaskWithProject::TASK_STATUS_IN_PROGRESS => TaskWithProject::TASK_STATUS_IN_PROGRESS,
                    TaskWithProject::TASK_STATUS_ON_HOLD => TaskWithProject::TASK_STATUS_ON_HOLD,
                    TaskWithProject::TASK_STATUS_COMPLETED => TaskWithProject::TASK_STATUS_COMPLETED,


                ],
            ])
            // ->add('ActualStartDate',DateType::class,['data' => new \DateTime('now')])
            // ->add('ActualEndDate',DateType::class,['data' => new \DateTime('+1day')])
            ->add('ActualStartDate', DateType::class, $dateOption)
            ->add('ActualEndDate', DateType::class, $dateOption);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskWithProject::class,
        ]);
    }
}
