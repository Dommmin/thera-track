<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609211846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD phone VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER email TYPE VARCHAR(50)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER first_name TYPE VARCHAR(20)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER last_name TYPE VARCHAR(20)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP phone
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER email TYPE VARCHAR(180)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER first_name TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER last_name TYPE VARCHAR(255)
        SQL);
    }
}
