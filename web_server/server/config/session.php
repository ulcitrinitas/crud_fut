<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('user');
    session_start();
}

function setUserSession(array $user, array $info = []): void {
    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['info']     = $info;
}

function getUserSession(): array {
    return [
        'user_id'  => $_SESSION['user_id']  ?? null,
        'username' => $_SESSION['username'] ?? 'Visitante',
        'info'     => $_SESSION['info']     ?? [],
    ];
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function destroyUserSession(): void {
    session_unset();
    session_destroy();
}

// Para fins de demo sem auth, cria uma sessão fake se não existir
function ensureDemoSession(): void {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id']  = 0;
        $_SESSION['username'] = 'Demo';
        $_SESSION['info']     = [
            'nome'          => 'Usuário Demo',
            'dt_nascimento' => '1990-01-01',
            'cpf'           => '000.000.000-00',
        ];
    }
}
