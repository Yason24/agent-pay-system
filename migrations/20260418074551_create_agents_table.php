<?php

return [

    'up' => function(PDO $db) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS agents (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255)
            )
        ");
    },

    'down' => function(PDO $db) {
        $db->exec("DROP TABLE IF EXISTS agents");
    }

];