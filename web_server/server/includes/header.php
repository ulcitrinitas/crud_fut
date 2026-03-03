<?php
require_once __DIR__ . '/../config/session.php';
ensureDemoSession();
$sessionUser = getUserSession();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚽ Futebol DB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:  #00c853;
            --dark:   #0a0f0d;
            --mid:    #111a14;
            --panel:  #162019;
            --border: #1e3022;
            --text:   #d4edd8;
            --muted:  #6b8f70;
            --accent: #e8ff3e;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── NAV ── */
        nav {
            background: var(--mid);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            height: 58px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem;
            color: var(--accent);
            letter-spacing: 2px;
            text-decoration: none;
            flex-shrink: 0;
        }

        .nav-links {
            display: flex;
            gap: 0.25rem;
            flex: 1;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            transition: all .15s;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--text);
            background: var(--panel);
        }

        .nav-links a.active {
            color: var(--accent);
        }

        /* ── SESSION BADGE ── */
        .session-badge {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 0.3rem 0.8rem 0.3rem 0.5rem;
            font-size: 0.8rem;
            cursor: pointer;
            transition: border-color .15s;
            position: relative;
        }

        .session-badge:hover { border-color: var(--green); }

        .session-avatar {
            width: 26px; height: 26px;
            background: var(--green);
            border-radius: 50%;
            display: grid; place-items: center;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--dark);
            flex-shrink: 0;
        }

        .session-name { color: var(--text); font-weight: 500; }

        /* session dropdown */
        .session-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            min-width: 220px;
            z-index: 200;
        }

        .session-badge:hover .session-dropdown { display: block; }

        .session-dropdown h4 {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 0.6rem;
        }

        .session-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            padding: 0.25rem 0;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        .session-row:last-child { border-bottom: none; }
        .session-row span:first-child { color: var(--muted); }

        /* ── MAIN ── */
        main {
            flex: 1;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2rem;
            color: var(--accent);
            letter-spacing: 2px;
            line-height: 1;
        }

        .page-subtitle {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.2rem;
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
            border: none;
        }

        .btn-primary {
            background: var(--green);
            color: var(--dark);
        }

        .btn-primary:hover { background: #00e060; transform: translateY(-1px); }

        .btn-danger {
            background: #2a0f0f;
            color: #ff6b6b;
            border: 1px solid #4a1a1a;
        }

        .btn-danger:hover { background: #3d1414; }

        .btn-ghost {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover { color: var(--text); border-color: var(--muted); }

        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.78rem; }

        /* ── TABLE ── */
        .table-wrap {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        th {
            background: var(--mid);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            padding: 0.9rem 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }

        tr:hover td { background: rgba(0,200,83,.04); }

        /* ── BADGE ── */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            background: rgba(0,200,83,.12);
            color: var(--green);
            border: 1px solid rgba(0,200,83,.2);
        }

        /* ── FORM ── */
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.8rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--muted);
        }

        input, select, textarea {
            background: var(--mid);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.6rem 0.85rem;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.875rem;
            transition: border-color .15s;
            width: 100%;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--green);
        }

        textarea { resize: vertical; min-height: 90px; }

        select option { background: var(--mid); }

        /* ── FILTER BAR ── */
        .filter-bar {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem 1.2rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .filter-bar .form-group { flex: 1; min-width: 160px; margin: 0; }

        /* ── ALERT ── */
        .alert {
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(0,200,83,.1);
            border: 1px solid rgba(0,200,83,.25);
            color: #70ffaa;
        }

        .alert-error {
            background: rgba(255,80,80,.1);
            border: 1px solid rgba(255,80,80,.25);
            color: #ffaaaa;
        }

        /* ── AVATAR ── */
        .player-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--green), #007a33);
            border-radius: 50%;
            display: grid; place-items: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--dark);
            flex-shrink: 0;
        }

        .player-name-cell {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--muted);
        }

        .empty-state .icon { font-size: 3rem; margin-bottom: 0.75rem; }

        /* ── FOOTER ── */
        footer {
            text-align: center;
            padding: 1.2rem;
            font-size: 0.78rem;
            color: var(--muted);
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="nav-brand">⚽ FutebolDB</a>
    <div class="nav-links">
        <a href="index.php"    class="<?= $currentPage === 'index'     ? 'active' : '' ?>">Jogadores</a>
        <a href="posicoes.php" class="<?= $currentPage === 'posicoes'   ? 'active' : '' ?>">Posições</a>
        <a href="tecnicos.php" class="<?= $currentPage === 'tecnicos'   ? 'active' : '' ?>">Técnicos</a>
        <a href="competicoes.php" class="<?= $currentPage === 'competicoes' ? 'active' : '' ?>">Competições</a>
        <a href="uniformes.php" class="<?= $currentPage === 'uniformes' ? 'active' : '' ?>">Uniformes</a>
    </div>

    <!-- Session Badge -->
    <div class="session-badge">
        <div class="session-avatar"><?= strtoupper(substr($sessionUser['username'], 0, 1)) ?></div>
        <span class="session-name"><?= htmlspecialchars($sessionUser['username']) ?></span>
        <span style="color:var(--muted)">▾</span>
        <div class="session-dropdown">
            <h4>Sessão do Usuário</h4>
            <div class="session-row">
                <span>ID</span>
                <span><?= $sessionUser['user_id'] ?? '—' ?></span>
            </div>
            <div class="session-row">
                <span>Username</span>
                <span><?= htmlspecialchars($sessionUser['username']) ?></span>
            </div>
            <?php if (!empty($sessionUser['info'])): ?>
            <div class="session-row">
                <span>Nome</span>
                <span><?= htmlspecialchars($sessionUser['info']['nome'] ?? '—') ?></span>
            </div>
            <div class="session-row">
                <span>Nascimento</span>
                <span><?= htmlspecialchars($sessionUser['info']['dt_nascimento'] ?? '—') ?></span>
            </div>
            <div class="session-row">
                <span>CPF</span>
                <span><?= htmlspecialchars($sessionUser['info']['cpf'] ?? '—') ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
