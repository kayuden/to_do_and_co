<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void //php bin/console doctrine:fixtures:load
    {
        $anonyme = $manager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
        if (!$anonyme) {
            $anonyme = new User();
            $anonyme->setUsername('anonyme');
            $anonyme->setEmail('anonyme@example.com');
            $anonyme->setRoles(['ROLE_USER']);
            $anonyme->setPassword($this->passwordHasher->hashPassword($anonyme, 'anonyme'));
            $manager->persist($anonyme);
        }

        $admin = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        if (!$admin) {
            $admin = new User();
            $admin->setUsername('admin');
            $admin->setEmail('admin@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'));
            $manager->persist($admin);
        }
        
        $kayuden = $manager->getRepository(User::class)->findOneBy(['username' => 'kayuden']);
        if (!$kayuden) {
            $kayuden = new User();
            $kayuden->setUsername('kayuden');
            $kayuden->setEmail('kbartholomot@gmail.com');
            $kayuden->setRoles(['ROLE_USER']);
            $kayuden->setPassword($this->passwordHasher->hashPassword($kayuden, 'bonjour'));
            $manager->persist($kayuden);
        }

        $john = $manager->getRepository(User::class)->findOneBy(['username' => 'john']);
        if (!$john) {
            $john = new User();
            $john->setUsername('john');
            $john->setEmail('john@example.com');
            $john->setRoles(['ROLE_USER']);
            $john->setPassword($this->passwordHasher->hashPassword($john, 'johnpass'));
            $manager->persist($john);
        }

        $task1 = (new Task())
            ->setTitle('Préparer la réunion de lundi')
            ->setContent('Créer l’ordre du jour, inviter les participants et réserver la salle.')
            ->setAuthor($admin);
        $manager->persist($task1);

        $task2 = (new Task())
            ->setTitle('Nettoyer la base de données')
            ->setContent('Supprimer les anciens logs et les données de test obsolètes.')
            ->setAuthor($anonyme);
        $manager->persist($task2);

        $task3 = (new Task())
            ->setTitle('Rédiger la documentation technique')
            ->setContent('Décrire les endpoints de l’API et le schéma de la base de données.')
            ->setAuthor($john);
        $manager->persist($task3);

        $task4 = (new Task())
            ->setTitle('Mettre à jour le site vitrine')
            ->setContent('Modifier le contenu de la page d’accueil et corriger les liens cassés.')
            ->setAuthor($anonyme);
        $manager->persist($task4);

        $task5 = (new Task())
            ->setTitle('Sauvegarde hebdomadaire du serveur')
            ->setContent('Lancer le script de backup et vérifier l’intégrité des fichiers.')
            ->setAuthor($admin)
            ->setIsDone(true);
        $manager->persist($task5);

        $manager->flush();
    }
}
