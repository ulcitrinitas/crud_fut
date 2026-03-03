<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
ensureDemoSession();
$pdo = getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    if ($action === 'create' || $action === 'update') {
        if (empty($nome)) { $_SESSION['flash_error'] = 'Nome obrigatorio.'; }
        elseif ($action === 'create') {
            $pdo->prepare("INSERT INTO posicoes (nome) VALUES (?)")->execute([$nome]);
            $_SESSION['flash_success'] = 'Posicao criada!';
        } else {
            $pdo->prepare("UPDATE posicoes SET nome=? WHERE id=?")->execute([$nome,$id]);
            $_SESSION['flash_success'] = 'Posicao atualizada!';
        }
        header('Location: posicoes.php'); exit;
    }
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM posicoes WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Posicao removida.';
        header('Location: posicoes.php'); exit;
    }
}

$posicoes = $pdo->query("SELECT p.*, COUNT(j.id) AS total_jogadores FROM posicoes p LEFT JOIN jogadores j ON j.posicao_id = p.id GROUP BY p.id ORDER BY p.nome")->fetchAll();

$editItem = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM posicoes WHERE id=?"); $s->execute([$id]);
    $editItem = $s->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Posicoes</div><div class="page-subtitle"><?= count($posicoes) ?> posicao(oes) cadastrada(s)</div></div>
    <button class="btn btn-primary" onclick="toggleForm()">+ Nova Posicao</button>
</div>

<div id="item-form" style="display:<?= $editItem ? 'block' : 'none' ?>;margin-bottom:1.5rem;">
    <div class="card">
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--accent);margin-bottom:1rem;letter-spacing:1px;"><?= $editItem ? 'Editar Posicao' : 'Nova Posicao' ?></h3>
        <form method="POST" action="posicoes.php">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>
            <div class="form-grid" style="max-width:400px">
                <div class="form-group">
                    <label>Nome da Posicao *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($editItem['nome'] ?? '') ?>" placeholder="Ex: Goleiro, Zagueiro...">
                </div>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary"><?= $editItem ? 'Salvar' : 'Cadastrar' ?></button>
                <a href="posicoes.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
<?php if (empty($posicoes)): ?>
    <div class="empty-state"><div class="icon">&#127939;</div><p>Nenhuma posicao cadastrada.</p></div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Nome</th><th>Total Jogadores</th><th style="text-align:right">Acoes</th></tr></thead>
        <tbody>
        <?php foreach ($posicoes as $p): ?>
        <tr>
            <td style="color:var(--muted)"><?= $p['id'] ?></td>
            <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
            <td><span class="badge"><?= $p['total_jogadores'] ?></span></td>
            <td style="text-align:right">
                <div style="display:flex;justify-content:flex-end;gap:.4rem">
                    <a href="posicoes.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <form method="POST" onsubmit="return confirm('Remover posicao?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
<script>function toggleForm(){var f=document.getElementById('item-form');f.style.display=f.style.display==='none'?'block':'none';}</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
