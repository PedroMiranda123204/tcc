<?php
include("includes/conexao.php");
include("includes/functions.php");

// 1. Verifica produtos com validade próxima/vencida e registra os avisos
$alertas_para_exibir = verificarAlertasValidade($conexao, 7);

// 2. Busca geral de produtos para a tabela (ordenados por validade - FEFO)
$hoje = date('Y-m-d');
$dados = buscarProdutos($conexao, $_GET['pesquisa'] ?? null);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Controle de Estoque - Carrinho</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include("includes/nav.php"); ?>

<h1>Gestão de Estoque e Insumos</h1>

<!-- Bloco de Alertas em Destaque na Tela Inicial -->
<?php if (!empty($alertas_para_exibir)) { ?>
<div class="banner-alertas">
    <h3>⚠️ Alertas de Validade Próxima (≤ 7 dias)</h3>
    <ul>
        <?php foreach ($alertas_para_exibir as $alerta) { ?>
            <li class="<?= $alerta['tipo']; ?>"><?= $alerta['msg']; ?></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<!-- Formulário de Pesquisa -->
<form method="GET" class="search-form">
    <input type="text" name="pesquisa" placeholder="Pesquisar por nome, código ou categoria" value="<?= isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : '' ?>">
    <button type="submit">Buscar</button>
</form>

<hr>

<!-- Formulário de Cadastro -->
<form action="cadastrar.php" method="POST" class="add-form">
    <h3>Entrada de Novo Lote/Insumo</h3>
    <input type="text" name="codigo" placeholder="Código (Ex: PAO-01)" required>
    <input type="text" name="nome" placeholder="Nome do Produto" required>
    <input type="text" name="lote" placeholder="Lote (Ex: LT-445)" required>
    <input type="number" name="quantidade" placeholder="Quantidade" required>
    <input type="number" step="0.01" name="preco" placeholder="Preço Total (R$)" required>
    <label>Data de Validade:</label>
    <input type="date" name="validade" required>
    <label>Data de Compra:</label>
    <input type="date" name="data_compra" required>
    <input type="text" name="categoria" placeholder="Categoria (Ex: Pães, Molhos)">
    <button type="submit" name="cadastrar">Registrar Entrada</button>
</form>

<hr>

<!-- Tabela Principal com Validação Dinâmica de Cor -->
<table border="1">
<tr>
    <th>Código</th>
    <th>Nome</th>
    <th>Lote</th>
    <th>Qtd</th>
    <th>Preço</th>
    <th>Validade</th>
    <th>Categoria</th>
    <th>Ações</th>
</tr>
<?php while ($produto = mysqli_fetch_assoc($dados)) {
    $data_validade = date('d/m/Y', strtotime($produto['validade']));

    // Lógica para pintar de vermelho se faltar <= 7 dias para vencer
    $dias_ate_vencer = (int)floor((strtotime($produto['validade']) - strtotime($hoje)) / (60 * 60 * 24));
    $estilo_data = $dias_ate_vencer <= 7
        ? "color: #dc3545; font-weight: bold; background-color: #f8d7da;"
        : "color: #333;";
?>
<tr>
    <td><?= htmlspecialchars($produto['codigo']); ?></td>
    <td><?= htmlspecialchars($produto['nome']); ?></td>
    <td><?= htmlspecialchars($produto['lote']); ?></td>
    <td><?= $produto['quantidade']; ?></td>
    <td>R$ <?= number_format($produto['preco'], 2, ",", "."); ?></td>
    <td style="<?= $estilo_data; ?>"><?= $data_validade; ?></td>
    <td><?= htmlspecialchars($produto['categoria']); ?></td>
    <td>
        <a href="editar.php?id=<?= $produto['id']; ?>">Editar</a> |
        <a href="excluir.php?id=<?= $produto['id']; ?>" onclick="return confirm('Deseja excluir?')">Excluir</a>
    </td>
</tr>
<?php } ?>
</table>
</body>
</html>