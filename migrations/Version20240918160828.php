<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240918160828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph_posts_translation DROP FOREIGN KEY FK_4A6449B6BDF81E77');
        $this->addSql('DROP INDEX IDX_4A6449B6BDF81E77 ON paragraph_posts_translation');
        $this->addSql('ALTER TABLE paragraph_posts_translation DROP posts_translation_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph_posts_translation ADD posts_translation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph_posts_translation ADD CONSTRAINT FK_4A6449B6BDF81E77 FOREIGN KEY (posts_translation_id) REFERENCES posts_translation (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4A6449B6BDF81E77 ON paragraph_posts_translation (posts_translation_id)');
    }
}