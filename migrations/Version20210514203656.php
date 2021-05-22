<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210514203656 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE album ADD image1 VARCHAR(255) NOT NULL, ADD image2 VARCHAR(255) NOT NULL, ADD image3 VARCHAR(255) NOT NULL, DROP popularity');
        $this->addSql('ALTER TABLE artist ADD image1 VARCHAR(255) NOT NULL, ADD image2 VARCHAR(255) NOT NULL, ADD image3 VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE track ADD image1 VARCHAR(255) NOT NULL, ADD image2 VARCHAR(255) NOT NULL, ADD image3 VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE album ADD popularity INT NOT NULL, DROP image1, DROP image2, DROP image3');
        $this->addSql('ALTER TABLE artist DROP image1, DROP image2, DROP image3');
        $this->addSql('ALTER TABLE track DROP image1, DROP image2, DROP image3');
    }
}
