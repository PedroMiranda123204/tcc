<?php
include("includes/conexao.php");
include("includes/functions.php");

$resumo = dashboard($conexao);
$produtos = dadosExcel($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Relatórios - Controle de Estoque</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include("includes/nav.php"); ?>

<h1>Relatórios</h1>

<div class="acoes-relatorio">
    <a href="exportar_pdf.php" target="_blank">📄 Exportar Relatório em PDF</a>
    <a href="exportar_excel.php">📊 Exportar Planilha (Excel/CSV)</a>
</div>

<div class="cards">
    <div class="card">
        <div class="rotulo">Total de Produtos</div>
        <div class="valor"><?= $resumo['total_produtos']; ?></div>
    </div>
    <div class="card sucesso">
        <div class="rotulo">Valor em Estoque</div>
        <div class="valor">R$ <?= number_format($resumo['valor_estoque'], 2, ",", "."); ?></div>
    </div>
    <div class="card alerta">
        <div class="rotulo">Vencendo em 7 dias</div>
        <div class="valor"><?= $resumo['produtos_vencendo']; ?></div>
    </div>
    <div class="card perigo">
        <div class="rotulo">Produtos Vencidos</div>
        <div class="valor"><?= $resumo['produtos_vencidos']; ?></div>
    </div>
</div>

<h2>Todos os Produtos</h2>
<table border="1">
<tr>
    <th>Código</th><th>Nome</th><th>Lote</th><th>Qtd</th><th>Preço</th><th>Validade</th><th>Categoria</th>
</tr>
<?php if (count($produtos) > 0): ?>
    <?php foreach ($produtos as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['codigo']); ?></td>
        <td><?= htmlspecialchars($p['nome']); ?></td>
        <td><?= htmlspecialchars($p['lote']); ?></td>
        <td><?= $p['quantidade']; ?></td>
        <td>R$ <?= number_format($p['preco'], 2, ",", "."); ?></td>
        <td><?= $p['validade'] ? date('d/m/Y', strtotime($p['validade'])) : '-'; ?></td>
        <td><?= htmlspecialchars($p['categoria']); ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="7" style="text-align:center;">Nenhum produto cadastrado.</td></tr>
<?php endif; ?>
</table>
</body>
</html>