<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendWelcomeEmail(string $userEmail, string $userName, string $urlActivation)
{
    $email = (new Email())
        ->from('no-reply@agorafit.com')
        ->to($userEmail)
        ->subject('Bienvenue dans l’aventure AgoraFit 🎉')
        ->text("Bonjour $userName,\n\nBienvenue sur AgoraFit !\n\nActivez votre compte dès maintenant pour rejoindre la communauté et relever vos premiers défis sportifs : $urlActivation\n\nÀ très vite sur AgoraFit 💪")
        ->html("
            <div style='font-family: Arial, sans-serif; color:#333; line-height:1.6; max-width:600px; margin:auto; padding:20px; border:1px solid #eee; border-radius:10px;'>
                <h1 style='color:#daf020; text-align:center;'>Bienvenue sur <span style='color:#000;'>AgoraFit</span> 🎉</h1>
                <p>Bonjour <strong>$userName</strong>,</p>
                <p>Nous sommes ravis de vous compter parmi nous ! 🚀 AgoraFit, c’est bien plus qu’une application : c’est une communauté de passionnés prêts à relever des défis sportifs et à partager leur énergie.</p>
                
                <div style='text-align:center; margin:30px 0;'>
                    <a href='$urlActivation' style='background:#daf020; color:#000; padding:15px 25px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;'>
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
        ");

    $this->mailer->send($email);
}

}