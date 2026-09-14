"use strict";
const elementoFaturamento = document.getElementById('faturamento-total');
const elementoProdutoDestaque = document.getElementById('produto-destaque');
const tabelaVendas = document.getElementById('tabela-vendas');
const inputFiltro = document.getElementById('input-filtro');
let vendasCarregadas = [];
function obterValor(venda) {
    const valor = Number(venda.valor_total_item);
    return Number.isFinite(valor) ? valor : 0;
}
function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
function obterData(data) {
    const dataFormatada = new Date(data);
    return Number.isNaN(dataFormatada.getTime())
        ? 'Data inválida'
        : dataFormatada.toLocaleDateString('pt-BR');
}
function mostrarMensagemTabela(mensagem) {
    if (!tabelaVendas) {
        return;
    }
    tabelaVendas.replaceChildren();
    const linha = document.createElement('tr');
    const celula = document.createElement('td');
    celula.colSpan = 4;
    celula.className = 'text-center text-muted';
    celula.textContent = mensagem;
    linha.appendChild(celula);
    tabelaVendas.appendChild(linha);
}
function renderizarVendas(vendas) {
    if (!tabelaVendas) {
        return;
    }
    if (vendas.length === 0) {
        mostrarMensagemTabela('Nenhum dado registrado');
        return;
    }
    tabelaVendas.replaceChildren();
    vendas.map((venda) => {
        const linha = document.createElement('tr');
        const cliente = document.createElement('td');
        const data = document.createElement('td');
        const produto = document.createElement('td');
        const valor = document.createElement('td');
        cliente.textContent = venda.nome_cliente;
        data.textContent = obterData(venda.data_pedido);
        produto.textContent = venda.nome_produto;
        valor.textContent = formatarMoeda(obterValor(venda));
        valor.className = 'fw-bold text-success';
        linha.append(cliente, data, produto, valor);
        tabelaVendas.appendChild(linha);
    });
}
function atualizarIndicadores(vendas) {
    const faturamentoTotal = vendas.reduce((acumulador, venda) => acumulador + obterValor(venda), 0);
    const frequenciaProdutos = vendas.reduce((contagem, venda) => {
        contagem[venda.nome_produto] = (contagem[venda.nome_produto] ?? 0) + 1;
        return contagem;
    }, {});
    const produtoMaisVendido = Object.entries(frequenciaProdutos)
        .sort(([, quantidadeA], [, quantidadeB]) => quantidadeB - quantidadeA)[0]?.[0] ?? 'Nenhum registro';
    if (elementoFaturamento) {
        elementoFaturamento.textContent = formatarMoeda(faturamentoTotal);
    }
    if (elementoProdutoDestaque) {
        elementoProdutoDestaque.textContent = produtoMaisVendido;
    }
}
function filtrarVendas() {
    const termo = inputFiltro instanceof HTMLInputElement ? inputFiltro.value.trim().toLowerCase() : '';
    const vendasFiltradas = vendasCarregadas.filter((venda) => venda.nome_cliente.toLowerCase().includes(termo));
    renderizarVendas(vendasFiltradas);
}
async function carregarVendas() {
    try {
        const resposta = await fetch(`api_dashboard.php?_=${Date.now()}`, { cache: 'no-store' });
        if (!resposta.ok) {
            throw new Error('Falha na comunicação com a API');
        }
        const dados = await resposta.json();
        if (!Array.isArray(dados)) {
            throw new Error('A API retornou dados inválidos');
        }
        vendasCarregadas = dados;
        atualizarIndicadores(vendasCarregadas);
        filtrarVendas();
    }
    catch (erro) {
        console.error('Erro ao buscar dados:', erro);
        if (elementoFaturamento) {
            elementoFaturamento.textContent = 'Indisponível';
        }
        if (elementoProdutoDestaque) {
            elementoProdutoDestaque.textContent = 'Indisponível';
        }
        mostrarMensagemTabela('Não foi possível carregar as vendas.');
    }
}
inputFiltro?.addEventListener('input', filtrarVendas);
document.getElementById('atualizar-dashboard')?.addEventListener('click', carregarVendas);
carregarVendas();
