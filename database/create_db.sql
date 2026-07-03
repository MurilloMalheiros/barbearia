-- Cria o role e o banco de dados para a barbearia
-- Execute como superusuário: psql -U postgres -f create_db.sql

DO $$ BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = 'barbearia_user') THEN
        CREATE ROLE barbearia_user WITH LOGIN PASSWORD 'TROQUE_ESTA_SENHA';
    END IF;
END $$;

SELECT 'CREATE DATABASE barbearia_db OWNER barbearia_user ENCODING ''UTF8'' LC_COLLATE ''pt_BR.UTF-8'' LC_CTYPE ''pt_BR.UTF-8'' TEMPLATE template0'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'barbearia_db')\gexec

GRANT ALL PRIVILEGES ON DATABASE barbearia_db TO barbearia_user;
