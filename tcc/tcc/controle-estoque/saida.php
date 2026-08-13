<?php
include("includes/conexao.php");
include("includes/functions.php");

$mensagem = null;
$erro = null;

if (isset($_POST['registrar_saida'])) {
    $produtoId = (int)($_POST['produto_id'] ?? 0);
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $observacao = trim($_POST['observacao'] ?? '');

    $produto = $produtoId ? buscarProduto($conexao, $produtoId) : null;

    if (!$produto) {
        $erro = "Selecione um produto válido.";
    } elseif ($quantidade <= 0) {
        $erro = "Informe uma quantidade maior que zero.";
    } elseif ($quantidade > $produto['quantidade']) {
        $erro = "Quantidade indisponível em estoque (disponível: {$produto['quantidade']}).";
    } else {
        registrarSaida($conexao, $produtoId, $quantidade, $observacao !== '' ? $observacao : null);
        header("Location: saida.php?ok=1");
        exit;
    }
}

$produtos = listarProdutos($conexao);
$ultimasSaidas = listarMovimentacoes($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Saída de Estoque - Controle de Estoque</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include("includes/nav.php"); ?>

<h1>Registrar Saída de Estoque</h1>

<?php if (isset($_GET['ok'])): ?>
    <div class="banner-alertas"><ul><li class="warning">Saída registrada com sucesso!</li></ul></div>
<?php endif; ?>
<?php if ($erro): ?>
    <div class="banner-alertas"><ul><li class="danger"><?= htmlspecialchars($erro); ?></li></ul></div>
<?php endif; ?>

<form method="POST" class="add-form">
    <h3>Nova Saída</h3>
    <label>Produto:</label>
    <select name="produto_id" required>
        <option value="">Selecione um produto</option>
        <?php mysqli_data_seek($produtos, 0); while ($p = mysqli_fetch_assoc($produtos)): ?>
            <option value="<?= $p['id']; ?>">
                <?= htmlspecialchars($p['codigo'] . ' - ' . $p['nome'] . ' (Lote ' . $p['lote'] . ', disp: ' . $p['quantidade'] . ')'); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Quantidade a retirar:</label>
    <input type="number" name="quantidade" min="1" required>

    <label>Observação (opcional):</label>
    <input type="text" name="observacao" placeholder="Ex: Uso na produção do dia">

    <button type="submit" name="registrar_saida">Registrar Saída</button>
</form>

<hr>

<h2>Últimas Movimentações de Saída</h2>
<table border="1">
<tr><th>Data</th><th>Produto</th><th>Quantidade</th><th>Observação</th></tr>
<?php
$temSaida = false;
while ($m = mysqli_fetch_assoc($ultimasSaidas)):
    if ($m['tipo'] !== 'saida') continue;
    $temSaida = true;
?>
    <tr>
        <td><?= date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
        <td><?= htmlspecialchars(($m['codigo'] ?? '') . ' - ' . ($m['nome'] ?? 'Produto removido')); ?></td>
        <td><?= $m['quantidade']; ?></td>
        <td><?= htmlspecialchars($m['observacao'] ?? ''); ?></td>
    </tr>
<?php endwhile; ?>
<?php if (!$temSaida): ?>
    <tr><td colspan="4" style="text-align:center;">Nenhuma saída registrada ainda.</td></tr>
<?php endif; ?>
</table>
</body>
</html>