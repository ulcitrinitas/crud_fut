CREATE DATABASE futebol;

USE futebol;

CREATE TABLE uniformes (
id INT primary key AUTO_INCREMENT,
tipo ENUM('calção','meião','camisa') DEFAULT NULL,
nome VARCHAR(255) DEFAULT NULL,
foto VARCHAR(255) DEFAULT NULL,
-- PRIMARY KEY (id),
KEY roupas_idx_tipo (tipo)
);

CREATE TABLE tecnicos (
id INT primary key AUTO_INCREMENT,
nome VARCHAR(255) DEFAULT NULL,
descricao MEDIUMTEXT,
foto VARCHAR(255) DEFAULT NULL,
data_nascimento DATE DEFAULT NULL,
data_falecimento DATE DEFAULT NULL,
time TINYINT(1) DEFAULT NULL
) ;

CREATE TABLE competicoes (
id INT primary key AUTO_INCREMENT,
nome VARCHAR(255) DEFAULT NULL,
internacional TINYINT(1) DEFAULT NULL
);

create table pais(
pais_id int primary key auto_increment,
nome_pais varchar(100)
);

create table estado(
estado_id int primary key auto_increment,
nome_estado varchar(100),
pais_id int,
foreign key (pais_id) references pais(pais_id)
);

CREATE TABLE cidades (
id INT primary key AUTO_INCREMENT,
estado_id INT,
nome VARCHAR(100) DEFAULT NULL,
foreign key (estado_id) references estado(estado_id)
);

CREATE TABLE posicoes (
id INT primary key AUTO_INCREMENT,
nome VARCHAR(255) DEFAULT NULL
);

DROP TABLE IF EXISTS jogadores;
CREATE TABLE jogadores (
id INT primary key AUTO_INCREMENT,

posicao_id INT,
gols_sofridos INT DEFAULT NULL,
nome VARCHAR(255) DEFAULT NULL,
nome_real VARCHAR(255) DEFAULT NULL,
descricao MEDIUMTEXT,
titulos MEDIUMTEXT,
foto VARCHAR(255) DEFAULT NULL,
data_nascimento DATE DEFAULT NULL,
data_falecimento DATE DEFAULT NULL,
time TINYINT(1) DEFAULT NULL,
FOREIGN KEY (posicao_id) REFERENCES posicoes(id),
FOREIGN KEY (posicao_id) REFERENCES posicoes(id)
);

show tables;

create table usuarios (
	user_id int auto_increment primary key,
    username varchar(255) not null,
    pass char(60) not null,
    created_at datetime default now() not null,
    updated_at datetime default now() not null
);

create table usuarios_info(
	info_id int auto_increment primary key,
    nome varchar(255) not null,
    dt_nascimento date not null,
    cpf char(14) not null,
    user_id int not null,
    foreign key (user_id) references usuarios(user_id)
);


