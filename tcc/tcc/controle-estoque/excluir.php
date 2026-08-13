<?php
include("includes/conexao.php");
include("includes/functions.php");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    excluirProduto($conexao, $id);
}

header("Location: index.php");
exit;
?>
