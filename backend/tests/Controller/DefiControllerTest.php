<?php

namespace App\Tests\Controller;

use App\Entity\Defi;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefiControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager = null;
    private ?User $adminUser = null;
    private ?User $regularUser = null;
    private ?Defi $defi = null;
    private ?string $adminToken = null;
    private ?string $regularToken = null;
    private ?\Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface $jwtManager = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Vider les users
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();

        $this->adminUser = $this->createTestUser('admin@test.com', ['ROLE_ADMIN']);
        $this->regularUser = $this->createTestUser('user@test.com', ['ROLE_USER']);

        $this->entityManager->persist($this->adminUser);
        $this->entityManager->persist($this->regularUser);
        $this->entityManager->flush();

        // Créer un défi initial
        $this->defi = new Defi();
        $this->defi->setTitre('Défi Test 1')
            ->setDescription('Description test')
            ->setDateDefi(new \DateTimeImmutable('+1 month'))
            ->setCreateur($this->adminUser);
        $this->entityManager->persist($this->defi);

        $this->jwtManager = static::getContainer()->get(\Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface::class);

        $this->adminToken = $this->jwtManager->create($this->adminUser);
        $this->regularToken = $this->jwtManager->create($this->regularUser);

        $this->entityManager->flush();
    }

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    private function createTestUser(string $email, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $user->setRoles($roles);
        $user->setNom('Nom_' . uniqid());
        $user->setPrenom('Prenom_' . uniqid());
        $user->setIsActive(true);
        $user->setDateCGU(new \DateTimeImmutable());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setAvatar('default.png');
        return $user;
    }

    private function authHeaders(string $token): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    private function loginUser(User $user): void
    {
        $this->client->loginUser($user);
    }

    // Test de la liste des défis (GET /defis)
    public function testListDefis(): void
    {
        $this->client->request('GET', '/defis');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(3, $response); // 3 défis créés dans setUp

        // Vérifier la structure du premier défi
        $this->assertArrayHasKey('id', $response[0]);
        $this->assertArrayHasKey('titre', $response[0]);
        $this->assertArrayHasKey('description', $response[0]);
    }

    // Test du détail d'un défi (GET /defis/{id})
    public function testDetailDefi(): void
    {
        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Défi Test 1']);

        $this->client->request('GET', '/defis/' . $defi->getId());

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($defi->getId(), $response['id']);
        $this->assertEquals('Défi Test 1', $response['titre']);
    }

    // Test du détail d'un défi inexistant
    public function testDetailDefiNotFound(): void
    {
        $this->client->request('GET', '/defis/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    // Test de création d'un défi par un admin (POST /defis)
    public function testCreateDefiAsAdmin(): void
    {
        $this->loginUser($this->adminUser);

        $defiData = [
            'titre' => 'Nouveau Défi',
            'description' => 'Description du nouveau défi',
            'dateDefi' => '2025-12-31',
            'typeDefi' => 'course',
            'region' => 'Nouvelle Région',
            'pays' => 'France',
            'distance' => '21.1',
            'minParticipant' => '10',
            'maxParticipant' => '100'
        ];

        $this->client->request('POST', '/defis', $defiData);

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Défi créé avec succès', $response['message']);
        $this->assertArrayHasKey('defi', $response);
        $this->assertEquals('Nouveau Défi', $response['defi']['titre']);

        // Vérifier que le défi a été créé en base
        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Nouveau Défi']);
        $this->assertNotNull($defi);
        $this->assertEquals($this->adminUser->getId(), $defi->getCreateur()->getId());
    }

    // Test de création avec upload d'image
    public function testCreateDefiWithImage(): void
    {
        $this->client->loginUser($this->adminUser);

        // Créer un fichier temporaire pour simuler l'upload
        $tempFile = tempnam(sys_get_temp_dir(), 'test_image');
        file_put_contents($tempFile, 'fake image content');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test-image.jpg',
            'image/jpeg',
            null,
            true
        );

        $defiData = [
            'titre' => 'Défi avec Image',
            'description' => 'Description du défi avec image',
            'dateDefi' => '2025-12-31',
            'typeDefi' => 'course',
            'region' => 'Région',
            'pays' => 'France',
            'distance' => '10.5',
            'minParticipant' => '5',
            'maxParticipant' => '50',
        ];

        $this->client->request('POST', '/defis', $defiData, ['image' => $uploadedFile]);

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertStringContainsString('/images/defis/', $response['defi']['image']);
    }

    // Test de création avec date invalide
    public function testCreateDefiWithInvalidDate(): void
    {
        $this->loginUser($this->adminUser);

        $defiData = [
            'titre' => 'Défi Date Invalide',
            'description' => 'Description',
            'dateDefi' => 'date-invalide',
            'typeDefi' => 'course',
            'region' => 'Région',
            'pays' => 'France',
            'distance' => '10',
            'minParticipant' => '5',
            'maxParticipant' => '50'
        ];

        $this->client->request('POST', '/defis', $defiData);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Format de date invalide', $response['message']);
    }

    // Test de création par un utilisateur non admin
    public function testCreateDefiAsRegularUser(): void
    {
        $this->loginUser($this->regularUser);

        $defiData = [
            'titre' => 'Tentative Défi',
            'description' => 'Description',
            'dateDefi' => '2025-12-31',
            'typeDefi' => 'course',
            'region' => 'Région',
            'pays' => 'France',
            'distance' => '10',
            'minParticipant' => '5',
            'maxParticipant' => '50'
        ];

        $this->client->request('POST', '/defis', $defiData);

        $this->assertResponseStatusCodeSame(403); // Forbidden
    }

    // Test de création sans authentification
    public function testCreateDefiUnauthenticated(): void
    {
        $defiData = [
            'titre' => 'Tentative Défi',
            'description' => 'Description'
        ];

        $this->client->request('POST', '/defis', $defiData);

        $this->assertResponseStatusCodeSame(401); // Unauthorized
    }

    // Test de modification d'un défi (PUT /defis/{id})
    public function testEditDefi(): void
    {
        $this->loginUser($this->adminUser);

        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Défi Test 1']);

        $updatedData = [
            'titre' => 'Défi Test 1 Modifié',
            'description' => 'Description modifiée',
            'distance' => 15.5
        ];

        $this->client->request(
            'PUT',
            '/defis/' . $defi->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Défi modifié avec succès', $response['message']);

        // Vérifier les modifications en base
        $this->entityManager->refresh($defi);
        $this->assertEquals('Défi Test 1 Modifié', $defi->getTitre());
        $this->assertEquals('Description modifiée', $defi->getDescription());
        $this->assertEquals(15.5, $defi->getDistance());
    }

    // Test de modification par un utilisateur non admin
    public function testEditDefiAsRegularUser(): void
    {
        $this->loginUser($this->regularUser);

        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Défi Test 1']);

        $updatedData = ['titre' => 'Tentative Modification'];

        $this->client->request(
            'PUT',
            '/defis/' . $defi->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseStatusCodeSame(403); // Forbidden
    }

    // Test de modification d'un défi inexistant
    public function testEditDefiNotFound(): void
    {
        $this->loginUser($this->adminUser);

        $updatedData = ['titre' => 'Modification'];

        $this->client->request(
            'PUT',
            '/defis/99999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseStatusCodeSame(404);
    }

    // Test de suppression d'un défi (DELETE /defis/{id})
    public function testDeleteDefi(): void
    {
        $this->loginUser($this->adminUser);

        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Défi Test 1']);
        $defiId = $defi->getId();

        $this->client->request('DELETE', '/defis/' . $defiId);

        $this->assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Défi supprimé avec succès', $response['message']);

        // Vérifier que le défi a été supprimé
        $deletedDefi = $this->entityManager->getRepository(Defi::class)->find($defiId);
        $this->assertNull($deletedDefi);
    }

    // Test de suppression par un utilisateur non admin
    public function testDeleteDefiAsRegularUser(): void
    {
        $this->loginUser($this->regularUser);

        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Défi Test 1']);

        $this->client->request('DELETE', '/defis/' . $defi->getId());

        $this->assertResponseStatusCodeSame(403); // Forbidden
    }

    // Test de suppression d'un défi inexistant
    public function testDeleteDefiNotFound(): void
    {
        $this->loginUser($this->adminUser);

        $this->client->request('DELETE', '/defis/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    // Test de suppression sans authentification
    public function testDeleteDefiUnauthenticated(): void
    {
        $defi = $this->entityManager->getRepository(Defi::class)->findOneBy(['titre' => 'Défi Test 1']);

        $this->client->request('DELETE', '/defis/' . $defi->getId());

        $this->assertResponseStatusCodeSame(401); // Unauthorized
    }

    // Test des groupes de sérialisation
    public function testSerializationGroups(): void
    {
        $this->client->request('GET', '/defis');

        $response = json_decode($this->client->getResponse()->getContent(), true);

        // Vérifier que les champs du groupe 'defis_list' sont présents
        $firstDefi = $response[0];
        $this->assertArrayHasKey('id', $firstDefi);
        $this->assertArrayHasKey('titre', $firstDefi);
        $this->assertArrayHasKey('description', $firstDefi);
        $this->assertArrayHasKey('dateDefi', $firstDefi);
        $this->assertArrayHasKey('typeDefi', $firstDefi);
        $this->assertArrayHasKey('region', $firstDefi);
        $this->assertArrayHasKey('pays', $firstDefi);
        $this->assertArrayHasKey('distance', $firstDefi);
        $this->assertArrayHasKey('minParticipant', $firstDefi);
        $this->assertArrayHasKey('maxParticipant', $firstDefi);
        $this->assertArrayHasKey('image', $firstDefi);
    }

    // Test de gestion des erreurs serveur
    public function testServerErrorHandling(): void
    {
        $this->loginUser($this->adminUser);

        // Données incomplètes pour provoquer une erreur
        $invalidData = [
            'titre' => null, // Titre requis
            'description' => null // Description requise
        ];

        $this->client->request('POST', '/defis', $invalidData);

        // Le contrôleur devrait gérer l'erreur et retourner une réponse JSON
        $this->assertResponseStatusCodeSame(500);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Erreur serveur', $response['message']);
    }
}
