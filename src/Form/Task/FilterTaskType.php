<?php

namespace App\Form\Task;

use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use App\Repository\TaskWithProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterTaskType extends AbstractType
{

    public TaskWithProjectRepository $TaskWithProjectRepository;
    public projectDetailsRepository $projectDetailsRepository;
    public ProjectAssignmentRepository $ProjectAssignmentRepository;
    public function __construct(TaskWithProjectRepository $TaskWithProjectRepository, ProjectDetailsRepository $projectDetailsRepository, ProjectAssignmentRepository $projectAssignmentRepository)
    {
        $this->TaskWithProjectRepository = $TaskWithProjectRepository;
        $this->projectDetailsRepository = $projectDetailsRepository;
        $this->ProjectAssignmentRepository = $projectAssignmentRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('searchByProjectName', TextType::class, [
                'required' => false
            ])
            ->add('searchByEmployee', TextType::class, [
                'required' => false

            ])
            ->setMethod('GET');
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
