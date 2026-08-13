<?php

function buscarProdutos($conexao, $termo = null) {
    if ($termo !== null && trim($termo) !== "") {
        $filtro = '%' . $termo . '%';
        $sql = "SELECT * FROM produtos WHERE nome LIKE ? OR codigo LIKE ? OR categoria LIKE ? ORDER BY validade ASC";
        $stmt = mysqli_prepare($conexao, $sql);
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, "sss", $filtro, $filtro, $filtro);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    $sql = "SELECT * FROM produtos ORDER BY validade ASC";
    return mysqli_query($conexao, $sql);
}

function buscarProduto($conexao, $id) {
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultado);
}

function cadastrarProduto($conexao, array $dados) {
    $codigo = $dados['codigo'] ?? '';
    $nome = $dados['nome'] ?? '';
    $lote = $dados['lote'] ?? '';
    $quantidade = isset($dados['quantidade']) ? (int)$dados['quantidade'] : 0;
    $preco = isset($dados['preco']) ? (float)$dados['preco'] : 0.0;
    $validade = $dados['validade'] ?? null;
    $data_compra = $dados['data_compra'] ?? null;
    $categoria = $dados['categoria'] ?? '';

    $sql = "INSERT INTO produtos (codigo, nome, lote, quantidade, preco, validade, data_compra, categoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sssidsss", $codigo, $nome, $lote, $quantidade, $preco, $validade, $data_compra, $categoria);
    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    return mysqli_insert_id($conexao);
}

function editarProduto($conexao, $id, array $dados) {
    $codigo = $dados['codigo'] ?? '';
    $nome = $dados['nome'] ?? '';
    $lote = $dados['lote'] ?? '';
    $quantidade = isset($dados['quantidade']) ? (int)$dados['quantidade'] : 0;
    $preco = isset($dados['preco']) ? (float)$dados['preco'] : 0.0;
    $validade = $dados['validade'] ?? null;
    $data_compra = $dados['data_compra'] ?? null;
    $categoria = $dados['categoria'] ?? '';

    $sql = "UPDATE produtos SET codigo = ?, nome = ?, lote = ?, quantidade = ?, preco = ?, validade = ?, data_compra = ?, categoria = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sssidsssi", $codigo, $nome, $lote, $quantidade, $preco, $validade, $data_compra, $categoria, $id);
    return mysqli_stmt_execute($stmt);
}

function excluirProduto($conexao, $id) {
    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_affected_rows($stmt) > 0;
}

function criarTabelaMovimentacoes($conexao) {
    $sql = "CREATE TABLE IF NOT EXISTS movimentacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        tipo ENUM('entrada','saida','ajuste') NOT NULL,
        quantidade INT NOT NULL,
        observacao TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return mysqli_query($conexao, $sql);
}

function registrarMovimentacao($conexao, $produtoId, $tipo, $quantidade, $observacao = null) {
    if (!in_array($tipo, ['entrada', 'saida', 'ajuste'], true)) {
        $tipo = 'ajuste';
    }

    if (!criarTabelaMovimentacoes($conexao)) {
        return false;
    }

    $sql = "INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "isis", $produtoId, $tipo, $quantidade, $observacao);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_affected_rows($stmt) > 0;
}

function registrarEntrada($conexao, $produtoId, $quantidade, $observacao = null) {
    $quantidade = max(0, (int)$quantidade);

    $sql = "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $quantidade, $produtoId);
    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    return registrarMovimentacao($conexao, $produtoId, 'entrada', $quantidade, $observacao);
}

function registrarSaida($conexao, $produtoId, $quantidade, $observacao = null) {
    $quantidade = max(0, (int)$quantidade);

    $produto = buscarProduto($conexao, $produtoId);
    if (!$produto) {
        return false;
    }

    $novaQuantidade = max(0, $produto['quantidade'] - $quantidade);

    $sql = "UPDATE produtos SET quantidade = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $novaQuantidade, $produtoId);
    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    return registrarMovimentacao($conexao, $produtoId, 'saida', $quantidade, $observacao);
}

function buscarAlertas($conexao, $dias = 7) {
    $sql = "SELECT * FROM produtos WHERE validade IS NOT NULL AND validade <= DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY validade ASC";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $dias);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function buscarAvisos($conexao, $apenasNaoLidos = false) {
    $sql = "SELECT a.*, p.nome, p.lote, p.validade FROM avisos a LEFT JOIN produtos p ON a.produto_id = p.id";
    if ($apenasNaoLidos) {
        $sql .= " WHERE a.lido = 0";
    }
    $sql .= " ORDER BY a.created_at DESC";

    return mysqli_query($conexao, $sql);
}

function dashboard($conexao) {
    criarTabelaMovimentacoes($conexao);

    return [
        'total_produtos' => (int)obterValorUnico($conexao, "SELECT COUNT(*) FROM produtos"),
        'produtos_vencidos' => (int)obterValorUnico($conexao, "SELECT COUNT(*) FROM produtos WHERE validade < CURDATE()"),
        'produtos_vencendo' => (int)obterValorUnico($conexao, "SELECT COUNT(*) FROM produtos WHERE validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"),
        'estoque_baixo' => (int)obterValorUnico($conexao, "SELECT COUNT(*) FROM produtos WHERE quantidade <= 5"),
        'valor_estoque' => (float)obterValorUnico($conexao, "SELECT COALESCE(SUM(preco), 0) FROM produtos"),
        'total_avisos' => (int)obterValorUnico($conexao, "SELECT COUNT(*) FROM avisos"),
        'total_movimentacoes' => (int)obterValorUnico($conexao, "SELECT COUNT(*) FROM movimentacoes"),
    ];
}

function estoqueBaixo($conexao, $limite = 5) {
    $sql = "SELECT * FROM produtos WHERE quantidade <= ? ORDER BY quantidade ASC";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $limite);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function valorEstoque($conexao) {
    return (float)obterValorUnico($conexao, "SELECT COALESCE(SUM(preco), 0) FROM produtos");
}

function produtosVencidos($conexao) {
    $sql = "SELECT * FROM produtos WHERE validade < CURDATE() ORDER BY validade ASC";
    return mysqli_query($conexao, $sql);
}

function produtosVencendo($conexao, $dias = 7) {
    $sql = "SELECT * FROM produtos WHERE validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY validade ASC";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $dias);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function buscarCategorias($conexao) {
    $sql = "SELECT DISTINCT categoria FROM produtos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
    return mysqli_query($conexao, $sql);
}

function pesquisarProdutos($conexao, $termo, $ordenarPor = null, $ordem = 'ASC') {
    $filtro = '%' . $termo . '%';
    $allowed = ['validade', 'nome', 'categoria', 'quantidade', 'preco', 'codigo'];
    $ordem = strtoupper($ordem) === 'DESC' ? 'DESC' : 'ASC';
    $coluna = in_array($ordenarPor, $allowed, true) ? $ordenarPor : 'validade';

    $sql = "SELECT * FROM produtos WHERE nome LIKE ? OR codigo LIKE ? OR categoria LIKE ? ORDER BY {$coluna} {$ordem}";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sss", $filtro, $filtro, $filtro);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function ordenarProdutos($conexao, $campo = 'validade', $ordem = 'ASC') {
    $allowed = ['validade', 'nome', 'categoria', 'quantidade', 'preco', 'codigo'];
    $ordem = strtoupper($ordem) === 'DESC' ? 'DESC' : 'ASC';
    $campo = in_array($campo, $allowed, true) ? $campo : 'validade';

    $sql = "SELECT * FROM produtos ORDER BY {$campo} {$ordem}";
    return mysqli_query($conexao, $sql);
}

function obterValorUnico($conexao, $sql) {
    $resultado = mysqli_query($conexao, $sql);
    if (!$resultado) {
        return 0;
    }

    $linha = mysqli_fetch_array($resultado);
    return $linha[0] ?? 0;
}

function verificarAlertasValidade($conexao, $dias = 7) {
    // Garante que a tabela de avisos exista
    $sqlCreate = "CREATE TABLE IF NOT EXISTS avisos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NULL,
        mensagem TEXT,
        tipo VARCHAR(50),
        lido TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($conexao, $sqlCreate);

    $alertas = [];

    // Busca produtos com validade dentro do intervalo
    $sql = "SELECT * FROM produtos WHERE validade IS NOT NULL AND validade <= DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY validade ASC";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return $alertas;
    }
    mysqli_stmt_bind_param($stmt, "i", $dias);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $hoje_ts = strtotime(date('Y-m-d'));
    while ($prod = mysqli_fetch_assoc($res)) {
        $val = $prod['validade'];
        if (empty($val)) continue;

        $val_ts = strtotime($val);
        $diff_days = (int) floor(($val_ts - $hoje_ts) / (60 * 60 * 24));

        if ($diff_days < 0) {
            $tipo = 'vencido';
            $mensagem = "Produto {$prod['nome']} (Lote: {$prod['lote']}) está vencido em " . date('d/m/Y', $val_ts);
        } else {
            $tipo = 'vencendo';
            $mensagem = "Produto {$prod['nome']} (Lote: {$prod['lote']}) vence em {$diff_days} dia(s) em " . date('d/m/Y', $val_ts);
        }

        // Evita inserir avisos duplicados no mesmo dia para o mesmo produto e tipo
        $checkSql = "SELECT COUNT(*) FROM avisos WHERE produto_id = ? AND tipo = ? AND DATE(created_at) = CURDATE()";
        $checkStmt = mysqli_prepare($conexao, $checkSql);
        if ($checkStmt) {
            mysqli_stmt_bind_param($checkStmt, "is", $prod['id'], $tipo);
            mysqli_stmt_execute($checkStmt);
            $checkRes = mysqli_stmt_get_result($checkStmt);
            $countRow = mysqli_fetch_array($checkRes);
            $existsToday = ($countRow[0] ?? 0) > 0;
        } else {
            $existsToday = false;
        }

        if (!$existsToday) {
            $insSql = "INSERT INTO avisos (produto_id, mensagem, tipo) VALUES (?, ?, ?)";
            $insStmt = mysqli_prepare($conexao, $insSql);
            if ($insStmt) {
                mysqli_stmt_bind_param($insStmt, "iss", $prod['id'], $mensagem, $tipo);
                mysqli_stmt_execute($insStmt);
            }
        }

        $alertas[] = [
            'tipo' => $tipo,
            'msg' => $mensagem,
        ];
    }

    return $alertas;
}
