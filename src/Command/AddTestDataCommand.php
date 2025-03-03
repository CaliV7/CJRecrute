<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:add-test-data',
    description: 'Adds test data to the database',
)]
class AddTestDataCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Créer des utilisateurs
        $user1 = new User();
        $user1->setEmail('user1@example.com');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword('password');
        $user1->setFirstName('Jean');
        $user1->setLastName('Dupont');
        
        $user2 = new User();
        $user2->setEmail('user2@example.com');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword('password');
        $user2->setFirstName('Marie');
        $user2->setLastName('Durand');
        
        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        
        // Créer des offres d'emploi
        $job1 = new Job();
        $job1->setTitle('Développeur Full Stack');
        $job1->setDescription('Nous recherchons un développeur full stack pour rejoindre notre équipe.');
        $job1->setCompany('Tech Solutions');
        $job1->setLocation('Paris');
        $job1->setCreatedAt(new \DateTime());
        
        $job2 = new Job();
        $job2->setTitle('Développeur Frontend Angular');
        $job2->setDescription('Poste pour un développeur frontend spécialisé en Angular.');
        $job2->setCompany('Digital Agency');
        $job2->setLocation('Lyon');
        $job2->setCreatedAt(new \DateTime());
        
        $this->entityManager->persist($job1);
        $this->entityManager->persist($job2);
        
        $this->entityManager->flush();
        
        $output->writeln('Test data added successfully!');
        
        return Command::SUCCESS;
    }
}