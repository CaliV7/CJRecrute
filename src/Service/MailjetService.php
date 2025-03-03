<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use App\Entity\Application;

class MailjetService
{
    private $mailer;
    private $senderEmail;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->senderEmail = 'your-email@example.com';
    }

    public function sendApplicationConfirmation(Application $application)
    {
        $user = $application->getUser();
        $job = $application->getJob();
        
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($user->getEmail())
            ->subject('Confirmation de votre candidature')
            ->html(
                "<h1>Confirmation de candidature</h1>
                <p>Bonjour {$user->getFirstName()},</p>
                <p>Nous confirmons la réception de votre candidature pour le poste de {$job->getTitle()} chez {$job->getCompany()}.</p>
                <p>Nous étudierons votre dossier et reviendrons vers vous rapidement.</p>
                <p>Cordialement,<br>L'équipe de recrutement</p>"
            );
            
        $this->mailer->send($email);
    }
    
    public function sendNewApplicationNotification(Application $application)
    {
        $user = $application->getUser();
        $job = $application->getJob();
        
        $email = (new Email())
            ->from($this->senderEmail)
            ->to('recruteur@example.com')
            ->subject('Nouvelle candidature reçue')
            ->html(
                "<h1>Nouvelle candidature</h1>
                <p>Une nouvelle candidature a été reçue.</p>
                <p>Candidat: {$user->getFirstName()} {$user->getLastName()}</p>
                <p>Poste: {$job->getTitle()}</p>
                <p>Entreprise: {$job->getCompany()}</p>
                <p>Date: {$application->getCreatedAt()->format('d/m/Y H:i')}</p>"
            );
            
        $this->mailer->send($email);
    }
}