<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910180805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posts_translation DROP created_at, DROP updated_at, DROP img_post, DROP img_width, DROP img_height, DROP srcset, CHANGE post_id post_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posts_translation ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD img_post VARCHAR(500) DEFAULT NULL, ADD img_width INT DEFAULT NULL, ADD img_height INT DEFAULT NULL, ADD srcset LONGTEXT DEFAULT NULL, CHANGE post_id post_id INT DEFAULT NULL');
    }
}
