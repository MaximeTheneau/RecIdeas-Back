<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240917164253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paragraph_posts (id INT AUTO_INCREMENT NOT NULL, subtitle VARCHAR(170) DEFAULT NULL, paragraph VARCHAR(5000) DEFAULT NULL, img_post_paragh VARCHAR(500) DEFAULT NULL, alt_img VARCHAR(170) DEFAULT NULL, slug VARCHAR(50) DEFAULT NULL, link VARCHAR(500) DEFAULT NULL, link_subtitle VARCHAR(255) DEFAULT NULL, img_post_paragh_file VARCHAR(255) DEFAULT NULL, img_width INT DEFAULT NULL, img_height INT DEFAULT NULL, img_post VARCHAR(500) DEFAULT NULL, srcset LONGTEXT DEFAULT NULL, posts_id INT DEFAULT NULL, INDEX IDX_CEE77992D5E258C5 (posts_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE paragraph_posts ADD CONSTRAINT FK_CEE77992D5E258C5 FOREIGN KEY (posts_id) REFERENCES posts (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph_posts DROP FOREIGN KEY FK_CEE77992D5E258C5');
        $this->addSql('DROP TABLE paragraph_posts');
    }
}
