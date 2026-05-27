<?php
// вспомогательные функции

// отправка JSON-ответа
function jsonResponse($success, $data = null, $error = ''){
    if(ob_get_level() > 0){
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => $success];
    if($data !== null){
        $response['data'] = $data;
    }
    if($error !== ''){
        $response['error'] = $error;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
