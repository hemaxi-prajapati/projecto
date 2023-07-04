<?php

namespace App\Form\Meeting;

use App\Entity\Meetings;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateMeetingType extends AbstractType
{
    public function __construct(private UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $users = $this->userRepository->findAllUserToJoinMeeting();
        $usersArray = array();
        foreach ($users as $user) {
            $usersArray[$user->getName()] = $user->getId();
        }
        $builder
            ->add('subject', TextType::class)
            ->add('meetingStartTime', DateTimeType::class, ['data' => new \DateTime('now')])
            ->add('meetingEndTime', DateTimeType::class, ['data' => new \DateTime('+1hour')])
            ->add('meetingAssign', ChoiceType::class, [
                'choices' => $usersArray,
                'mapped' => false

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meetings::class,
        ]);
    }
}
