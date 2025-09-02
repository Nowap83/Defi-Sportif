<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class MailerService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $fromEmail;

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $logger,
        string $fromEmail = 'no-reply@agorafit.com'
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->fromEmail = $fromEmail;
    }

    public function sendWelcomeEmail(string $userEmail, string $userName, string $urlActivation): bool
    {
        try {
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($userEmail)
                ->subject('Bienvenue dans l\'aventure AgoraFit 🎉')
                ->text($this->getWelcomeTextContent($userName, $urlActivation))
                ->html($this->getWelcomeHtmlContent($userName, $urlActivation));

            $this->mailer->send($email);
            $this->logger->info('Welcome email sent successfully', ['email' => $userEmail]);

            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send welcome email', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendPasswordResetEmail(string $userEmail, string $userName, string $resetUrl): bool
    {
        try {
            $email = (new Email())
                ->from($this->fromEmail)
                ->to($userEmail)
                ->subject('Réinitialisation de votre mot de passe AgoraFit 🔐')
                ->text($this->getPasswordResetTextContent($userName, $resetUrl))
                ->html($this->getPasswordResetHtmlContent($userName, $resetUrl));

            $this->mailer->send($email);
            $this->logger->info('Password reset email sent successfully', ['email' => $userEmail]);

            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send password reset email', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function getWelcomeTextContent(string $userName, string $urlActivation): string
    {
        return "Bonjour {$userName},

        Bienvenue sur AgoraFit !

        Activez votre compte dès maintenant pour rejoindre la communauté et relever vos premiers défis sportifs : {$urlActivation}

        À très vite sur AgoraFit 💪

        ---
        Cet email a été envoyé automatiquement, merci de ne pas y répondre.";
    }

    private function getWelcomeHtmlContent(string $userName, string $urlActivation): string
    {
        // Échapper les variables pour éviter les injections XSS
        $userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $urlActivation = htmlspecialchars($urlActivation, ENT_QUOTES, 'UTF-8');

        return "
            <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6; max-width:600px; margin:auto; padding:20px; border:1px solid #eee; border-radius:10px;'>
                <h1 style='color:#daf020; text-align:center;'>Bienvenue sur <span style='color:#000;'>AgoraFit</span> 🎉</h1>
                <p>Bonjour <strong>{$userName}</strong>,</p>
                <p>Nous sommes ravis de vous compter parmi nous ! 🚀 AgoraFit, c'est bien plus qu'une application : c'est une communauté de passionnés prêts à relever des défis sportifs et à partager leur énergie.</p>
               
                <div style='text-align:center; margin:30px 0;'>
                    <a href='{$urlActivation}' style='background:#daf020; color:#000; padding:15px 25px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;'>
                        ✅ Activer mon compte
                    </a>
                </div>
                <p>Une fois votre compte activé, vous pourrez :</p>
                <ul>
                    <li>🏆 Rejoindre et relever des défis sportifs</li>
                    <li>📊 Suivre vos performances et vos progrès</li>
                    <li>🤝 Partager vos réussites avec la communauté</li>
                </ul>
                <p style='margin-top:20px;'>À très vite sur <strong>AgoraFit</strong> 💪</p>
                <hr style='margin:30px 0; border:none; border-top:1px solid #ddd;'/>
                <p style='font-size:12px; color:#777; text-align:center;'>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            </div>
        ";
    }

    private function getPasswordResetTextContent(string $userName, string $resetUrl): string
    {
        return "Bonjour {$userName},

        Vous avez demandé la réinitialisation de votre mot de passe AgoraFit.

        Cliquez sur le lien suivant pour créer un nouveau mot de passe : {$resetUrl}

        ⚠️ Ce lien est valide pendant 1 heure seulement.

        Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.

        L'équipe AgoraFit 💪

        ---
        Cet email a été envoyé automatiquement, merci de ne pas y répondre.";
    }

    private function getPasswordResetHtmlContent(string $userName, string $resetUrl): string
    {
        // Échapper les variables pour éviter les injections XSS
        $userName = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
        $resetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        return "
            <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6; max-width:600px; margin:auto; padding:20px; border:1px solid #eee; border-radius:10px;'>
                <h1 style='color:#daf020; text-align:center;'>Réinitialisation de mot de passe</h1>
                <h2 style='color:#000; text-align:center;'>AgoraFit 🔐</h2>
                
                <p>Bonjour <strong>{$userName}</strong>,</p>
                <p>Vous avez demandé la réinitialisation de votre mot de passe AgoraFit.</p>
                
                <div style='text-align:center; margin:30px 0;'>
                    <a href='{$resetUrl}' style='background:#daf020; color:#000; padding:15px 25px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;'>
                        🔑 Réinitialiser mon mot de passe
                    </a>
                </div>
                
                <div style='background:#fff3cd; border:1px solid #ffeaa7; border-radius:8px; padding:15px; margin:20px 0;'>
                    <p style='margin:0; color:#856404;'>
                        ⚠️ <strong>Important :</strong> Ce lien est valide pendant <strong>1 heure seulement</strong>.
                    </p>
                </div>
                
                <p>Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email en toute sécurité. Votre mot de passe actuel reste inchangé.</p>
                
                <p style='margin-top:30px;'>L'équipe <strong>AgoraFit</strong> 💪</p>
                
                <hr style='margin:30px 0; border:none; border-top:1px solid #ddd;'/>
                <p style='font-size:12px; color:#777; text-align:center;'>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            </div>
        ";
    }
}
