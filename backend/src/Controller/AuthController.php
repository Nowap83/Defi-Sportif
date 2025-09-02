<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Utilisateur;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Service\MailerService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        throw new \LogicException('This should be intercepted by the authenticator');
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, MailerService $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé.',
                    'status' => 400
                ],
                400
            );
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setRoles(['ROLE_USER']);
        $user->setIsActive(false);
        $user->setDateCGU(new \DateTimeImmutable());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setAvatar('/images/avatars/default.png');
        $user->setVerificationToken(bin2hex(random_bytes(32)));

        $em->persist($user);
        $em->flush();

        $urlFrontend = 'http://localhost:5173/verify/' . $user->getVerificationToken();
        $mailer->sendWelcomeEmail($user->getEmail(), $user->getPrenom(), $urlFrontend);

        return $this->json(
            [
                'success' => true,
                'message' => 'Utilisateur créé. Vérifie ton email pour activer ton compte.',
                'status' => 201
            ],
            201
        );
    }


    #[Route('/verify-mail/{token}', name: 'verify', methods: ['GET'])]
    public function verify(string $token, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur introuvable ou token invalide.',
                'status'  => 404
            ], 404);
        }

        if ($user->isActive()) {
            return $this->json([
                'success' => false,
                'message' => 'Ce compte est déjà activé.',
                'status'  => 400
            ], 400);
        }

        $user->setIsActive(true);
        $user->setVerificationToken(null);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Votre compte a été activé avec succès.',
            'status'  => 200,
            'data'    => ['email' => $user->getEmail()]
        ], 200);
    }
    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request, 
        EntityManagerInterface $em, 
        MailerService $mailer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['email'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email requis.',
                'status' => 400
            ], 400);
        }

        // Validation de l'email
        $violations = $validator->validate($data['email'], [
            new Assert\NotBlank(),
            new Assert\Email()
        ]);

        if (count($violations) > 0) {
            return $this->json([
                'success' => false,
                'message' => 'Format d\'email invalide.',
                'status' => 400
            ], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        
        // On renvoie toujours un message de succès pour des raisons de sécurité
        // (éviter l'énumération des emails existants)
        if (!$user) {
            return $this->json([
                'success' => true,
                'message' => 'Si cet email existe dans notre base, vous recevrez un lien de réinitialisation.',
                'status' => 200
            ], 200);
        }

        // Vérifier si l'utilisateur est actif
        if (!$user->isActive()) {
            return $this->json([
                'success' => false,
                'message' => 'Votre compte n\'est pas encore activé. Vérifiez votre email.',
                'status' => 400
            ], 400);
        }

        // Générer un token de réinitialisation
        $resetToken = bin2hex(random_bytes(32));
        $user->setResetToken($resetToken);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
        
        $em->flush();

        // URL de réinitialisation
        $resetUrl = 'http://localhost:5173/reset-password/' . $resetToken;
        
        $emailSent = $mailer->sendPasswordResetEmail(
            $user->getEmail(), 
            $user->getPrenom() ?: $user->getNom(), 
            $resetUrl
        );

        if (!$emailSent) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.',
                'status' => 500
            ], 500);
        }

        return $this->json([
            'success' => true,
            'message' => 'Si cet email existe dans notre base, vous recevrez un lien de réinitialisation.',
            'status' => 200
        ], 200);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request, 
        EntityManagerInterface $em, 
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'success' => false,
                'message' => 'Données JSON invalides.',
                'status' => 400
            ], 400);
        }

        // Vérification des champs requis
        if (!isset($data['token']) || !isset($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Token et mot de passe requis.',
                'status' => 400
            ], 400);
        }

        // Validation du mot de passe
        $violations = $validator->validate($data['password'], [
            new Assert\NotBlank(),
            new Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères.')
        ]);

        if (count($violations) > 0) {
            return $this->json([
                'success' => false,
                'message' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'status' => 400
            ], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $data['token']]);
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Token de réinitialisation invalide.',
                'status' => 400
            ], 400);
        }

        // Vérifier si le token n'a pas expiré
        if ($user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            return $this->json([
                'success' => false,
                'message' => 'Le token de réinitialisation a expiré. Demandez un nouveau lien.',
                'status' => 400
            ], 400);
        }

        // Réinitialiser le mot de passe
        $user->setPassword($hasher->hashPassword($user, $data['password']));
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Votre mot de passe a été réinitialisé avec succès.',
            'status' => 200
        ], 200);
    }
}

