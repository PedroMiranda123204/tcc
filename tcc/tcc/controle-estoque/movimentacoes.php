<?php
include("includes/conexao.php");
include("includes/functions.php");

$filtroTipo = $_GET['tipo'] ?? '';
$movimentacoes = listarMovimentacoes($conexao);

$tiposValidos = ['entrada' => 'Entrada', 'saida' => 'Saída', 'ajuste' => 'Ajuste'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Movimentações - Controle de Estoque</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include("includes/nav.php"); ?>

<h1>Histórico de Movimentações</h1>

<div class="filtros">
    <form method="GET">
        <label>
            Filtrar por tipo:
            <select name="tipo" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach ($tiposValidos as $valor => $rotulo): ?>
                    <option value="<?= $valor; ?>" <?= $filtroTipo === $valor ? 'selected' : ''; ?>><?= $rotulo; ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>
</div>

<table border="1">
<tr>
    <th>Data</th>
    <th>Produto</th>
    <th>Tipo</th>
    <th>Quantidade</th>
    <th>Observação</th>
</tr>
<?php
$temLinha = false;
while ($m = mysqli_fetch_assoc($movimentacoes)):
    if ($filtroTipo !== '' && $m['tipo'] !== $filtroTipo) continue;
    $temLinha = true;
?>
<tr>
    <td><?= date('d/m/Y H:i', strtotime($m['created_at'])); ?></td>
    <td><?= htmlspecialchars(($m['codigo'] ?? '') . ' - ' . ($m['nome'] ?? 'Produto removido')); ?></td>
    <td><span class="badge <?= $m['tipo']; ?>"><?= $tiposValidos[$m['tipo']] ?? ucfirst($m['tipo']); ?></span></td>
    <td><?= $m['quantidade']; ?></td>
    <td><?= htmlspecialchars($m['observacao'] ?? ''); ?></td>
</tr>
<?php endwhile; ?>
<?php if (!$temLinha): ?>
<tr><td colspan="5" style="text-align:center;">Nenhuma movimentação encontrada.</td></tr>
<?php endif; ?>
</table>
</body>
</html>