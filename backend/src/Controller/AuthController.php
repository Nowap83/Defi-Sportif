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
}
