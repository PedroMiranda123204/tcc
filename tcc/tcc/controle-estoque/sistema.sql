CREATE DATABASE sistema;

USE sistema;

CREATE TABLE produtos (

    id INT AUTO_INCREMENT PRIMARY KEY,

    codigo VARCHAR(20) NOT NULL,

    nome VARCHAR(100) NOT NULL,

	lote VARCHAR(50) NOT NULL,

    quantidade INT NOT NULL,

    preco DECIMAL(10,2) NOT NULL,

    validade DATE,

    data_compra DATE,

    categoria VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

CREATE TABLE IF NOT EXISTS avisos (
id INT AUTO_INCREMENT PRIMARY KEY,
produto_id INT,
mensagem TEXT NOT NULL,
tipo VARCHAR(20) DEFAULT 'warning', -- 'warning', 'danger', 'info'
lido TINYINT(1) DEFAULT 0,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);