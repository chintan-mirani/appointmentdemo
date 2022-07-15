<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\AppointmentUser;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback as ConstraintsCallback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AppointmentType extends AbstractType
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Name is required.'])
                ]
            ])
            ->add('date', DateType::class, [
                'empty_data' => true,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Date is required.']),
                ]
            ])
            ->add('startTime', TimeType::class, [
                'empty_data' => true,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Start time is required.']),
                    new ConstraintsCallback([$this, 'startTimeValidation']),
                ]
            ])
            ->add('endTime', TimeType::class, [
                'empty_data' => true,
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'End time is required.']),
                    new ConstraintsCallback([$this, 'startTimeEndTimeValidation']),
                ]
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'mapped' => false,
                'expanded'  => false,
                'multiple'  => true,
                'choice_label' => function(User $user) {
                    return $user->getFullName();
                },
                'data' => $options['existingUsers'],
                'required' => false,
                'constraints' => [
                    new ConstraintsCallback([$this, 'usersValidation']),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
            'existingUsers' => ''
        ]);
    }

    /**
     * @param ExecutionContextInterface $context
     * @param $endTime
     */
    public function startTimeValidation($startTime, ExecutionContextInterface $context)
    {        
        $formData = $context->getRoot()->getViewData();
        $appointmentDate = $formData->getDate();

        if (!isset($startTime) || !isset($appointmentDate)) 
        {
            return false;
        }

        $timeNow = new DateTime();
        
        if ($startTime->format('H:i') <= $timeNow->format('H:i') && $appointmentDate->format('Y-m-d') == $timeNow->format('Y-m-d')) {
            return $context->buildViolation('Please select current time above time.')
                    ->atPath('endTime')
                    ->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     * @param $endTime
     */
    public function startTimeEndTimeValidation($endTime, ExecutionContextInterface $context)
    {
        $formData = $context->getRoot()->getViewData();
        $appointmentStartTime = $formData->getStartTime();
        $appointmentEndTime = $formData->getEndTime();
        
        if (!$appointmentStartTime) 
        {
            return false;
        }
        
        if ($appointmentStartTime->format('a') == $appointmentEndTime->format('a')) {
            if ($appointmentStartTime->format('H:i:s') >= $appointmentEndTime->format('H:i:s')) {
                return $context->buildViolation('Please select valid end time.')
                        ->atPath('endTime')
                        ->addViolation();
            }
        }
    }
    
    /**
     * @param ExecutionContextInterface $context
     * @param $users 
     */
    public function usersValidation($users, ExecutionContextInterface $context)
    {  
        $usersId = [];
        $formData = $context->getRoot()->getViewData();
        $appointmentStartTime = $formData->getStartTime();
        $appointmentEndTime = $formData->getEndTime();
        $appointmentDate = $formData->getDate();
        $appointmentId = $formData->getId();

        if (!count($users)) {
    
            return  $context->buildViolation('User is required.')
                            ->atPath('users')
                            ->addViolation();
        }

        if (!$appointmentDate || !isset($appointmentStartTime) || !isset($appointmentEndTime)) 
        {
            return false;
        }

        foreach ($users as $user) {

            $usersId[] = $user->getId();
        }      
        
        if ($usersId) {
            
            $userName = [];

            $appointmentUsers = $this->entityManager->getRepository(AppointmentUser::class)->getSelectedUsersAppointment($usersId, $appointmentId);
            
            foreach ($appointmentUsers as $appointmentUser) {
                
                if ($appointmentUser->getAppointment()->getDate()->format('Y-m-d') == $appointmentDate->format('Y-m-d'))  
                {
                    if (($appointmentStartTime->format('H:i:s') >= $appointmentUser->getAppointment()->getStartTime()->format('H:i:s') && $appointmentStartTime->format('H:i:s') <= $appointmentUser->getAppointment()->getEndTime()->format('H:i:s')) || ($appointmentEndTime->format('H:i:s') >= $appointmentUser->getAppointment()->getStartTime()->format('H:i:s') && $appointmentEndTime->format('H:i:s') <= $appointmentUser->getAppointment()->getEndTime()->format('H:i:s')))
                    {
                        $userName[$appointmentUser->getUser()->getId()] = $appointmentUser->getUser()->getFullName();
                    }
                }
            }
            
            if ($userName) {
                $context->buildViolation(implode(', ', $userName).(count($userName) > 1 ? '  are' : ' is').' occupied.')
                        ->atPath('users')
                        ->addViolation();
            }
        }
    }
}
