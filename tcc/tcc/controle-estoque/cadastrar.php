<?php
include("includes/conexao.php");
include("includes/functions.php");

if (isset($_POST['cadastrar'])) {
    cadastrarProduto($conexao, $_POST);
    header("Location: index.php");
    exit;
}
?>
