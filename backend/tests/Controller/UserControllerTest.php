<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private User $adminUser;
    private User $regularUser;
    private User $anotherUser;

    protected function setUp(): void
    {
        // Créer le client une seule fois
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Commencer une transaction pour isoler les tests
        $this->entityManager->beginTransaction();
        
        $this->createTestUsers();
    }

    protected function tearDown(): void
    {
        // Annuler la transaction pour nettoyer
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->rollback();
        }
        
        parent::tearDown();
    }

    private function createTestUsers(): void
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        // Utilisateur admin
        $this->adminUser = new User();
        $this->adminUser->setEmail('admin@test.com');
        $this->adminUser->setNom('Admin');
        $this->adminUser->setPrenom('Test');
        $this->adminUser->setRoles(['ROLE_ADMIN']);
        $this->adminUser->setIsActive(true);
        $this->adminUser->setDateCGU(new \DateTimeImmutable());
        
        // Ajouter created_at si nécessaire
        if (method_exists($this->adminUser, 'setCreatedAt')) {
            $this->adminUser->setCreatedAt(new \DateTimeImmutable());
        }
        
        $hashedPassword = $passwordHasher->hashPassword($this->adminUser, 'adminpass');
        $this->adminUser->setPassword($hashedPassword);

        // Utilisateur normal
        $this->regularUser = new User();
        $this->regularUser->setEmail('user@test.com');
        $this->regularUser->setNom('User');
        $this->regularUser->setPrenom('Regular');
        $this->regularUser->setRoles(['ROLE_USER']);
        $this->regularUser->setIsActive(true);
        $this->regularUser->setDateCGU(new \DateTimeImmutable());
        
        if (method_exists($this->regularUser, 'setCreatedAt')) {
            $this->regularUser->setCreatedAt(new \DateTimeImmutable());
        }
        
        $hashedPassword = $passwordHasher->hashPassword($this->regularUser, 'userpass');
        $this->regularUser->setPassword($hashedPassword);

        // Autre utilisateur
        $this->anotherUser = new User();
        $this->anotherUser->setEmail('another@test.com');
        $this->anotherUser->setNom('Another');
        $this->anotherUser->setPrenom('User');
        $this->anotherUser->setRoles(['ROLE_USER']);
        $this->anotherUser->setIsActive(true);
        $this->anotherUser->setDateCGU(new \DateTimeImmutable());
        
        if (method_exists($this->anotherUser, 'setCreatedAt')) {
            $this->anotherUser->setCreatedAt(new \DateTimeImmutable());
        }
        
        $hashedPassword = $passwordHasher->hashPassword($this->anotherUser, 'anotherpass');
        $this->anotherUser->setPassword($hashedPassword);

        $this->entityManager->persist($this->adminUser);
        $this->entityManager->persist($this->regularUser);
        $this->entityManager->persist($this->anotherUser);
        $this->entityManager->flush();
    }

    private function loginUser(User $user): void
    {
        $this->client->loginUser($user);
    }

    // Test de l'index (liste des utilisateurs) - GET /user
    public function testIndexAsAdmin(): void
    {
        $this->loginUser($this->adminUser);

        $this->client->request('GET', '/user');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertCount(3, $response);
        
        $this->assertArrayHasKey('id', $response[0]);
        $this->assertArrayHasKey('email', $response[0]);
    }

    public function testIndexAsRegularUser(): void
    {
        $this->loginUser($this->regularUser);

        $this->client->request('GET', '/user');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testIndexUnauthenticated(): void
    {
        $this->client->request('GET', '/user');

        $this->assertResponseStatusCodeSame(401);
    }

    // Test de récupération du profil utilisateur - GET /user/me
    public function testGetMe(): void
    {
        $this->loginUser($this->regularUser);

        $this->client->request('GET', '/user/me');

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($this->regularUser->getId(), $response['id']);
        $this->assertEquals('user@test.com', $response['email']);
        $this->assertEquals('User', $response['nom']);
        $this->assertEquals('Regular', $response['prenom']);
        $this->assertEquals(['ROLE_USER'], $response['roles']);
        $this->assertTrue($response['isActive']);
        $this->assertArrayHasKey('dateCGU', $response);
        $this->assertArrayHasKey('avatar', $response);
    }

    public function testGetMeUnauthenticated(): void
    {
        $this->client->request('GET', '/user/me');

        $this->assertResponseStatusCodeSame(401);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Unauthorized', $response['error']);
    }

    // Test de mise à jour du profil - POST /user/me
    public function testUpdateMe(): void
    {
        $this->loginUser($this->regularUser);

        $updateData = [
            'nom' => 'Nouveau Nom',
            'prenom' => 'Nouveau Prénom',
            'email' => 'newemail@test.com'
        ];

        $this->client->request(
            'POST',
            '/user/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Profil mis à jour avec succès', $response['message']);
        $this->assertEquals('Nouveau Nom', $response['user']['nom']);
        $this->assertEquals('Nouveau Prénom', $response['user']['prenom']);
        $this->assertEquals('newemail@test.com', $response['user']['email']);
    }

    public function testUpdateMePartialData(): void
    {
        $this->loginUser($this->regularUser);

        $updateData = [
            'nom' => 'Nom Modifié Seulement'
        ];

        $this->client->request(
            'POST',
            '/user/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Nom Modifié Seulement', $response['user']['nom']);
        $this->assertEquals('Regular', $response['user']['prenom']);
        $this->assertEquals('user@test.com', $response['user']['email']);
    }

    public function testUpdateMeUnauthenticated(): void
    {
        $updateData = ['nom' => 'Test'];

        $this->client->request(
            'POST',
            '/user/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(401);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Unauthorized', $response['message']);
    }

    // Test d'upload d'avatar - POST /user/me/avatar
    public function testUploadAvatar(): void
    {
        $this->loginUser($this->regularUser);

        // Créer un fichier temporaire
        $tempFile = tempnam(sys_get_temp_dir(), 'test_avatar');
        file_put_contents($tempFile, 'fake avatar content');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'avatar.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/user/me/avatar',
            [],
            ['avatar' => $uploadedFile]
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Avatar mis à jour', $response['message']);
        $this->assertStringContainsString('/images/avatars/', $response['avatar']);
    }

    public function testUploadAvatarNoFile(): void
    {
        $this->loginUser($this->regularUser);

        $this->client->request('POST', '/user/me/avatar');

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Aucun fichier fourni', $response['error']);
    }

    public function testUploadAvatarUnauthenticated(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_avatar');
        file_put_contents($tempFile, 'fake content');
        
        $uploadedFile = new UploadedFile(
            $tempFile,
            'avatar.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->client->request(
            'POST',
            '/user/me/avatar',
            [],
            ['avatar' => $uploadedFile]
        );

        $this->assertResponseStatusCodeSame(401);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Unauthorized', $response['error']);
    }

    // Test de suppression de son propre compte - DELETE /user/me/delete
    public function testDeleteMe(): void
    {
        $this->loginUser($this->regularUser);
        $userId = $this->regularUser->getId();

        $this->client->request('DELETE', '/user/me/delete');

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Votre compte a été supprimé avec succès', $response['message']);

        // Dans un contexte de transaction, on ne peut pas vérifier la suppression réelle
        // car elle sera annulée au tearDown
    }

    public function testDeleteMeUnauthenticated(): void
    {
        $this->client->request('DELETE', '/user/me/delete');

        $this->assertResponseStatusCodeSame(401);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Utilisateur non authentifié', $response['message']);
    }

    // Test de suppression d'un utilisateur par un admin - DELETE /user/delete/{id}
    public function testDeleteUserAsAdmin(): void
    {
        $this->loginUser($this->adminUser);
        $userToDeleteId = $this->anotherUser->getId();

        $this->client->request('DELETE', '/user/delete/' . $userToDeleteId);

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        $this->assertEquals("L'utilisateur {$userToDeleteId} a été supprimé avec succès", $response['message']);
    }

    public function testDeleteUserAsRegularUser(): void
    {
        $this->loginUser($this->regularUser);
        $userToDeleteId = $this->anotherUser->getId();

        $this->client->request('DELETE', '/user/delete/' . $userToDeleteId);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteUserNotFound(): void
    {
        $this->loginUser($this->adminUser);

        $this->client->request('DELETE', '/user/delete/99999');

        $this->assertResponseStatusCodeSame(404);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Utilisateur introuvable', $response['message']);
    }

    public function testDeleteUserUnauthenticated(): void
    {
        $userToDeleteId = $this->anotherUser->getId();

        $this->client->request('DELETE', '/user/delete/' . $userToDeleteId);

        $this->assertResponseStatusCodeSame(401);
    }

    // Test de la structure de réponse pour /user/me
    public function testGetMeResponseStructure(): void
    {
        $this->loginUser($this->regularUser);

        $this->client->request('GET', '/user/me');

        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        $expectedKeys = ['id', 'email', 'nom', 'prenom', 'roles', 'isActive', 'dateCGU', 'avatar'];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $response, "La clé '$key' devrait être présente dans la réponse");
        }

        $this->assertIsInt($response['id']);
        $this->assertIsString($response['email']);
        $this->assertIsArray($response['roles']);
        $this->assertIsBool($response['isActive']);
        $this->assertIsString($response['dateCGU']);
    }

    // Test de mise à jour avec des données vides
    public function testUpdateMeWithEmptyData(): void
    {
        $this->loginUser($this->regularUser);

        $updateData = [];

        $this->client->request(
            'POST',
            '/user/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($response['success']);
        
        $this->assertEquals('User', $response['user']['nom']);
        $this->assertEquals('Regular', $response['user']['prenom']);
        $this->assertEquals('user@test.com', $response['user']['email']);
    }
}