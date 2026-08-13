<?php
include("includes/conexao.php");
include("includes/functions.php");

$resumo = dashboard($conexao);
$vencendo = produtosVencendo($conexao, 7);
$criticos = produtosCriticos($conexao, 5);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard - Controle de Estoque</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include("includes/nav.php"); ?>

<h1>Dashboard</h1>

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
    <div class="card alerta">
        <div class="rotulo">Estoque Baixo (≤5)</div>
        <div class="valor"><?= $resumo['estoque_baixo']; ?></div>
    </div>
    <div class="card">
        <div class="rotulo">Movimentações Registradas</div>
        <div class="valor"><?= $resumo['total_movimentacoes']; ?></div>
    </div>
</div>

<div class="chart-container">
    <h3>Situação Geral do Estoque</h3>
    <canvas id="graficoEstoque" height="90"></canvas>
</div>

<hr>

<h2>Produtos Vencendo em Breve</h2>
<table border="1">
<tr><th>Código</th><th>Nome</th><th>Lote</th><th>Validade</th><th>Qtd</th></tr>
<?php if (mysqli_num_rows($vencendo) > 0): ?>
    <?php while ($p = mysqli_fetch_assoc($vencendo)): ?>
    <tr>
        <td><?= htmlspecialchars($p['codigo']); ?></td>
        <td><?= htmlspecialchars($p['nome']); ?></td>
        <td><?= htmlspecialchars($p['lote']); ?></td>
        <td><?= date('d/m/Y', strtotime($p['validade'])); ?></td>
        <td><?= $p['quantidade']; ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="5" style="text-align:center;">Nenhum produto vencendo nos próximos 7 dias.</td></tr>
<?php endif; ?>
</table>

<hr>

<h2>Produtos com Estoque Baixo</h2>
<table border="1">
<tr><th>Código</th><th>Nome</th><th>Lote</th><th>Qtd</th><th>Categoria</th></tr>
<?php if (mysqli_num_rows($criticos) > 0): ?>
    <?php while ($p = mysqli_fetch_assoc($criticos)): ?>
    <tr>
        <td><?= htmlspecialchars($p['codigo']); ?></td>
        <td><?= htmlspecialchars($p['nome']); ?></td>
        <td><?= htmlspecialchars($p['lote']); ?></td>
        <td><?= $p['quantidade']; ?></td>
        <td><?= htmlspecialchars($p['categoria']); ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="5" style="text-align:center;">Nenhum produto com estoque baixo.</td></tr>
<?php endif; ?>
</table>

<script src="assets/js/chart.js"></script>
<script>
criarGraficoBarras('graficoEstoque', {
    labels: ['Total', 'Vencendo', 'Vencidos', 'Estoque Baixo'],
    valores: [
        <?= (int)$resumo['total_produtos']; ?>,
        <?= (int)$resumo['produtos_vencendo']; ?>,
        <?= (int)$resumo['produtos_vencidos']; ?>,
        <?= (int)$resumo['estoque_baixo']; ?>
    ],
    cores: ['#0d6efd', '#ffc107', '#dc3545', '#fd7e14']
});
</script>
</body>
</html>