<?php

namespace Yason\WebsiteTemplate\Core;

use PDO;

class MigrationRunner
{
    private PDO $db;

    public function __construct()
    {
        Env::load(ROOT . '/.env');
        $this->db = Database::getConnection();

        $this->createMigrationsTable();
    }

    private function createMigrationsTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                migration VARCHAR(255),
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function run(): void
    {
        $files = glob(ROOT . '/migrations/*.php');

        $executed = $this->db
            ->query("SELECT migration FROM migrations")
            ->fetchAll(PDO::FETCH_COLUMN);

        foreach ($files as $file) {

            $name = basename($file);

            if (in_array($name, $executed)) {
                continue;
            }

            echo "Running $name...\n";

            $migration = require $file;

            $migration($this->db);

            $stmt = $this->db->prepare(
                "INSERT INTO migrations (migration) VALUES (?)"
            );

            $stmt->execute([$name]);
        }

        echo "Migrations complete ✅\n";
    }
}