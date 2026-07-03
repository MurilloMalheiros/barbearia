-- Setup do schema barbearia dentro do malharia_db
-- Execute: psql -U malharia_user -h 127.0.0.1 -d malharia_db -f database/setup_schema.sql

SET client_encoding TO 'UTF8';

-- Cria e configura o schema isolado
CREATE SCHEMA IF NOT EXISTS barbearia;
SET search_path TO barbearia, public;

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
-- TABELA: tentativas_login
-- ============================================================
CREATE TABLE IF NOT EXISTS tentativas_login (
    id SERIAL PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    tentativas INTEGER DEFAULT 1,
    bloqueado_ate TIMESTAMP,
    ultima_tentativa TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_b_tentativas_ip ON tentativas_login(ip);

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
-- TABELA: imagens
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
CREATE INDEX IF NOT EXISTS idx_b_imagens_ativo ON imagens(ativo);

-- ============================================================
-- TABELA: horarios_disponiveis
-- ============================================================
CREATE TABLE IF NOT EXISTS horarios_disponiveis (
    id SERIAL PRIMARY KEY,
    dia_semana SMALLINT NOT NULL CHECK (dia_semana BETWEEN 0 AND 6),
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
CREATE INDEX IF NOT EXISTS idx_b_agendamentos_data   ON agendamentos(data_agendamento);
CREATE INDEX IF NOT EXISTS idx_b_agendamentos_status ON agendamentos(status);
CREATE UNIQUE INDEX IF NOT EXISTS idx_b_agendamentos_slot
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
CREATE INDEX IF NOT EXISTS idx_b_visitas_criado ON visitas(criado_em);

-- ============================================================
-- VIEWS
-- ============================================================
CREATE OR REPLACE VIEW vw_visitas_diarias AS
SELECT DATE(criado_em) AS dia, COUNT(*) AS total
FROM visitas
WHERE criado_em >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY DATE(criado_em) ORDER BY dia;

CREATE OR REPLACE VIEW vw_visitas_navegador AS
SELECT COALESCE(navegador, 'Desconhecido') AS navegador, COUNT(*) AS total
FROM visitas GROUP BY navegador ORDER BY total DESC;

CREATE OR REPLACE VIEW vw_visitas_so AS
SELECT COALESCE(sistema_operacional, 'Desconhecido') AS sistema_operacional, COUNT(*) AS total
FROM visitas GROUP BY sistema_operacional ORDER BY total DESC;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================
INSERT INTO configuracoes (chave, valor) VALUES
    ('empresa_nome',       'Barbearia Estilo'),
    ('empresa_whatsapp',   '5511999999999'),
    ('empresa_descricao',  'Barbearia clássica com serviços premium. Cortes modernos e tradicionais.'),
    ('empresa_logo',       ''),
    ('hero_image',         ''),
    ('seo_title',          'Barbearia Estilo - Cortes & Barba Premium'),
    ('seo_description',    'Barbearia especializada em cortes modernos, barba e tratamentos masculinos.'),
    ('seo_keywords',       'barbearia, corte, barba, barbeiro'),
    ('og_image',           ''),
    ('google_analytics',   ''),
    ('endereco',           ''),
    ('email_contato',      ''),
    ('instagram',          ''),
    ('facebook',           '')
ON CONFLICT (chave) DO NOTHING;

INSERT INTO horarios_disponiveis (dia_semana, hora_inicio, hora_fim, duracao_minutos, ativo) VALUES
    (0, '08:00', '18:00', 60, FALSE),
    (1, '08:00', '18:00', 60, TRUE),
    (2, '08:00', '18:00', 60, TRUE),
    (3, '08:00', '18:00', 60, TRUE),
    (4, '08:00', '18:00', 60, TRUE),
    (5, '08:00', '18:00', 60, TRUE),
    (6, '08:00', '13:00', 60, TRUE)
ON CONFLICT (dia_semana) DO NOTHING;

INSERT INTO servicos (nome, descricao, icone, preco, duracao_minutos, ordem) VALUES
    ('Corte de Cabelo',      'Corte clássico ou moderno com acabamento impecável.',  '✂️',  35.00, 60, 1),
    ('Barba',                'Modelagem com navalha e toalha quente.',               '🪒',  25.00, 45, 2),
    ('Corte + Barba',        'Combo completo com desconto especial.',                '💈',  55.00, 90, 3),
    ('Pigmentação de Barba', 'Coloração para barba com produtos premium.',           '🎨',  45.00, 60, 4),
    ('Hidratação Capilar',   'Tratamento profundo para cabelos ressecados.',         '💧',  40.00, 60, 5)
ON CONFLICT DO NOTHING;

-- Admin padrão (senha: Admin@2024)
INSERT INTO administradores (nome, email, senha_hash)
VALUES (
    'Administrador',
    'admin@barbearia.com.br',
    '$2y$12$YrLa4ITKNVMlHFXoRTlOSOvWD.RcEOdFl.JCWKk2GA33gnFzLvbxW'
) ON CONFLICT (email) DO NOTHING;

\echo '>>> Schema barbearia criado com sucesso!'
