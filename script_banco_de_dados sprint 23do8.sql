-- ==========================================
-- SCRIPT DO BANCO DE DADOS - DASHBOARD
-- PROJETO LUZ
-- ==========================================


CREATE OR REPLACE VIEW vw_dados_dashboard AS
SELECT c.nome AS nome_cliente, p.data_pedido, pr.nome AS nome_produto, (ip.quantidade * ip.preco_unitario) AS valor_total_item
FROM pedido p
JOIN Cliente c ON p.id_cliente = c.id_cliente
JOIN Itens_pedido ip ON p.id_pedido = ip.id_pedido
JOIN Produtos pr ON ip.id_produto = pr.id_produto;


DROP PROCEDURE IF EXISTS sp_buscar_vendas_dashboard //

CREATE PROCEDURE sp_buscar_vendas_dashboard(
    IN p_nome_cliente VARCHAR(100), 
    IN p_limite INT,
    IN p_offset INT
)
BEGIN
    SELECT * 
    FROM vw_dados_dashboard
    WHERE p_nome_cliente = '' OR nome_cliente LIKE CONCAT('%', p_nome_cliente, '%')
    ORDER BY data_pedido DESC
    LIMIT p_limite OFFSET p_offset;
END //

DELIMITER ;


DELIMITER //

DROP TRIGGER IF EXISTS trg_padroniza_preco_positivo //

CREATE TRIGGER trg_padroniza_preco_positivo
BEFORE UPDATE ON Produtos
FOR EACH ROW
BEGIN
    
    IF NEW.preco < 0 THEN
        SET NEW.preco = ABS(NEW.preco);
    END IF;
END //

DELIMITER ;

DROP FUNCTION IF EXISTS fn_calcula_total_item //

CREATE FUNCTION fn_calcula_total_item(p_quantidade INT, p_preco DECIMAL(10,2)) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    RETURN p_quantidade * p_preco;
END //

DELIMITER ;

-- 5. DADOS DE TESTE 
INSERT INTO Cliente (id_cliente, nome, email, senha, telefone) VALUES (1, 'João Silva', 'joao@email.com', '123', '99999999');
INSERT INTO Produtos (id_produto, nome, preco, descricao, imagem) VALUES (1, 'Quadro caro', 3500.00, 'Quadro top', 'img.png');
INSERT INTO pedido (id_pedido, id_cliente, data_pedido, status, total) VALUES (1, 1, '2026-08-20 10:00:00', 'Concluído', 0);
INSERT INTO Itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (1, 1, 2, 3500.00);
-- Garante que a coluna nivel existe na tabela de clientes
ALTER TABLE Cliente ADD COLUMN IF NOT EXISTS nivel VARCHAR(20) DEFAULT 'cliente';

-- Cria o usuário Admin padrão para acesso ao Dashboard
INSERT INTO Cliente (nome, email, senha, telefone, nivel) 
VALUES ('Administrador', 'admin@luz.com', '123', '00000000', 'admin');