<?php

namespace App\DataFixtures;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\SoundSystem;
use App\Entity\Member;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager) : void
    {
        $this->loadUsers($manager);
    }

    private function loadUsers(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$email,$plainPassword,$role,$name]) {
            $user = new Member();
            $password = $this->hasher->hashPassword($user, $plainPassword);
            $user->setEmail($email);
            $user->setName($name);
            $user->setPassword($password);

            $roles = array();
            $roles[] = $role;
            $user->setRoles($roles);

            $soundSystem = new SoundSystem();
            $soundSystem->setDescription('La collection de subs de ' . $name);
            $user->setSoundSystem($soundSystem);

            $manager->persist($soundSystem);
            $manager->persist($user);
        }
        $manager->flush();
    }
    private function getUserData()
    {
        yield [
            'chris@localhost',
            'chris',
            'ROLE_USER',
            'Chris'
        ];
        yield [
            'anna@localhost',
            'anna',
            'ROLE_ADMIN',
            'Anna'
        ];
    }
}
