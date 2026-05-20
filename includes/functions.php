<?php
/**
 * Shared utility functions for КЛИК-КЛАВ
 */

function jsonResponse(bool $success, $data = null, string $error = '', int $code = 200): void {
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
    return number_format($price, 0, ',', ' ') . ' ₽';
}

function setCorsHeaders(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }
}
