<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
ensureDemoSession();
$pdo = getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'update') {
        $fields = [
            'nome'             => trim($_POST['nome'] ?? ''),
            'nome_real'        => trim($_POST['nome_real'] ?? ''),
            'posicao_id'       => (int)($_POST['posicao_id'] ?? 0) ?: null,
            'data_nascimento'  => $_POST['data_nascimento'] ?: null,
            'data_falecimento' => $_POST['data_falecimento'] ?: null,
            'gols_sofridos'    => $_POST['gols_sofridos'] !== '' ? (int)$_POST['gols_sofridos'] : null,
            'descricao'        => trim($_POST['descricao'] ?? ''),
            'titulos'          => trim($_POST['titulos'] ?? ''),
            'foto'             => trim($_POST['foto'] ?? ''),
            'time'             => isset($_POST['time']) ? 1 : 0,
        ];
        if (empty($fields['nome'])) {
            $_SESSION['flash_error'] = 'O nome do jogador é obrigatório.';
        } else {
            if ($action === 'create') {
                $sql = "INSERT INTO jogadores (nome,nome_real,posicao_id,data_nascimento,data_falecimento,gols_sofridos,descricao,titulos,foto,time) VALUES (:nome,:nome_real,:posicao_id,:data_nascimento,:data_falecimento,:gols_sofridos,:descricao,:titulos,:foto,:time)";
                $pdo->prepare($sql)->execute($fields);
                $_SESSION['flash_success'] = 'Jogador cadastrado com sucesso!';
            } else {
                $fields['id'] = $id;
                $sql = "UPDATE jogadores SET nome=:nome,nome_real=:nome_real,posicao_id=:posicao_id,data_nascimento=:data_nascimento,data_falecimento=:data_falecimento,gols_sofridos=:gols_sofridos,descricao=:descricao,titulos=:titulos,foto=:foto,time=:time WHERE id=:id";
                $pdo->prepare($sql)->execute($fields);
                $_SESSION['flash_success'] = 'Jogador atualizado com sucesso!';
            }
        }
        header('Location: index.php' . ($fields['posicao_id'] ? '?posicao_id='.$fields['posicao_id'] : ''));
        exit;
    }
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM jogadores WHERE id = ?")->execute([$id]);
        $_SESSION['flash_success'] = 'Jogador removido.';
        header('Location: index.php');
        exit;
    }
}

$posicoes = $pdo->query("SELECT * FROM posicoes ORDER BY nome")->fetchAll();

$filterPosicao = isset($_GET['posicao_id']) && $_GET['posicao_id'] !== '' ? (int)$_GET['posicao_id'] : null;
$filterNome    = trim($_GET['nome'] ?? '');
$where = []; $params = [];
if ($filterPosicao) { $where[] = 'j.posicao_id = :posicao_id'; $params[':posicao_id'] = $filterPosicao; }
if ($filterNome !== '') { $where[] = '(j.nome LIKE :nome OR j.nome_real LIKE :nome)'; $params[':nome'] = '%'.$filterNome.'%'; }
$whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';

$stmt = $pdo->prepare("SELECT j.*, p.nome AS posicao FROM jogadores j LEFT JOIN posicoes p ON p.id = j.posicao_id $whereSql ORDER BY j.nome");
$stmt->execute($params);
$jogadores = $stmt->fetchAll();

$editPlayer = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM jogadores WHERE id = ?");
    $s->execute([$id]);
    $editPlayer = $s->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div>
        <div class="page-title">Jogadores</div>
        <div class="page-subtitle"><?= count($jogadores) ?> jogador(es) encontrado(s)</div>
    </div>
    <button class="btn btn-primary" onclick="toggleForm()">+ Novo Jogador</button>
</div>

<div class="filter-bar">
    <form method="GET" action="index.php" style="display:contents">
        <div class="form-group">
            <label>Filtrar por Posicao</label>
            <select name="posicao_id" onchange="this.form.submit()">
                <option value="">Todas as posicoes</option>
                <?php foreach ($posicoes as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filterPosicao == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Buscar por nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($filterNome) ?>" placeholder="Nome ou nome real...">
        </div>
        <button type="submit" class="btn btn-ghost">Filtrar</button>
        <?php if ($filterPosicao || $filterNome): ?><a href="index.php" class="btn btn-ghost">x Limpar</a><?php endif; ?>
    </form>
</div>

<div id="jogador-form" style="display:<?= $editPlayer ? 'block' : 'none' ?>;margin-bottom:1.5rem;">
    <div class="card">
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--accent);margin-bottom:1.2rem;letter-spacing:1px;">
            <?= $editPlayer ? 'Editar Jogador' : 'Novo Jogador' ?>
        </h3>
        <form method="POST" action="index.php">
            <input type="hidden" name="action" value="<?= $editPlayer ? 'update' : 'create' ?>">
            <?php if ($editPlayer): ?><input type="hidden" name="id" value="<?= $editPlayer['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome / Apelido *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($editPlayer['nome'] ?? '') ?>" placeholder="Ex: Pele">
                </div>
                <div class="form-group">
                    <label>Nome Real</label>
                    <input type="text" name="nome_real" value="<?= htmlspecialchars($editPlayer['nome_real'] ?? '') ?>" placeholder="Nome completo">
                </div>
                <div class="form-group">
                    <label>Posicao</label>
                    <select name="posicao_id">
                        <option value="">Selecione</option>
                        <?php foreach ($posicoes as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($editPlayer['posicao_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Gols Sofridos</label>
                    <input type="number" name="gols_sofridos" value="<?= $editPlayer['gols_sofridos'] ?? '' ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" value="<?= $editPlayer['data_nascimento'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Data de Falecimento</label>
                    <input type="date" name="data_falecimento" value="<?= $editPlayer['data_falecimento'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Foto (URL)</label>
                    <input type="text" name="foto" value="<?= htmlspecialchars($editPlayer['foto'] ?? '') ?>">
                </div>
                <div class="form-group" style="justify-content:flex-end;padding-bottom:.3rem;">
                    <label>No time?</label>
                    <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;font-size:.875rem;color:var(--text);">
                        <input type="checkbox" name="time" style="width:auto" <?= ($editPlayer['time'] ?? 0) ? 'checked' : '' ?>> Sim
                    </label>
                </div>
                <div class="form-group full">
                    <label>Descricao</label>
                    <textarea name="descricao"><?= htmlspecialchars($editPlayer['descricao'] ?? '') ?></textarea>
                </div>
                <div class="form-group full">
                    <label>Titulos</label>
                    <textarea name="titulos"><?= htmlspecialchars($editPlayer['titulos'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1.2rem;">
                <button type="submit" class="btn btn-primary"><?= $editPlayer ? 'Salvar alteracoes' : 'Cadastrar' ?></button>
                <a href="index.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
<?php if (empty($jogadores)): ?>
    <div class="empty-state">
        <div class="icon">&#9917;</div>
        <p>Nenhum jogador encontrado.</p>
        <?php if ($filterPosicao || $filterNome): ?><a href="index.php" style="color:var(--green);font-size:.85rem;">Limpar filtros</a><?php endif; ?>
    </div>
<?php else: ?>
    <table>
        <thead><tr>
            <th>#</th><th>Jogador</th><th>Nome Real</th><th>Posicao</th><th>Nascimento</th><th>Time</th><th style="text-align:right">Acoes</th>
        </tr></thead>
        <tbody>
        <?php foreach ($jogadores as $j): ?>
        <tr>
            <td style="color:var(--muted);font-size:.8rem;"><?= $j['id'] ?></td>
            <td>
                <div class="player-name-cell">
                    <div class="player-avatar"><?= strtoupper(substr($j['nome'],0,2)) ?></div>
                    <strong><?= htmlspecialchars($j['nome']) ?></strong>
                </div>
            </td>
            <td style="color:var(--muted)"><?= htmlspecialchars($j['nome_real'] ?: '-') ?></td>
            <td><?php if ($j['posicao']): ?><span class="badge"><?= htmlspecialchars($j['posicao']) ?></span><?php else: ?><span style="color:var(--muted)">-</span><?php endif; ?></td>
            <td style="color:var(--muted);font-size:.83rem;"><?= $j['data_nascimento'] ? date('d/m/Y',strtotime($j['data_nascimento'])) : '-' ?></td>
            <td><?= $j['time'] ? '<span style="color:var(--green);font-size:.8rem;">Ativo</span>' : '<span style="color:var(--muted);font-size:.8rem;">-</span>' ?></td>
            <td style="text-align:right">
                <div style="display:flex;justify-content:flex-end;gap:.4rem">
                    <a href="index.php?action=edit&id=<?= $j['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <form method="POST" action="index.php" onsubmit="return confirm('Remover <?= htmlspecialchars(addslashes($j['nome'])) ?>?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $j['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
<script>function toggleForm(){var f=document.getElementById('jogador-form');f.style.display=f.style.display==='none'?'block':'none';}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
