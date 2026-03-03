<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
ensureDemoSession();
$pdo = getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'update') {
        $fields = [
            'nome'           => trim($_POST['nome'] ?? ''),
            'internacional'  => isset($_POST['internacional']) ? 1 : 0,
        ];
        if (empty($fields['nome'])) { $_SESSION['flash_error'] = 'Nome obrigatorio.'; }
        elseif ($action === 'create') {
            $pdo->prepare("INSERT INTO competicoes (nome,internacional) VALUES (:nome,:internacional)")->execute($fields);
            $_SESSION['flash_success'] = 'Competicao criada!';
        } else {
            $fields['id'] = $id;
            $pdo->prepare("UPDATE competicoes SET nome=:nome,internacional=:internacional WHERE id=:id")->execute($fields);
            $_SESSION['flash_success'] = 'Competicao atualizada!';
        }
        header('Location: competicoes.php'); exit;
    }
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM competicoes WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Competicao removida.';
        header('Location: competicoes.php'); exit;
    }
}

$competicoes = $pdo->query("SELECT * FROM competicoes ORDER BY nome")->fetchAll();
$editItem = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM competicoes WHERE id=?"); $s->execute([$id]);
    $editItem = $s->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Competicoes</div><div class="page-subtitle"><?= count($competicoes) ?> competicao(oes)</div></div>
    <button class="btn btn-primary" onclick="toggleForm()">+ Nova Competicao</button>
</div>

<div id="item-form" style="display:<?= $editItem ? 'block' : 'none' ?>;margin-bottom:1.5rem;">
    <div class="card">
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--accent);margin-bottom:1rem;letter-spacing:1px;"><?= $editItem ? 'Editar Competicao' : 'Nova Competicao' ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>
            <div class="form-grid" style="max-width:500px">
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($editItem['nome'] ?? '') ?>" placeholder="Ex: Copa do Mundo, Libertadores...">
                </div>
                <div class="form-group" style="justify-content:flex-end;padding-bottom:.3rem;">
                    <label>Tipo</label>
                    <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;font-size:.875rem;color:var(--text);">
                        <input type="checkbox" name="internacional" style="width:auto" <?= ($editItem['internacional'] ?? 0) ? 'checked' : '' ?>> Internacional
                    </label>
                </div>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary"><?= $editItem ? 'Salvar' : 'Cadastrar' ?></button>
                <a href="competicoes.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
<?php if (empty($competicoes)): ?>
    <div class="empty-state"><div class="icon">&#127942;</div><p>Nenhuma competicao cadastrada.</p></div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Nome</th><th>Tipo</th><th style="text-align:right">Acoes</th></tr></thead>
        <tbody>
        <?php foreach ($competicoes as $c): ?>
        <tr>
            <td style="color:var(--muted)"><?= $c['id'] ?></td>
            <td><strong><?= htmlspecialchars($c['nome']) ?></strong></td>
            <td><?= $c['internacional'] ? '<span class="badge">Internacional</span>' : '<span style="color:var(--muted)">Nacional</span>' ?></td>
            <td style="text-align:right">
                <div style="display:flex;justify-content:flex-end;gap:.4rem">
                    <a href="competicoes.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <form method="POST" onsubmit="return confirm('Remover?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
