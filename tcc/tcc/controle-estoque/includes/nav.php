<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
$menu = [
    'index.php' => ['texto' => 'Estoque', 'href' => 'index.php'],
    'dashboard.php' => ['texto' => 'Dashboard', 'href' => 'dashboard.php'],
    'saida.php' => ['texto' => 'Saídas', 'href' => 'saida.php'],
    'movimentacoes.php' => ['texto' => 'Movimentações', 'href' => 'movimentacoes.php'],
    'relatorios.php' => ['texto' => 'Relatórios', 'href' => 'relatorios.php'],
    'avisos.php' => ['texto' => 'Avisos', 'href' => 'avisos.php'],
];
?>
<nav class="navbar">
    <?php foreach ($menu as $arquivo => $item): ?>
        <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" class="<?= $paginaAtual === $arquivo ? 'active' : ''; ?>"><?= htmlspecialchars($item['texto'], ENT_QUOTES, 'UTF-8'); ?></a>
    <?php endforeach; ?>
</nav>
