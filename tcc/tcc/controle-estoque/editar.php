<?php
include("includes/conexao.php");
include("includes/functions.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

$produto = buscarProduto($conexao, $id);
if (!$produto) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['editar'])) {
    editarProduto($conexao, $id, $_POST);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Editar Insumo/Produto</h2>
    <form method="POST">
        <label>Código:</label>
        <input type="text" name="codigo" value="<?= htmlspecialchars($produto['codigo']); ?>" required>
        
        <label>Nome:</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']); ?>" required>
        
        <label>Lote:</label>
        <input type="text" name="lote" value="<?= htmlspecialchars($produto['lote']); ?>" required>
        
        <label>Quantidade:</label>
        <input type="number" name="quantidade" value="<?= $produto['quantidade']; ?>" required>
        
        <label>Preço:</label>
        <input type="number" step="0.01" name="preco" value="<?= $produto['preco']; ?>" required>
        
        <label>Validade:</label>
        <input type="date" name="validade" value="<?= $produto['validade']; ?>">
        
        <label>Data da Compra:</label>
        <input type="date" name="data_compra" value="<?= $produto['data_compra']; ?>">
        
        <label>Categoria:</label>
        <input type="text" name="categoria" value="<?= htmlspecialchars($produto['categoria']); ?>">
        
        <button type="submit" name="editar">Salvar Alterações</button>
    </form>
</body>
</html>