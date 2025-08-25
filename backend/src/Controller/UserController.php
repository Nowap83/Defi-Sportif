<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/user', name: 'api_user_')]
final class UserController extends AbstractController
{
    #[Route('/me', name: 'get_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'roles' => $user->getRoles(),
            'isActive' => $user->isActive(),
            'dateCGU' => $user->getDateCGU()->format('Y-m-d'),
            'avatar' => $user->getAvatar()
        ]);
    }

    #[Route('/me/avatar', name: 'avatar_upload', methods: ['POST'])]
    public function uploadAvatar(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $file = $request->files->get('avatar');
        if (!$file) {
            return $this->json(['error' => 'Aucun fichier fourni'], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/avatars/';
        $fileName = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($uploadDir, $fileName);
        } catch (FileException $e) {
            return $this->json(['error' => 'Erreur lors de l\'upload'], 500);
        }

        $user->setAvatar('/images/avatars/' . $fileName);
        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Avatar mis à jour',
            'avatar' => $user->getAvatar(),
        ]);
    }
}
