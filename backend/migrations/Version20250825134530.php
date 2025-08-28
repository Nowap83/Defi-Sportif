<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825134530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE defi ADD createur_id INT NOT NULL, ADD description VARCHAR(255) NOT NULL, ADD date_defi DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD type_defi VARCHAR(255) NOT NULL, ADD region VARCHAR(255) NOT NULL, ADD pays VARCHAR(255) NOT NULL, ADD distance DOUBLE PRECISION NOT NULL, ADD min_participant INT NOT NULL, ADD max_participant INT NOT NULL, ADD image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE defi ADD CONSTRAINT FK_DCD5A35E73A201E5 FOREIGN KEY (createur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_DCD5A35E73A201E5 ON defi (createur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE defi DROP FOREIGN KEY FK_DCD5A35E73A201E5');
        $this->addSql('DROP INDEX IDX_DCD5A35E73A201E5 ON defi');
        $this->addSql('ALTER TABLE defi DROP createur_id, DROP description, DROP date_defi, DROP type_defi, DROP region, DROP pays, DROP distance, DROP min_participant, DROP max_participant, DROP image');
    }
}
