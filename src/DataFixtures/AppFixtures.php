<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void //php bin/console doctrine:fixtures:load
    {
        $anonyme = $manager->getRepository(User::class)->findOneBy(['username' => 'anonyme']);
        if (!$anonyme) {
            $anonyme = new User();
            $anonyme->setUsername('anonyme');
            $anonyme->setEmail('anonyme@exemple.com');
            $anonyme->setRoles(['ROLE_USER']);
            $anonyme->setPassword($this->passwordHasher->hashPassword($anonyme, 'anonyme123'));
            $manager->persist($anonyme);
        }

        $admin = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        if (!$admin) {
            $admin = new User();
            $admin->setUsername('admin');
            $admin->setEmail('admin@exemple.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $manager->persist($admin);
        }

        $user1 = $manager->getRepository(User::class)->findOneBy(['username' => 'marie']);
        if (!$user1) {
            $user1 = new User();
            $user1->setUsername('marie');
            $user1->setEmail('marie@exemple.com');
            $user1->setRoles(['ROLE_USER']);
            $user1->setPassword($this->passwordHasher->hashPassword($user1, 'marie123'));
            $manager->persist($user1);
        }

        $user2 = $manager->getRepository(User::class)->findOneBy(['username' => 'john']);
        if (!$user2) {
            $user2 = new User();
            $user2->setUsername('john');
            $user2->setEmail('john@exemple.com');
            $user2->setRoles(['ROLE_USER']);
            $user2->setPassword($this->passwordHasher->hashPassword($user2, 'john123'));
            $manager->persist($user2);
        }

        $task1 = (new Task())
            ->setTitle('Préparer la réunion de lundi')
            ->setContent('Créer l’ordre du jour, inviter les participants et réserver la salle.')
            ->setAuthor($admin);
        $manager->persist($task1);

        $task2 = (new Task())
            ->setTitle('Nettoyer la base de données')
            ->setContent('Supprimer les anciens logs et les données de test obsolètes.')
            ->setAuthor($user1);
        $manager->persist($task2);

        $task3 = (new Task())
            ->setTitle('Rédiger la documentation technique')
            ->setContent('Décrire les endpoints de l’API et le schéma de la base de données.')
            ->setAuthor($user2);
        $manager->persist($task3);

        $task4 = (new Task())
            ->setTitle('Mettre à jour le site vitrine')
            ->setContent('Modifier le contenu de la page d’accueil et corriger les liens cassés.')
            ->setAuthor($anonyme);
        $manager->persist($task4);

        $task5 = (new Task())
            ->setTitle('Sauvegarde hebdomadaire du serveur')
            ->setContent('Lancer le script de backup et vérifier l’intégrité des fichiers.')
            ->setAuthor($anonyme)
            ->setIsDone(true);
        $manager->persist($task5);

        $manager->flush();
    }
}
