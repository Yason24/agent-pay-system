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
                batch INT,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function run(): void
    {
        $files = glob(ROOT . '/migrations/*.php');

        $executed = $this->db
            ->query("SELECT migration FROM migrations")
            ->fetchAll(\PDO::FETCH_COLUMN);

        $batch = (int)$this->db
            ->query("SELECT COALESCE(MAX(batch),0)+1 FROM migrations")
            ->fetchColumn();

        foreach ($files as $file) {

            $name = basename($file);

            if (in_array($name, $executed)) {
                continue;
            }

            echo "Running $name...\n";

            $migration = require $file;

            $migration($this->db);

            $stmt = $this->db->prepare(
                "INSERT INTO migrations (migration, batch) VALUES (?, ?)"
            );

            $stmt->execute([$name, $batch]);
        }

        echo "Migrations complete ✅\n";
    }

    public function rollback(): void
    {
        $batch = $this->db
            ->query("SELECT MAX(batch) FROM migrations")
            ->fetchColumn();

        if (!$batch) {
            echo "Nothing to rollback\n";
            return;
        }

        $migrations = $this->db
            ->prepare("SELECT migration FROM migrations WHERE batch=? ORDER BY id DESC");

        $migrations->execute([$batch]);

        foreach ($migrations->fetchAll(\PDO::FETCH_COLUMN) as $fileName) {

            echo "Rollback $fileName...\n";

            $migration = require ROOT . "/migrations/$fileName";

            if (is_array($migration) && isset($migration['down'])) {
                $migration['down']($this->db);
            }

            $this->db
                ->prepare("DELETE FROM migrations WHERE migration=?")
                ->execute([$fileName]);
        }

        echo "Rollback complete ✅\n";
    }
}