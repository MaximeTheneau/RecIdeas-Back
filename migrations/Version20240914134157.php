<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240914134157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE translate_translation ADD translate_id INT DEFAULT NULL, DROP translate');
        $this->addSql('ALTER TABLE translate_translation ADD CONSTRAINT FK_1F2267A2649893AF FOREIGN KEY (translate_id) REFERENCES translate (id)');
        $this->addSql('CREATE INDEX IDX_1F2267A2649893AF ON translate_translation (translate_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE translate_translation DROP FOREIGN KEY FK_1F2267A2649893AF');
        $this->addSql('DROP INDEX IDX_1F2267A2649893AF ON translate_translation');
        $this->addSql('ALTER TABLE translate_translation ADD translate VARCHAR(1000) NOT NULL, DROP translate_id');
    }
}
