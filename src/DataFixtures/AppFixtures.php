<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        UserFactory::new()
            ->withAttributes([
                'email' => 'importer.user@example.com',
                'plainPassword' => '1234',
                'firstName' => 'Importer',
                'lastName' => 'User',
                'avatar' => 'tisha.png',
                'roles' => [],
            ])
            ->create();

        UserFactory::new()
            ->withAttributes([
                'email' => 'munteanucalexandru@gmail.com',
                'plainPassword' => '1234',
                'firstName' => 'Alex',
                'lastName' => 'M',
            ])
            ->promoteRole('ROLE_SUPER_ADMIN')
            ->create();

        $manager->flush();
    }
}
