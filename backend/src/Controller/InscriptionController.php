<?php

namespace App\Controller;

use App\Entity\Defi;
use App\Entity\Inscription;
use App\Entity\User;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/inscriptions', name: 'inscription_')]
class InscriptionController extends AbstractController
{
    /**
     * POST /defis/{id}/inscriptions
     * Inscrire l’utilisateur courant à un défi
     */
    #[Route('/defis/{id}', name: 'create', methods: ['POST'])]
    public function create(Defi $defi, EntityManagerInterface $em, InscriptionRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        // Vérifier si déjà inscrit
        $existing = $repo->findOneBy(['user' => $user, 'defi' => $defi]);
        if ($existing) {
            return $this->json(['success' => false, 'message' => 'Déjà inscrit à ce défi'], 400);
        }

        $inscription = new Inscription();
        $inscription->setUser($user);
        $inscription->setDefi($defi);
        $inscription->setStatut('en_attente');

        $em->persist($inscription);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription créée avec succès',
            'inscription' => $inscription
        ], 201, [], ['groups' => ['inscription:read']]);
    }

    /**
     * GET /defis/{id}/inscriptions
     * Liste des inscrits d’un défi (admin only)
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/defis/{id}', name: 'list', methods: ['GET'])]
    public function list(Defi $defi, InscriptionRepository $repo): JsonResponse
    {
        $inscriptions = $repo->findBy(['defi' => $defi]);

        return $this->json($inscriptions, 200, [], ['groups' => ['inscription:read']]);
    }

    /**
     * PATCH /inscriptions/{id}
     * Modifier le statut d’une inscription (admin only)
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(Request $request, Inscription $inscription, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['statut']) && in_array($data['statut'], ['validee', 'annulee'])) {
            $inscription->setStatut($data['statut']);
            $em->flush();
            return $this->json(['success' => true, 'message' => 'Statut mis à jour']);
        }

        return $this->json(['success' => false, 'message' => 'Statut invalide'], 400);
    }

    /**
     * POST /inscriptions/{id}/request-cancel
     * Demande de désinscription (utilisateur)
     */
    #[Route('/{id}/request-cancel', name: 'request_cancel', methods: ['POST'])]
    public function requestCancel(Inscription $inscription, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User || $inscription->getUser() !== $user) {
            return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        // Ici on pourrait notifier l’admin, pour l’instant on change juste le statut
        $inscription->setStatut('annulee');
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Demande de désinscription envoyée'], 202);
    }
}
