<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$message = $input["message"];

$api_key = getenv("OPENAI_API_KEY");

if (!$api_key) {
    error_log("API Key no encontrada");
    echo json_encode(["error_message" => "API Key no configurada"]);
    exit;
}

$data = array(
    "model" => "gpt-3.5-turbo",
    "messages" => array(
        array("role" => "system", "content" => "You are a helpful AI assistant."),
        array("role" => "user", "content" => $message)
    ),
    "max_tokens" => 100,
    "temperature" => 0.7
);

$json_payload = json_encode($data, JSON_PRETTY_PRINT);
error_log("Payload enviado: " . $json_payload);

$options = array(
    "http" => array(
        "header"  => "Content-Type: application/json\r\n" .
                     "Authorization: Bearer $api_key\r\n" .
                     "Content-Length: " . strlen($json_payload) . "\r\n",
        "method"  => "POST",
        "content" => $json_payload
    )
);

$context  = stream_context_create($options);
$result = file_get_contents("https://api.openai.com/v1/chat/completions", false, $context);

if ($result === false) {
    error_log("Error al llamar a la API de OpenAI");
    echo json_encode(["error_message" => "Error al conectarse con OpenAI"]);
    exit;
}

$response = json_decode($result, true);

if (isset($response["error"])) {
    echo json_encode(["error_message" => $response["error"]["message"]]);
} else {
    echo json_encode(["reply" => $response["choices"][0]["message"]["content"]]);
}
?>
