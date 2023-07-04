<?php

namespace App\Form\Project;

use App\Entity\ProjectDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $Project = $builder->getData();
        $dateOption = ['widget' => 'single_text'];
        if (!$Project->getId()) {
            $dateOption['data'] = new \DateTime('now');
        }
        $builder
            ->add('Name')
            ->add('Description')
            ->add('StartDate', DateType::class, $dateOption)
            ->add('EndDate', DateType::class, $dateOption)
            // ->add('StartDate', DateType::class, ['data' => new \DateTime('now')])
            // ->add('EndDate', DateType::class, ['data' => new \DateTime('+1day')])
            ->add('Status', ChoiceType::class, [
                'choices'  => [
                    ProjectDetails::PROJECT_STATUS_OPEN => ProjectDetails::PROJECT_STATUS_OPEN,
                    ProjectDetails::PROJECT_STATUS_IN_PROGRESS => ProjectDetails::PROJECT_STATUS_IN_PROGRESS,
                    ProjectDetails::PROJECT_STATUS_COMPLETED => ProjectDetails::PROJECT_STATUS_COMPLETED,
                    ProjectDetails::PROJECT_STATUS_ON_HOLD => ProjectDetails::PROJECT_STATUS_ON_HOLD,

                ],
            ])
            ->add('projectAttenchment', FileType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectDetails::class,
        ]);
    }
}
