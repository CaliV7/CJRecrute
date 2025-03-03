<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Job;
use App\Entity\User;
use App\Service\MailjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApplicationController extends AbstractController
{
    private $entityManager;
    private $mailjetService;

    public function __construct(EntityManagerInterface $entityManager, MailjetService $mailjetService)
    {
        $this->entityManager = $entityManager;
        $this->mailjetService = $mailjetService;
    }

    #[Route('/applications', name: 'create_application', methods: ['POST'])]
    public function createApplication(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Dans un cas réel, vous récupéreriez l'utilisateur connecté
        $user = $this->entityManager->getRepository(User::class)->find($data['userId']);
        $job = $this->entityManager->getRepository(Job::class)->find($data['jobId']);
        
        if (!$user || !$job) {
            return new JsonResponse(['error' => 'User or Job not found'], 404);
        }
        
        $application = new Application();
        $application->setUser($user);
        $application->setJob($job);
        $application->setStatus('pending');
        $application->setCreatedAt(new \DateTime());
        $application->setResume($data['resume'] ?? 'resume.pdf');
        $application->setCoverLetter($data['coverLetter'] ?? null);
        
        $this->entityManager->persist($application);
        $this->entityManager->flush();
        
        // Envoi des emails avec Mailjet
        $this->mailjetService->sendApplicationConfirmation($application);
        $this->mailjetService->sendNewApplicationNotification($application);
        
        return new JsonResponse([
            'id' => $application->getId(),
            'status' => $application->getStatus(),
            'createdAt' => $application->getCreatedAt()->format('Y-m-d H:i:s')
        ], 201);
    }

    #[Route('/applications', name: 'get_applications', methods: ['GET'])]
    public function getApplications(): JsonResponse
    {
        $applications = $this->entityManager->getRepository(Application::class)->findAll();
        
        $data = [];
        foreach ($applications as $application) {
            $data[] = [
                'id' => $application->getId(),
                'job' => [
                    'id' => $application->getJob()->getId(),
                    'title' => $application->getJob()->getTitle(),
                    'company' => $application->getJob()->getCompany()
                ],
                'user' => [
                    'id' => $application->getUser()->getId(),
                    'firstName' => $application->getUser()->getFirstName(),
                    'lastName' => $application->getUser()->getLastName(),
                    'email' => $application->getUser()->getEmail()
                ],
                'status' => $application->getStatus(),
                'createdAt' => $application->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }
        
        return new JsonResponse($data);
    }
}