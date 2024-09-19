<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919162304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph_posts_translation DROP FOREIGN KEY FK_4A6449B6818985F6');
        $this->addSql('DROP INDEX IDX_4A6449B6818985F6 ON paragraph_posts_translation');
        $this->addSql('ALTER TABLE paragraph_posts_translation DROP paragraph_posts_id_id');
        $this->addSql('DROP INDEX UNIQ_885DBAFA989D9B62 ON posts');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX UNIQ_885DBAFA989D9B62 ON posts (slug)');
        $this->addSql('ALTER TABLE paragraph_posts_translation ADD paragraph_posts_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph_posts_translation ADD CONSTRAINT FK_4A6449B6818985F6 FOREIGN KEY (paragraph_posts_id_id) REFERENCES paragraph_posts (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4A6449B6818985F6 ON paragraph_posts_translation (paragraph_posts_id_id)');
    }
}
