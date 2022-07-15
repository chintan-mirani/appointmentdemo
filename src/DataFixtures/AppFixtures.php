<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    protected $faker;
    protected $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();

        $user->setFirstName('chintan');
        $user->setLastName('mirani');
        $user->setEmail('chintanmirani12#@gmail.com');
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'chintan12#'
        ));
        $user->updatedTimestamps();
        $manager->persist($user);

        $this->faker = Factory::create();
        
        for ($i = 0; $i < 13; $i++) {
            $user = new User();

            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setEmail($this->faker->safeEmail);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'password12#'
            ));
            $user->updatedTimestamps();
            $manager->persist($user);
        }

        $manager->flush();
    }
}
