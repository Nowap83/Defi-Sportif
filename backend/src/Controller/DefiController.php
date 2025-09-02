<?php

namespace App\Controller;

use App\Entity\Defi;
use App\Entity\User; // Add this missing import
use App\Repository\DefiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/defis', name: 'defis_')]
class DefiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(DefiRepository $defiRepository): JsonResponse
    {
        $defis = $defiRepository->findAll();

        return $this->json($defis, 200, [], ['groups' => ['defis_list']]);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(Defi $defi): JsonResponse
    {
        return $this->json($defi, 200, [], ['groups' => ['defi_list']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            error_log("User not authenticated or not instance of User");
            return $this->json(['success' => false, 'message' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        

        try {
            $defi = new Defi();
            $defi->setTitre($request->request->get('titre'));
            $defi->setDescription($request->request->get('description'));
            $defi->setTypeDefi($request->request->get('typeDefi'));
            $defi->setRegion($request->request->get('region'));
            $defi->setPays($request->request->get('pays'));
            $defi->setDistance((float) $request->request->get('distance', 0));
            $defi->setMinParticipant((int) $request->request->get('minParticipant', 0));
            $defi->setMaxParticipant((int) $request->request->get('maxParticipant', 0));
            $defi->setCreateur($user);

            $dateDefi = $request->request->get('dateDefi');
            if ($dateDefi) {
                try {
                    $defi->setDateDefi(new \DateTimeImmutable($dateDefi));
                } catch (\Exception $e) {
                    error_log("Date format error: " . $e->getMessage());
                    return $this->json(['success' => false, 'message' => 'Format de date invalide'], 400);
                }
            }

            $file = $request->files->get('image');
            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/images/defis/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = uniqid('defi_') . '.' . $file->guessExtension();
                try {
                    $file->move($uploadDir, $fileName);
                    $defi->setImage('/images/defis/' . $fileName);
                } catch (FileException $e) {
                    error_log("File upload error: " . $e->getMessage());
                    return $this->json(['success' => false, 'message' => 'Erreur upload image'], 500);
                }
            }

            $em->persist($defi);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Défi créé avec succès',
                'defi' => [
                    'id' => $defi->getId(),
                    'titre' => $defi->getTitre(),
                    'description' => $defi->getDescription(),
                    'dateDefi' => $defi->getDateDefi()?->format('Y-m-d'),
                    'typeDefi' => $defi->getTypeDefi(),
                    'region' => $defi->getRegion(),
                    'pays' => $defi->getPays(),
                    'distance' => $defi->getDistance(),
                    'minParticipant' => $defi->getMinParticipant(),
                    'maxParticipant' => $defi->getMaxParticipant(),
                    'image' => $defi->getImage(),
                ]
            ], 201);
        } catch (\Exception $e) {
            error_log("Error creating defi: " . $e->getMessage());
            return $this->json(['success' => false, 'message' => 'Erreur dans la création du défi'], 500);
        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(Request $request, Defi $defi, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $defi->setTitre($data['titre'] ?? $defi->getTitre());
        $defi->setDescription($data['description'] ?? $defi->getDescription());
        if (!empty($data['dateDefi'])) {
            $defi->setDateDefi(new \DateTimeImmutable($data['dateDefi']));
        }
        $defi->setTypeDefi($data['typeDefi'] ?? $defi->getTypeDefi());
        $defi->setRegion($data['region'] ?? $defi->getRegion());
        $defi->setPays($data['pays'] ?? $defi->getPays());
        $defi->setDistance(isset($data['distance']) ? (float)$data['distance'] : $defi->getDistance());
        $defi->setMinParticipant(isset($data['minParticipant']) ? (int)$data['minParticipant'] : $defi->getMinParticipant());
        $defi->setMaxParticipant(isset($data['maxParticipant']) ? (int)$data['maxParticipant'] : $defi->getMaxParticipant());
        $defi->setImage($data['image'] ?? $defi->getImage());

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Défi modifié avec succès',
            'defi' => $defi
        ], 200, [], ['groups' => ['defi_list']]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Defi $defi, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($defi);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Défi supprimé avec succès'
        ]);
    }
}