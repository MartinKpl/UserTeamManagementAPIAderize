<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615091434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE teams (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users_teams (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, role VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users_teams_users (users_teams_id INTEGER NOT NULL, users_id INTEGER NOT NULL, PRIMARY KEY(users_teams_id, users_id), CONSTRAINT FK_170861B6AD7774C8 FOREIGN KEY (users_teams_id) REFERENCES users_teams (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_170861B667B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_170861B6AD7774C8 ON users_teams_users (users_teams_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_170861B667B3B43D ON users_teams_users (users_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users_teams_teams (users_teams_id INTEGER NOT NULL, teams_id INTEGER NOT NULL, PRIMARY KEY(users_teams_id, teams_id), CONSTRAINT FK_9549E607AD7774C8 FOREIGN KEY (users_teams_id) REFERENCES users_teams (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9549E607D6365F12 FOREIGN KEY (teams_id) REFERENCES teams (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9549E607AD7774C8 ON users_teams_teams (users_teams_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9549E607D6365F12 ON users_teams_teams (teams_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE teams
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users_teams
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users_teams_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users_teams_teams
        SQL);
    }
}
