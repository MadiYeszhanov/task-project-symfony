<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925063658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @throws \Exception
     */
    public function up(Schema $schema): void
    {
        // пароль 12345
        $this->addSql("INSERT INTO public.user (email, roles, password) VALUES ('user@example.com', '[\"USER\"]', '$2y$13$9vavuZz.v7oz8MSie2Ssq.cjWU9v/VFXZhhx9n6yv/69lG82f0T0a')");
    }

    public function down(Schema $schema): void
    {

    }
}
