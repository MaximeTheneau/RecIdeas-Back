<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240920170749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posts_translation DROP FOREIGN KEY FK_682D784C12469DE2');
        $this->addSql('DROP INDEX IDX_682D784C12469DE2 ON posts_translation');
        $this->addSql('ALTER TABLE posts_translation DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE posts_translation ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE posts_translation ADD CONSTRAINT FK_682D784C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_682D784C12469DE2 ON posts_translation (category_id)');
    }
}
