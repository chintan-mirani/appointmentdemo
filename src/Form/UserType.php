<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback as ConstraintsCallback;

class UserType extends AbstractType
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'First name is required.'])
                ]
            ])
            ->add('lastName', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Last name is required.'])
                ]
            ])
            ->add('email', EmailType::class, [
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(['message' => 'Email is required.']),
                    new Email(['message' => 'Email is not a valid.']),
                    new ConstraintsCallback([$this, 'emailValidation']),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * @param ExecutionContextInterface $context
     * @param $endTime
     */
    public function emailValidation($email, ExecutionContextInterface $context)
    {
        $formData = $context->getRoot()->getViewData();
        $userId = $formData->getId();

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return false;
        }

        if ($userId) {
    
            if ($user->getId() == $userId) {
                return false;
            }

            return  $context->buildViolation('This email is already exists.')
                            ->atPath('email')
                            ->addViolation();
        } else {
            
            return  $context->buildViolation('This email is already exists.')
                            ->atPath('email')
                            ->addViolation();
        }
    }
}
