<?php

namespace Framework\Core;

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

    public function run()
    {
        MigrationLock::lock($this->db);

        try {

            $this->db->beginTransaction();

            $files = glob(ROOT.'/migrations/*.php');

            $executed = $this->db
                ->query("SELECT migration FROM migrations")
                ->fetchAll(PDO::FETCH_COLUMN);

            $batch = $this->db
                ->query("SELECT COALESCE(MAX(batch),0)+1 FROM migrations")
                ->fetchColumn();

            foreach ($files as $file) {

                $name = basename($file);

                if (in_array($name, $executed)) {
                    continue;
                }

                echo "Running $name...\n";

                $migration = require $file;

                $migration['up']($this->db);

                $stmt = $this->db->prepare(
                    "INSERT INTO migrations (migration,batch) VALUES (?,?)"
                );

                $stmt->execute([$name,$batch]);
            }

            $this->db->commit();

            echo "Migrations complete ✅\n";

        } catch (\Throwable $e) {

            $this->db->rollBack();

            echo "Migration failed ❌\n";
            echo $e->getMessage()."\n";
        }

        MigrationLock::unlock($this->db);
    }

    public function rollback()
    {
        MigrationLock::lock($this->db);

        $this->db->beginTransaction();

        $batch = $this->db
            ->query("SELECT MAX(batch) FROM migrations")
            ->fetchColumn();

        if (!$batch) {
            echo "Nothing to rollback\n";
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT migration FROM migrations
         WHERE batch = ?
         ORDER BY id DESC"
        );

        $stmt->execute([$batch]);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $file) {

            echo "Rollback $file...\n";

            $migration = require ROOT."/migrations/$file";

            $migration['down']($this->db);

            $del = $this->db->prepare(
                "DELETE FROM migrations WHERE migration=?"
            );

            $del->execute([$file]);
        }

        $this->db->commit();

        MigrationLock::unlock($this->db);

        echo "Rollback complete ✅\n";
    }

    public function status()
    {
        $files = glob(ROOT.'/migrations/*.php');

        $executed = $this->db
            ->query("SELECT migration FROM migrations")
            ->fetchAll(PDO::FETCH_COLUMN);

        foreach ($files as $file) {

            $name = basename($file);

            $status = in_array($name,$executed)
                ? 'YES'
                : 'NO';

            echo str_pad($name,45)." | $status\n";
        }
    }
}