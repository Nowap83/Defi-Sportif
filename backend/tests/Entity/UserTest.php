<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    private function getValidUser(): User
    {
        $user = new User();
        $user->setEmail("test@example.com")
             ->setPassword("password123")
             ->setNom("Dupont")
             ->setPrenom("Jean")
             ->setIsActive(true)
             ->setDateCGU(new \DateTimeImmutable())
             ->setCreatedAt(new \DateTimeImmutable())
             ->setAvatar("https://example.com/avatar.png")
             ->setRoles(["ROLE_USER"]);

        return $user;
    }

    public function testValidUser(): void
    {
        $user = $this->getValidUser();
        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors, "Un utilisateur valide ne doit pas générer d'erreurs.");
    }

    public function testInvalidEmail(): void
    {
        $user = $this->getValidUser();
        $user->setEmail("invalid-email");

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), "Un email invalide doit générer une erreur.");
    }

    public function testBlankPassword(): void
    {
        $user = $this->getValidUser();
        $user->setPassword("");

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), "Un mot de passe vide doit générer une erreur.");
    }

    public function testShortPassword(): void
    {
        $user = $this->getValidUser();
        $user->setPassword("123");

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), "Un mot de passe trop court doit générer une erreur.");
    }

    public function testBlankNom(): void
    {
        $user = $this->getValidUser();
        $user->setNom("");

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), "Un nom vide doit générer une erreur.");
    }

    public function testBlankPrenom(): void
    {
        $user = $this->getValidUser();
        $user->setPrenom("");

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), "Un prénom vide doit générer une erreur.");
    }

    public function testInvalidAvatarUrl(): void
    {
        $user = $this->getValidUser();
        $user->setAvatar("not-a-valid-url");

        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors), "Un avatar invalide doit générer une erreur.");
    }
}
