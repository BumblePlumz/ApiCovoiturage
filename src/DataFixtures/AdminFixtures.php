<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Personne;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private $passwordHasher;
    
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $user = new Personne();
        $user->setPseudo('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin'));
        $user->setIsActif(true);

        $manager->persist($user);
        $manager->flush();
    }
}