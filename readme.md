# Banco de dados 
CREATE DATABASE IF NOT EXISTS catalogomusica
USE catalogomusica;

CREATE TABLE IF NOT EXISTS musicas (
    id INT(11) NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    artista VARCHAR(150) NOT NULL,
    album VARCHAR(150) DEFAULT NULL,
    ano INT(11) DEFAULT NULL,
    genero VARCHAR(100) DEFAULT NULL,
    PRIMARY KEY (id)
