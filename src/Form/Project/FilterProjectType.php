<?php

namespace App\Form\Project;

use App\Repository\ProjectAssignmentRepository;
use App\Repository\ProjectDetailsRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterProjectType extends AbstractType
{
    public ProjectAssignmentRepository $projectAssignmentRepository;
    public projectDetailsRepository $projectDetailsRepository;
    public function __construct(ProjectAssignmentRepository $projectAssignmentRepository, ProjectDetailsRepository $projectDetailsRepository)
    {
        $this->projectAssignmentRepository = $projectAssignmentRepository;
        $this->projectDetailsRepository = $projectDetailsRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $projectsIdArray = $this->projectAssignmentRepository->findProjectByDepartmentQuerybuilder($options['departmetId'])
            ->getQuery()->getResult();

        $projects = array();
        foreach ($projectsIdArray as $projectId) {
            $projects[] = $this->projectDetailsRepository->find($projectId['projectId']);
        }
        $name = array('All' => "all");
        foreach ($projects as $project) {
            $name[$project->getProjectManager()->getFirstname()] = $project->getProjectManager()->getId();
        }
        $builder
            ->add('ProjectName', TextType::class, [])
            ->add('ProjectManager', ChoiceType::class, [
                'choices' => $name
            ])
            ->setMethod('GET')
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'departmetId'
        ]);
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}
