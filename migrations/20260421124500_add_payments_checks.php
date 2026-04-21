<?php

return [

    'up' => function (PDO $db) {
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_amount_positive_check');
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_allowed_check');

        $db->exec('ALTER TABLE payments ADD CONSTRAINT payments_amount_positive_check CHECK (amount > 0)');
        $db->exec("ALTER TABLE payments ADD CONSTRAINT payments_status_allowed_check CHECK (status IN ('pending','paid','failed'))");
    },

    'down' => function (PDO $db) {
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_amount_positive_check');
        $db->exec('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_allowed_check');
    },

];

