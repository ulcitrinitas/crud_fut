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
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo' => $_POST['tipo'] ?? null,
            'foto' => trim($_POST['foto'] ?? ''),
        ];
        if (empty($fields['nome'])) { $_SESSION['flash_error'] = 'Nome obrigatorio.'; }
        elseif ($action === 'create') {
            $pdo->prepare("INSERT INTO uniformes (nome,tipo,foto) VALUES (:nome,:tipo,:foto)")->execute($fields);
            $_SESSION['flash_success'] = 'Uniforme cadastrado!';
        } else {
            $fields['id'] = $id;
            $pdo->prepare("UPDATE uniformes SET nome=:nome,tipo=:tipo,foto=:foto WHERE id=:id")->execute($fields);
            $_SESSION['flash_success'] = 'Uniforme atualizado!';
        }
        header('Location: uniformes.php'); exit;
    }
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM uniformes WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Uniforme removido.';
        header('Location: uniformes.php'); exit;
    }
}

$uniformes = $pdo->query("SELECT * FROM uniformes ORDER BY tipo, nome")->fetchAll();
$editItem = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM uniformes WHERE id=?"); $s->execute([$id]);
    $editItem = $s->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Uniformes</div><div class="page-subtitle"><?= count($uniformes) ?> uniforme(s)</div></div>
    <button class="btn btn-primary" onclick="toggleForm()">+ Novo Uniforme</button>
</div>

<div id="item-form" style="display:<?= $editItem ? 'block' : 'none' ?>;margin-bottom:1.5rem;">
    <div class="card">
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--accent);margin-bottom:1rem;letter-spacing:1px;"><?= $editItem ? 'Editar Uniforme' : 'Novo Uniforme' ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($editItem['nome'] ?? '') ?>" placeholder="Ex: Camisa titular 2024">
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="">Selecione</option>
                        <option value="camisa"  <?= ($editItem['tipo'] ?? '') === 'camisa'  ? 'selected' : '' ?>>Camisa</option>
                        <option value="calcao"  <?= ($editItem['tipo'] ?? '') === 'calcao'  ? 'selected' : '' ?>>Calcao</option>
                        <option value="meiao"   <?= ($editItem['tipo'] ?? '') === 'meiao'   ? 'selected' : '' ?>>Meiao</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Foto (URL)</label>
                    <input type="text" name="foto" value="<?= htmlspecialchars($editItem['foto'] ?? '') ?>">
                </div>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary"><?= $editItem ? 'Salvar' : 'Cadastrar' ?></button>
                <a href="uniformes.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
<?php if (empty($uniformes)): ?>
    <div class="empty-state"><div class="icon">&#128084;</div><p>Nenhum uniforme cadastrado.</p></div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Nome</th><th>Tipo</th><th style="text-align:right">Acoes</th></tr></thead>
        <tbody>
        <?php foreach ($uniformes as $u): ?>
        <tr>
            <td style="color:var(--muted)"><?= $u['id'] ?></td>
            <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
            <td><?= $u['tipo'] ? '<span class="badge">'.htmlspecialchars(ucfirst($u['tipo'])).'</span>' : '<span style="color:var(--muted)">-</span>' ?></td>
            <td style="text-align:right">
                <div style="display:flex;justify-content:flex-end;gap:.4rem">
                    <a href="uniformes.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <form method="POST" onsubmit="return confirm('Remover?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
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
