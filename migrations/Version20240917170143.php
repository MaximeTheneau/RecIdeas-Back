<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240917170143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph_posts ADD posts_translation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph_posts ADD CONSTRAINT FK_CEE77992BDF81E77 FOREIGN KEY (posts_translation_id) REFERENCES posts_translation (id)');
        $this->addSql('CREATE INDEX IDX_CEE77992BDF81E77 ON paragraph_posts (posts_translation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph_posts DROP FOREIGN KEY FK_CEE77992BDF81E77');
        $this->addSql('DROP INDEX IDX_CEE77992BDF81E77 ON paragraph_posts');
        $this->addSql('ALTER TABLE paragraph_posts DROP posts_translation_id');
    }
}
