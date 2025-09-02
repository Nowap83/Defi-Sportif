<?php

namespace App\Tests\Entity;

use App\Entity\Defi;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DefiTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function createValidDefi(): Defi
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('hashedpassword');

        $defi = new Defi();
        $defi->setTitre('Marathon de Paris')
            ->setDescription('Un magnifique marathon dans les rues de Paris')
            ->setDateDefi(new \DateTimeImmutable('+1 month'))
            ->setTypeDefi('course')
            ->setRegion('Île-de-France')
            ->setPays('France')
            ->setDistance(42.195)
            ->setMinParticipant(10)
            ->setMaxParticipant(1000)
            ->setImage('marathon-paris.jpg')
            ->setCreateur($user);

        return $defi;
    }

    // Tests des getters et setters
    public function testGettersAndSetters(): void
    {
        $defi = new Defi();
        $user = new User();
        $date = new \DateTimeImmutable();

        $defi->setTitre('Test Titre')
            ->setDescription('Test Description')
            ->setDateDefi($date)
            ->setTypeDefi('course')
            ->setRegion('Test Région')
            ->setPays('Test Pays')
            ->setDistance(10.5)
            ->setMinParticipant(5)
            ->setMaxParticipant(50)
            ->setImage('test.jpg')
            ->setCreateur($user);

        $this->assertEquals('Test Titre', $defi->getTitre());
        $this->assertEquals('Test Description', $defi->getDescription());
        $this->assertEquals($date, $defi->getDateDefi());
        $this->assertEquals('course', $defi->getTypeDefi());
        $this->assertEquals('Test Région', $defi->getRegion());
        $this->assertEquals('Test Pays', $defi->getPays());
        $this->assertEquals(10.5, $defi->getDistance());
        $this->assertEquals(5, $defi->getMinParticipant());
        $this->assertEquals(50, $defi->getMaxParticipant());
        $this->assertEquals('test.jpg', $defi->getImage());
        $this->assertEquals($user, $defi->getCreateur());
        $this->assertNull($defi->getId()); // L'ID est généré par Doctrine
    }

    // Test de validation avec des données valides
    public function testValidDefi(): void
    {
        $defi = $this->createValidDefi();
        $violations = $this->validator->validate($defi);

        $this->assertCount(0, $violations);
    }

    // Tests de validation du titre
    public function testTitreNotBlank(): void
    {
        $defi = $this->createValidDefi();
        $defi->setTitre('');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('Le titre est obligatoire', $violations[0]->getMessage());
    }

    public function testTitreMinLength(): void
    {
        $defi = $this->createValidDefi();
        $defi->setTitre('ab'); // 2 caractères, minimum 3

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertStringContainsString('au moins 3 caractères', $violations[0]->getMessage());
    }

    public function testTitreMaxLength(): void
    {
        $defi = $this->createValidDefi();
        $defi->setTitre(str_repeat('a', 256)); // 256 caractères, maximum 255

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertStringContainsString('ne peut pas dépasser 255 caractères', $violations[0]->getMessage());
    }

    // Tests de validation de la description
    public function testDescriptionNotBlank(): void
    {
        $defi = $this->createValidDefi();
        $defi->setDescription('');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('La description est obligatoire', $violations[0]->getMessage());
    }

    public function testDescriptionMinLength(): void
    {
        $defi = $this->createValidDefi();
        $defi->setDescription('court'); // 5 caractères, minimum 10

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertStringContainsString('au moins 10 caractères', $violations[0]->getMessage());
    }

    // Tests de validation de la date

    public function testDateDefiInPast(): void
    {
        $defi = $this->createValidDefi();
        $defi->setDateDefi(new \DateTimeImmutable('-1 day')); // Date passée

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('La date du défi doit être dans le futur', $violations[0]->getMessage());
    }

    // Tests de validation du type de défi
    public function testTypeDefiNotBlank(): void
    {
        $defi = $this->createValidDefi();
        $defi->setTypeDefi('');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('Le type de défi est obligatoire', $violations[0]->getMessage());
    }

    public function testTypeDefiInvalidChoice(): void
    {
        $defi = $this->createValidDefi();
        $defi->setTypeDefi('invalid_type');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('Le type de défi choisi n\'est pas valide', $violations[0]->getMessage());
    }

    public function testTypeDefiValidChoices(): void
    {
        $validTypes = ['course', 'randonnee', 'velo', 'natation', 'triathlon', 'autre'];

        foreach ($validTypes as $type) {
            $defi = $this->createValidDefi();
            $defi->setTypeDefi($type);

            $violations = $this->validator->validate($defi);
            $this->assertCount(0, $violations, "Type '$type' should be valid");
        }
    }

    // Tests de validation de la région
    public function testRegionNotBlank(): void
    {
        $defi = $this->createValidDefi();
        $defi->setRegion('');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('La région est obligatoire', $violations[0]->getMessage());
    }

    // Tests de validation du pays
    public function testPaysNotBlank(): void
    {
        $defi = $this->createValidDefi();
        $defi->setPays('');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('Le pays est obligatoire', $violations[0]->getMessage());
    }

    // Tests de validation de la distance

    public function testDistancePositive(): void
    {
        $defi = $this->createValidDefi();
        $defi->setDistance(-5.0);

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('La distance doit être un nombre positif', $violations[0]->getMessage());
    }

    public function testDistanceMaxLimit(): void
    {
        $defi = $this->createValidDefi();
        $defi->setDistance(1001.0); // Dépasse la limite de 1000

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('La distance ne peut pas dépasser 1000 km', $violations[0]->getMessage());
    }

    // Tests de validation des participants

    public function testMinParticipantPositive(): void
    {
        $defi = $this->createValidDefi();
        $defi->setMinParticipant(-1);

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('Le nombre minimum de participants doit être positif', $violations[0]->getMessage());
    }


    public function testMaxParticipantPositive(): void
    {
        $defi = $this->createValidDefi();
        $defi->setMaxParticipant(-1);
        $violations = $this->validator->validate($defi);

        $this->assertGreaterThan(0, $violations->count());

        $messages = array_map(fn($v) => $v->getMessage(), iterator_to_array($violations));
        $this->assertContains('Le nombre maximum de participants doit être positif', $messages);
    }


    public function testParticipantsMinMaxRelation(): void
    {
        $defi = $this->createValidDefi();
        $defi->setMinParticipant(100);
        $defi->setMaxParticipant(50); // Max < Min

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());

        $violationMessages = [];
        foreach ($violations as $violation) {
            $violationMessages[] = $violation->getMessage();
        }

        $this->assertContains('Le nombre minimum de participants doit être inférieur au maximum', $violationMessages);
        $this->assertContains('Le nombre maximum de participants doit être supérieur au minimum', $violationMessages);
    }

    // Tests de validation de l'image
    public function testImageValidExtension(): void
    {
        $validExtensions = ['test.jpg', 'test.jpeg', 'test.png', 'test.gif', 'test.webp'];

        foreach ($validExtensions as $filename) {
            $defi = $this->createValidDefi();
            $defi->setImage($filename);

            $violations = $this->validator->validate($defi);
            $this->assertCount(0, $violations, "Extension '$filename' should be valid");
        }
    }

    public function testImageInvalidExtension(): void
    {
        $defi = $this->createValidDefi();
        $defi->setImage('test.txt');

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('L\'image doit avoir une extension valide (jpg, jpeg, png, gif, webp)', $violations[0]->getMessage());
    }

    // Test de validation du créateur
    public function testCreateurNotNull(): void
    {
        $defi = $this->createValidDefi();
        $defi->setCreateur(null);

        $violations = $this->validator->validate($defi);
        $this->assertGreaterThan(0, $violations->count());
        $this->assertEquals('Le créateur du défi est obligatoire', $violations[0]->getMessage());
    }

    // Test du fluent interface (méthodes chaînées)
    public function testFluentInterface(): void
    {
        $defi = new Defi();
        $user = new User();
        $date = new \DateTimeImmutable();

        $result = $defi->setTitre('Test')
            ->setDescription('Description test')
            ->setDateDefi($date)
            ->setTypeDefi('course')
            ->setRegion('Région')
            ->setPays('Pays')
            ->setDistance(10.0)
            ->setMinParticipant(1)
            ->setMaxParticipant(10)
            ->setImage('test.jpg')
            ->setCreateur($user);

        $this->assertInstanceOf(Defi::class, $result);
        $this->assertEquals('Test', $defi->getTitre());
    }
}
