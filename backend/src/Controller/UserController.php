<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Serializer\Annotation\Groups;


#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{


    #[Route('', name: 'index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        return $this->json($users, 200, [], ['groups' => ['user_index']]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, 200, [], ['groups' => ['user_show']] 
        );
    }
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

    #[Route('/me', name: 'update_me', methods: ['POST'])]
    public function updateMe(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'roles' => $user->getRoles(),
                'isActive' => $user->isActive(),
                'dateCGU' => $user->getDateCGU()->format('Y-m-d'),
                'avatar' => $user->getAvatar(),
            ]
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

     #[Route('/me/delete', name: 'delete_me', methods: ['DELETE'])]
    public function deleteMe(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Votre compte a été supprimé avec succès'
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
     #[Route('/delete/{id}', name: 'delete', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {

        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur introuvable'
            ], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => "L'utilisateur {$id} a été supprimé avec succès"
        ]);
    }


}
