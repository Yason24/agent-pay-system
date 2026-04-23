BEGIN;

-- 1) Телефон: уникальны только непустые значения
DROP INDEX IF EXISTS idx_users_phone_unique;
CREATE UNIQUE INDEX IF NOT EXISTS idx_users_phone_unique
    ON users ((NULLIF(BTRIM(phone), '')))
    WHERE NULLIF(BTRIM(phone), '') IS NOT NULL;

-- 2) Email может дублироваться: удаляем уникальные индексы по email на users
DO $$
DECLARE
    idx RECORD;
BEGIN
    FOR idx IN
        SELECT indexname
        FROM pg_indexes
        WHERE schemaname = 'public'
          AND tablename = 'users'
          AND indexdef ILIKE '%UNIQUE%'
          AND indexdef ILIKE '%email%'
    LOOP
        EXECUTE format('DROP INDEX IF EXISTS %I', idx.indexname);
    END LOOP;
END $$;

-- 3) Роли по ТЗ
UPDATE users SET role = 'admin'      WHERE id = 1;
UPDATE users SET role = 'dispatcher' WHERE id = 2;
UPDATE users SET role = 'agent'      WHERE id = 3;
UPDATE users SET role = 'agent'      WHERE id = 4;
UPDATE users SET role = 'admin'      WHERE id = 5;
UPDATE users SET role = 'accountant' WHERE id = 6;

-- 4) Реалистичные логины и профили
UPDATE users
SET
    last_name = 'Иванов',
    first_name = 'Алексей',
    middle_name = 'Петрович',
    login = 'admin1',
    phone = '+70000000001',
    city = 'Москва',
    name = 'Иванов Алексей Петрович'
WHERE id = 1;

UPDATE users
SET
    last_name = 'Смирнов',
    first_name = 'Денис',
    middle_name = 'Олегович',
    login = 'dispatcher1',
    phone = '+70000000002',
    city = 'Кемерово',
    name = 'Смирнов Денис Олегович'
WHERE id = 2;

UPDATE users
SET
    last_name = 'Кузнецов',
    first_name = 'Игорь',
    middle_name = 'Сергеевич',
    login = 'agent1',
    phone = '+70000000003',
    city = 'Томск',
    name = 'Кузнецов Игорь Сергеевич'
WHERE id = 3;

UPDATE users
SET
    last_name = 'Попова',
    first_name = 'Марина',
    middle_name = 'Андреевна',
    login = 'agent2',
    phone = '+70000000004',
    city = 'Новосибирск',
    name = 'Попова Марина Андреевна'
WHERE id = 4;

UPDATE users
SET
    last_name = 'Соколова',
    first_name = 'Елена',
    middle_name = 'Викторовна',
    login = 'admin2',
    phone = '+70000000005',
    city = 'Санкт-Петербург',
    name = 'Соколова Елена Викторовна'
WHERE id = 5;

UPDATE users
SET
    last_name = 'Орлов',
    first_name = 'Максим',
    middle_name = 'Игоревич',
    login = 'accountant1',
    phone = '+70000000006',
    city = 'Екатеринбург',
    name = 'Орлов Максим Игоревич'
WHERE id = 6;

COMMIT;

