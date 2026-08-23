interface Venda {
    nome_cliente: string;
    data_pedido: string;
    nome_produto: string;
    valor_total_item: string | number; 
}

async function carregarVendas(): Promise<void> {
    try {
        const resposta = await fetch('api_dashboard.php');
        
        if (!resposta.ok) throw new Error('Falha na comunicação com a API');

        const vendas: Venda[] = await resposta.json();

        // 1. O REDUCE
        const faturamentoTotal = vendas.reduce((acumulador, venda) => {
            return acumulador + Number(venda.valor_total_item);
        }, 0);

        const elementoFaturamento = document.getElementById('faturamento-total');
        if (elementoFaturamento) {
            elementoFaturamento.innerText = `R$ ${faturamentoTotal.toFixed(2).replace('.', ',')}`;
        }

        const tbody = document.getElementById('tabela-vendas');
        
        if (tbody) {
            if (vendas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum dado registrado</td></tr>';
            } else {
                const linhasHTML = vendas.map(venda => {
                    const valorFormatado = `R$ ${Number(venda.valor_total_item).toFixed(2).replace('.', ',')}`;
                    const dataFormatada = new Date(venda.data_pedido).toLocaleDateString('pt-BR');

                    return `
                        <tr>
                            <td>${venda.nome_cliente}</td>
                            <td>${dataFormatada}</td>
                            <td>${venda.nome_produto}</td>
                            <td class="fw-bold text-success">${valorFormatado}</td>
                        </tr>
                    `;
                }).join(''); 

                tbody.innerHTML = linhasHTML;
            }
        }
        
    
    } catch (erro) {
        console.error("Erro ao buscar dados:", erro);
    }
}

carregarVendas();