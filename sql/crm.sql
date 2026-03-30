CREATE DATABASE IF NOT EXISTS crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'admin',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE pipeline_stages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  slug VARCHAR(30) NOT NULL UNIQUE,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  company VARCHAR(160) NULL,
  phone VARCHAR(30) NULL,
  whatsapp VARCHAR(30) NULL,
  email VARCHAR(190) NULL,
  city VARCHAR(120) NULL,
  state VARCHAR(2) NULL,
  service_interest VARCHAR(100) NULL,
  lead_source VARCHAR(80) NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE lost_reasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE archive_reasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE opportunities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  lead_name VARCHAR(120) NOT NULL,
  service_interest VARCHAR(100) NULL,
  lead_source VARCHAR(80) NULL,
  stage_slug VARCHAR(30) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'ativo',
  estimated_value DECIMAL(12,2) NULL,
  closed_value DECIMAL(12,2) NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'media',
  temperature VARCHAR(20) NOT NULL DEFAULT 'morno',
  next_action VARCHAR(190) NULL,
  contact_type VARCHAR(60) NULL,
  next_followup DATE NULL,
  last_contact_at DATE NULL,
  notes TEXT NULL,
  tags VARCHAR(255) NULL,
  lost_reason_id INT NULL,
  archive_reason_id INT NULL,
  sale_date DATE NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_opp_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  CONSTRAINT fk_opp_lost FOREIGN KEY (lost_reason_id) REFERENCES lost_reasons(id),
  CONSTRAINT fk_opp_arch FOREIGN KEY (archive_reason_id) REFERENCES archive_reasons(id)
);

CREATE TABLE interactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  opportunity_id INT NOT NULL,
  type VARCHAR(40) NOT NULL,
  description TEXT NOT NULL,
  interaction_date DATE NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_inter_opp FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE
);

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  description VARCHAR(255) NULL,
  lead_id INT NULL,
  opportunity_id INT NULL,
  priority VARCHAR(20) NOT NULL DEFAULT 'media',
  due_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pendente',
  type VARCHAR(40) NOT NULL DEFAULT 'outro',
  notes TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_task_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT fk_task_opp FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE SET NULL
);

CREATE TABLE proposals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  opportunity_id INT NOT NULL,
  service VARCHAR(100) NULL,
  value DECIMAL(12,2) NOT NULL DEFAULT 0,
  sent_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'rascunho',
  close_chance INT NULL,
  expected_close_date DATE NULL,
  notes TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_prop_opp FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE
);

CREATE TABLE tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE lead_tags (
  lead_id INT NOT NULL,
  tag_id INT NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (lead_id, tag_id),
  CONSTRAINT fk_lt_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
  CONSTRAINT fk_lt_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  entity_type VARCHAR(40) NOT NULL,
  entity_id INT NOT NULL,
  action VARCHAR(80) NOT NULL,
  details TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_al_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (name,email,password_hash,role,created_at,updated_at)
VALUES ('Administrador','admin@crm.local','$2y$10$QFwYQ3vA7dStQfW8QOx8qunZ5nQivqR7M6a7v3fUZ9f4Nn4lq4Uvi','admin',NOW(),NOW());

INSERT INTO pipeline_stages (name,slug,is_closed,sort_order,created_at,updated_at) VALUES
('Contato','contato',0,1,NOW(),NOW()),('Qualificado','qualificado',0,2,NOW(),NOW()),('Estudo','estudo',0,3,NOW(),NOW()),('Reunião','reuniao',0,4,NOW(),NOW()),('Contrato','contrato',0,5,NOW(),NOW()),('Venda','venda',1,6,NOW(),NOW()),('Perdido','perdido',1,7,NOW(),NOW()),('Arquivado','arquivado',1,8,NOW(),NOW());

INSERT INTO lost_reasons (name,created_at,updated_at) VALUES
('preço',NOW(),NOW()),('sem resposta',NOW(),NOW()),('fechou com concorrente',NOW(),NOW()),('momento errado',NOW(),NOW()),('lead sem perfil',NOW(),NOW()),('desistiu',NOW(),NOW()),('outro',NOW(),NOW());

INSERT INTO archive_reasons (name,created_at,updated_at) VALUES
('sem retorno temporário',NOW(),NOW()),('projeto adiado',NOW(),NOW()),('contato inválido',NOW(),NOW()),('sem prioridade no momento',NOW(),NOW()),('aguardando momento futuro',NOW(),NOW()),('duplicado',NOW(),NOW()),('outro',NOW(),NOW());

INSERT INTO tags (name,created_at,updated_at) VALUES ('VIP',NOW(),NOW()),('Urgente',NOW(),NOW()),('Parceiro',NOW(),NOW());
