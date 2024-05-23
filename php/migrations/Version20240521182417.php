<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240521182417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "
        CREATE TABLE tasks (
  id bigint NOT NULL primary key,
  title text NOT NULL,
  description text NOT NULL
)"
        );

        $this->addSql(
            "CREATE TABLE tasks_users (
  id serial NOT NULL primary key ,
  task_id bigint NOT NULL,
  user_id bigint NOT NULL,
  completed boolean NOT NULL
)"
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("tasks_users");
        $schema->dropTable("tasks");
    }
}
