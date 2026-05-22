<?php
/**
 * Authentication helpers for КЛИК-КЛАВ
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        if (isAjax()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
            exit;
        }
        header('Location: /login-modal.html');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        if (isAjax()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Доступ запрещён']);
            exit;
        }
        header('Location: /index.html');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
