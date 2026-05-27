<?php
// общие вспомогательные функции

ini_set('display_errors', '0');
error_reporting(E_ALL);

// отправка JSON-ответа
function jsonResponse(bool $success, $data = null, string $error = '', int $code = 200): void {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => $success];
    if ($data !== null) {
        $response['data'] = $data;
    }
    if ($error !== '') {
        $response['error'] = $error;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function getPostField(string $key, string $default = ''): string {
    return sanitize($_POST[$key] ?? $default);
}

function getGetField(string $key, string $default = ''): string {
    return sanitize($_GET[$key] ?? $default);
}

function validateRequired(array $fields, array $data): array {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "Поле «{$field}» обязательно для заполнения";
        }
    }
    return $errors;
}

function formatPrice(float $price): string {
    return number_format($price, 0, '.', ' ') . ' ₽';
}

function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
