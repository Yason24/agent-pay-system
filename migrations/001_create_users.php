<?php

return [

    'up' => function (PDO $db) {
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255),
                email VARCHAR(255),
                password VARCHAR(255),
                role VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    },

    'down' => function (PDO $db) {
        $db->exec("DROP TABLE IF EXISTS users");
    },

];
