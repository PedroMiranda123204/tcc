<?php
include("includes/conexao.php");
include("includes/functions.php");
include("includes/nav.php"); 
$res_avisos = buscarAvisos($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Histórico de Avisos - Controle de Estoque</title>
<link rel="stylesheet" href="style.css">
</head>

<body>
<?php include("includes/nav.php"); ?>
<h1>Histórico de Avisos e Alertas</h1>
<div class="avisos-container">
<?php if(mysqli_num_rows($res_avisos) > 0){ ?>
<table border="1">
<tr>
<th>Data da Alerta</th>
<th>Mensagem</th>
<th>Lote</th>
<th>Data de Validade</th>
</tr>
<?php while($aviso = mysqli_fetch_assoc($res_avisos)){
$data_alerta = date('d/m/Y H:i',

strtotime($aviso['created_at']));

$val_prod = $aviso['validade'] ? date('d/m/Y',

strtotime($aviso['validade'])) : 'N/A';

?>
<tr class="<?= $aviso['tipo']; ?>">
<td><?= $data_alerta; ?></td>
<td><?= htmlspecialchars($aviso['mensagem']);

?></td>

<td><?= htmlspecialchars($aviso['lote'] ?? 'N/A');

?></td>

<td><?= $val_prod; ?></td>
</tr>
<?php } ?>
</table>
<?php } else { ?>
<p style="text-align: center;">Nenhum aviso registrado no

momento.</p>
<?php } ?>
</div>
</body>
</html>