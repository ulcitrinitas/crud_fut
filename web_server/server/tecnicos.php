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
            'nome'             => trim($_POST['nome'] ?? ''),
            'descricao'        => trim($_POST['descricao'] ?? ''),
            'foto'             => trim($_POST['foto'] ?? ''),
            'data_nascimento'  => $_POST['data_nascimento'] ?: null,
            'data_falecimento' => $_POST['data_falecimento'] ?: null,
            'time'             => isset($_POST['time']) ? 1 : 0,
        ];
        if (empty($fields['nome'])) { $_SESSION['flash_error'] = 'Nome obrigatorio.'; }
        elseif ($action === 'create') {
            $pdo->prepare("INSERT INTO tecnicos (nome,descricao,foto,data_nascimento,data_falecimento,time) VALUES (:nome,:descricao,:foto,:data_nascimento,:data_falecimento,:time)")->execute($fields);
            $_SESSION['flash_success'] = 'Tecnico cadastrado!';
        } else {
            $fields['id'] = $id;
            $pdo->prepare("UPDATE tecnicos SET nome=:nome,descricao=:descricao,foto=:foto,data_nascimento=:data_nascimento,data_falecimento=:data_falecimento,time=:time WHERE id=:id")->execute($fields);
            $_SESSION['flash_success'] = 'Tecnico atualizado!';
        }
        header('Location: tecnicos.php'); exit;
    }
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM tecnicos WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Tecnico removido.';
        header('Location: tecnicos.php'); exit;
    }
}

$tecnicos = $pdo->query("SELECT * FROM tecnicos ORDER BY nome")->fetchAll();
$editItem = null;
if ($action === 'edit' && $id) {
    $s = $pdo->prepare("SELECT * FROM tecnicos WHERE id=?"); $s->execute([$id]);
    $editItem = $s->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div><div class="page-title">Tecnicos</div><div class="page-subtitle"><?= count($tecnicos) ?> tecnico(s)</div></div>
    <button class="btn btn-primary" onclick="toggleForm()">+ Novo Tecnico</button>
</div>

<div id="item-form" style="display:<?= $editItem ? 'block' : 'none' ?>;margin-bottom:1.5rem;">
    <div class="card">
        <h3 style="font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--accent);margin-bottom:1rem;letter-spacing:1px;"><?= $editItem ? 'Editar Tecnico' : 'Novo Tecnico' ?></h3>
        <form method="POST">
            <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nome *</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($editItem['nome'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Foto (URL)</label>
                    <input type="text" name="foto" value="<?= htmlspecialchars($editItem['foto'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Data de Nascimento</label>
                    <input type="date" name="data_nascimento" value="<?= $editItem['data_nascimento'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Data de Falecimento</label>
                    <input type="date" name="data_falecimento" value="<?= $editItem['data_falecimento'] ?? '' ?>">
                </div>
                <div class="form-group" style="justify-content:flex-end;padding-bottom:.3rem;">
                    <label>No time?</label>
                    <label style="display:flex;align-items:center;gap:.5rem;text-transform:none;font-size:.875rem;color:var(--text);">
                        <input type="checkbox" name="time" style="width:auto" <?= ($editItem['time'] ?? 0) ? 'checked' : '' ?>> Sim
                    </label>
                </div>
                <div class="form-group full">
                    <label>Descricao</label>
                    <textarea name="descricao"><?= htmlspecialchars($editItem['descricao'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="display:flex;gap:.75rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary"><?= $editItem ? 'Salvar' : 'Cadastrar' ?></button>
                <a href="tecnicos.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
<?php if (empty($tecnicos)): ?>
    <div class="empty-state"><div class="icon">&#128100;</div><p>Nenhum tecnico cadastrado.</p></div>
<?php else: ?>
    <table>
        <thead><tr><th>#</th><th>Nome</th><th>Nascimento</th><th>Time</th><th style="text-align:right">Acoes</th></tr></thead>
        <tbody>
        <?php foreach ($tecnicos as $t): ?>
        <tr>
            <td style="color:var(--muted)"><?= $t['id'] ?></td>
            <td><strong><?= htmlspecialchars($t['nome']) ?></strong></td>
            <td style="color:var(--muted)"><?= $t['data_nascimento'] ? date('d/m/Y',strtotime($t['data_nascimento'])) : '-' ?></td>
            <td><?= $t['time'] ? '<span style="color:var(--green);font-size:.8rem;">Ativo</span>' : '<span style="color:var(--muted)">-</span>' ?></td>
            <td style="text-align:right">
                <div style="display:flex;justify-content:flex-end;gap:.4rem">
                    <a href="tecnicos.php?action=edit&id=<?= $t['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                    <form method="POST" onsubmit="return confirm('Remover?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
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
