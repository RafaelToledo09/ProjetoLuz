CREATE TABLE `Cliente`(
    `id_cliente` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome` TEXT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `telefone` VARCHAR(255) NOT NULL
);
CREATE TABLE `Produtos`(
    `id_produto` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `descricao` TEXT NOT NULL,
    `preco` DECIMAL(8, 2) NOT NULL,
    `imagem` VARCHAR(255) NOT NULL
);
CREATE TABLE `pedido`(
    `id_pedido` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` BIGINT NOT NULL,
    `data_pedido` DATETIME NOT NULL,
    `status` VARCHAR(255) NOT NULL,
    `total` DECIMAL(8, 2) NOT NULL
);
ALTER TABLE
    `pedido` ADD INDEX `pedido_id_cliente_index`(`id_cliente`);
CREATE TABLE `Itens_pedido`(
    `id_pedido` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_produto` BIGINT NOT NULL,
    `quantidade` INT NOT NULL,
    `preco_unitario` DECIMAL(8, 2) NOT NULL
);
CREATE TABLE `Endereço`(
    `id_endereço` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id_cliente` BIGINT NOT NULL,
    `cep` VARCHAR(255) NOT NULL,
    `rua` VARCHAR(255) NOT NULL,
    `numero` VARCHAR(255) NOT NULL,
    `complemento` VARCHAR(255) NOT NULL,
    `bairro` VARCHAR(255) NOT NULL,
    `cidade` VARCHAR(255) NOT NULL,
    `estado` VARCHAR(255) NOT NULL
);
ALTER TABLE
    `Itens_pedido` ADD CONSTRAINT `itens_pedido_id_pedido_foreign` FOREIGN KEY(`id_pedido`) REFERENCES `pedido`(`id_pedido`);
ALTER TABLE
    `pedido` ADD CONSTRAINT `pedido_id_cliente_foreign` FOREIGN KEY(`id_cliente`) REFERENCES `Cliente`(`id_cliente`);
ALTER TABLE
    `Itens_pedido` ADD CONSTRAINT `itens_pedido_id_produto_foreign` FOREIGN KEY(`id_produto`) REFERENCES `Produtos`(`id_produto`);
ALTER TABLE
    `Endereço` ADD CONSTRAINT `endereço_id_cliente_foreign` FOREIGN KEY(`id_cliente`) REFERENCES `Cliente`(`id_cliente`);