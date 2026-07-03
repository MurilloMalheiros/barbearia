-- ============================================================
-- SCHEMA PostgreSQL - Barbearia
-- Execute: psql -U barbearia_user -d barbearia_db -f schema.sql
-- ============================================================

CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============================================================
-- TABELA: configuracoes
-- ============================================================
CREATE TABLE IF NOT EXISTS configuracoes (
    id SERIAL PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABELA: administradores
-- ============================================================
CREATE TABLE IF NOT EXISTS administradores (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP,
    ip_ultimo_login VARCHAR(45),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABELA: tentativas_login (brute force protection)
-- ============================================================
CREATE TABLE IF NOT EXISTS tentativas_login (
    id SERIAL PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    tentativas INTEGER DEFAULT 1,
    bloqueado_ate TIMESTAMP,
    ultima_tentativa TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_tentativas_ip ON tentativas_login(ip);

-- ============================================================
-- TABELA: logs_acesso_admin
-- ============================================================
CREATE TABLE IF NOT EXISTS logs_acesso_admin (
    id SERIAL PRIMARY KEY,
    admin_id INTEGER REFERENCES administradores(id) ON DELETE SET NULL,
    ip VARCHAR(45),
    acao VARCHAR(100),
    detalhes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABELA: servicos
-- ============================================================
CREATE TABLE IF NOT EXISTS servicos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    icone VARCHAR(100),
    preco NUMERIC(10,2),
    duracao_minutos INTEGER DEFAULT 60,
    ativo BOOLEAN DEFAULT TRUE,
    ordem INTEGER DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABELA: imagens (portfólio de trabalhos)
-- ============================================================
CREATE TABLE IF NOT EXISTS imagens (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    arquivo VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100),
    tamanho_bytes BIGINT,
    ativo BOOLEAN DEFAULT TRUE,
    ordem INTEGER DEFAULT 0,
    admin_id INTEGER REFERENCES administradores(id) ON DELETE SET NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_imagens_ativo ON imagens(ativo);

-- ============================================================
-- TABELA: horarios_disponiveis (configuração de agenda pelo admin)
-- ============================================================
CREATE TABLE IF NOT EXISTS horarios_disponiveis (
    id SERIAL PRIMARY KEY,
    dia_semana SMALLINT NOT NULL CHECK (dia_semana BETWEEN 0 AND 6),
    -- 0=Domingo, 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado
    hora_inicio TIME NOT NULL DEFAULT '08:00',
    hora_fim    TIME NOT NULL DEFAULT '18:00',
    duracao_minutos INTEGER NOT NULL DEFAULT 60 CHECK (duracao_minutos IN (30, 60, 90, 120)),
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (dia_semana)
);

-- ============================================================
-- TABELA: agendamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS agendamentos (
    id SERIAL PRIMARY KEY,
    cliente_nome VARCHAR(200) NOT NULL,
    cliente_telefone VARCHAR(20) NOT NULL,
    servico_id INTEGER REFERENCES servicos(id) ON DELETE SET NULL,
    data_agendamento DATE NOT NULL,
    horario TIME NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente'
        CHECK (status IN ('pendente', 'confirmado', 'cancelado', 'concluido')),
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_agendamentos_data ON agendamentos(data_agendamento);
CREATE INDEX IF NOT EXISTS idx_agendamentos_status ON agendamentos(status);
CREATE UNIQUE INDEX IF NOT EXISTS idx_agendamentos_slot
    ON agendamentos(data_agendamento, horario)
    WHERE status NOT IN ('cancelado');

-- ============================================================
-- TABELA: visitas
-- ============================================================
CREATE TABLE IF NOT EXISTS visitas (
    id BIGSERIAL PRIMARY KEY,
    ip_hash VARCHAR(64),
    user_agent TEXT,
    navegador VARCHAR(100),
    sistema_operacional VARCHAR(100),
    pagina VARCHAR(500),
    referrer VARCHAR(1000),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_visitas_criado   ON visitas(criado_em);
CREATE INDEX IF NOT EXISTS idx_visitas_ip_hash  ON visitas(ip_hash);

-- ============================================================
-- TRIGGER: atualiza atualizado_em automaticamente
-- ============================================================
CREATE OR REPLACE FUNCTION fn_atualizar_timestamp()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;

DO $$ DECLARE
    t TEXT;
BEGIN
    FOREACH t IN ARRAY ARRAY[
        'administradores','configuracoes','servicos','imagens',
        'horarios_disponiveis','agendamentos'
    ] LOOP
        EXECUTE format('
            DROP TRIGGER IF EXISTS trg_%I_ts ON %I;
            CREATE TRIGGER trg_%I_ts
            BEFORE UPDATE ON %I
            FOR EACH ROW EXECUTE FUNCTION fn_atualizar_timestamp();
        ', t, t, t, t);
    END LOOP;
END $$;

-- ============================================================
-- VIEWS ÚTEIS
-- ============================================================
CREATE OR REPLACE VIEW vw_visitas_diarias AS
SELECT DATE(criado_em) AS dia, COUNT(*) AS total
FROM visitas
WHERE criado_em >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY DATE(criado_em)
ORDER BY dia;

CREATE OR REPLACE VIEW vw_visitas_navegador AS
SELECT COALESCE(navegador, 'Desconhecido') AS navegador, COUNT(*) AS total
FROM visitas GROUP BY navegador ORDER BY total DESC;

CREATE OR REPLACE VIEW vw_visitas_so AS
SELECT COALESCE(sistema_operacional, 'Desconhecido') AS sistema_operacional, COUNT(*) AS total
FROM visitas GROUP BY sistema_operacional ORDER BY total DESC;

CREATE OR REPLACE VIEW vw_agendamentos_hoje AS
SELECT a.*, s.nome AS servico_nome
FROM agendamentos a
LEFT JOIN servicos s ON s.id = a.servico_id
WHERE a.data_agendamento = CURRENT_DATE
ORDER BY a.horario;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================
INSERT INTO configuracoes (chave, valor) VALUES
    ('empresa_nome',       'Barbearia Estilo'),
    ('empresa_whatsapp',   '5511999999999'),
    ('empresa_descricao',  'Barbearia clássica com serviços premium. Cortes modernos e tradicionais para o homem contemporâneo.'),
    ('empresa_logo',       ''),
    ('hero_image',         ''),
    ('seo_title',          'Barbearia Estilo - Cortes & Barba Premium'),
    ('seo_description',    'Barbearia especializada em cortes modernos, barba e tratamentos masculinos. Agende seu horário online.'),
    ('seo_keywords',       'barbearia, corte de cabelo, barba, barbeiro, cabelo masculino, navalha'),
    ('og_image',           ''),
    ('google_analytics',   ''),
    ('endereco',           ''),
    ('email_contato',      ''),
    ('instagram',          ''),
    ('facebook',           '')
ON CONFLICT (chave) DO NOTHING;

-- Horários padrão: Segunda a Sábado, 08:00–18:00, intervalos de 1h
INSERT INTO horarios_disponiveis (dia_semana, hora_inicio, hora_fim, duracao_minutos, ativo) VALUES
    (0, '08:00', '18:00', 60, FALSE),  -- Domingo (inativo por padrão)
    (1, '08:00', '18:00', 60, TRUE),   -- Segunda
    (2, '08:00', '18:00', 60, TRUE),   -- Terça
    (3, '08:00', '18:00', 60, TRUE),   -- Quarta
    (4, '08:00', '18:00', 60, TRUE),   -- Quinta
    (5, '08:00', '18:00', 60, TRUE),   -- Sexta
    (6, '08:00', '13:00', 60, TRUE)    -- Sábado (meio período)
ON CONFLICT (dia_semana) DO NOTHING;

-- Serviços iniciais
INSERT INTO servicos (nome, descricao, icone, preco, duracao_minutos, ordem) VALUES
    ('Corte de Cabelo',      'Corte clássico ou moderno com acabamento impecável.',   '✂️',  35.00, 60, 1),
    ('Barba',                'Modelagem e aparagem de barba com navalha e toalha quente.', '🪒', 25.00, 45, 2),
    ('Corte + Barba',        'Combo completo com corte e barba por um preço especial.',    '💈', 55.00, 90, 3),
    ('Pigmentação de Barba', 'Coloração e pigmentação para barba com produtos premium.',   '🎨', 45.00, 60, 4),
    ('Hidratação Capilar',   'Tratamento profundo para cabelos ressecados e danificados.', '💧', 40.00, 60, 5)
ON CONFLICT DO NOTHING;

-- Administrador padrão (senha: Admin@2024 - TROQUE IMEDIATAMENTE)
INSERT INTO administradores (nome, email, senha_hash)
VALUES (
    'Administrador',
    'admin@barbearia.com.br',
    '$2y$12$6NZBBlqRjJaqQgp.NfD2MeQJH8XFocjABRpHvbDpJYPqiVLsJkQDm'
) ON CONFLICT (email) DO NOTHING;
