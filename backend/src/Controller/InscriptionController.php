<?php

namespace App\Controller;

use App\Entity\Defi;
use App\Entity\Inscription;
use App\Entity\User;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/inscriptions', name: 'inscription_')]
class InscriptionController extends AbstractController
{

    #[Route('/defis/{id}', name: 'create', methods: ['POST'])]
    public function create(Defi $defi, EntityManagerInterface $em, InscriptionRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $existing = $repo->findOneBy(['user' => $user, 'defi' => $defi]);
        if ($existing) {
            return $this->json(['success' => false, 'message' => 'Déjà inscrit à ce défi'], 400);
        }

        $inscription = new Inscription();
        $inscription->setUser($user);
        $inscription->setDefi($defi);
        $inscription->setDateInscription(new \DateTimeImmutable());

        $em->persist($inscription);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription créée avec succès',
            'inscription' => [
                'id' => $inscription->getId(),
                'user' => $user->getEmail(),
                'defi' => $defi->getTitre(),
                'dateInscription' => $inscription->getDateInscription()->format('Y-m-d H:i:s')
            ]
        ], 201);
    }

    #[Route('/defis/{id}/me', name: 'check_my_inscription', methods: ['GET'])]
    public function checkMyInscription(
        Defi $defi,
        InscriptionRepository $repo
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $inscription = $repo->findOneBy(['user' => $user, 'defi' => $defi]);

        if (!$inscription) {
            return $this->json([
                'inscrit' => false
            ], 200);
        }

        return $this->json([
            'inscrit' => true,
            'inscription' => [
                'id' => $inscription->getId(),
                'dateInscription' => $inscription->getDateInscription()->format('Y-m-d H:i:s')
            ]
        ], 200);
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/defis/{id}', name: 'list', methods: ['GET'])]
    public function list(Defi $defi, InscriptionRepository $repo): JsonResponse
    {
        $inscriptions = $repo->findBy(['defi' => $defi]);

        $data = array_map(fn(Inscription $i) => [
            'id' => $i->getId(),
            'user' => $i->getUser()->getEmail(),
            'dateInscription' => $i->getDateInscription()->format('Y-m-d H:i:s')
        ], $inscriptions);

        return $this->json($data, 200);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Inscription $inscription, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($inscription);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Inscription supprimée avec succès'
        ]);
    }


    #[Route('/{id}/request-cancel', name: 'request_cancel', methods: ['POST'])]
    public function requestCancel(Inscription $inscription): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User || $inscription->getUser() !== $user) {
            return $this->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        //TODO MAILER A FAIRE
        return $this->json([
            'success' => true,
            'message' => 'Votre demande de désinscription a été transmise à l’admin'
        ], 202);
    }
}
