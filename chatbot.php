<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$message = $input["message"] ?? "";

$api_key = getenv("OPENAI_API_KEY");

if (!$api_key) {
    error_log("API Key not found");
    echo json_encode(["error_message" => "API Key is not configured"]);
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
error_log("Payload sent: " . $json_payload);

$options = array(
    "http" => array(
        "header"  => "Content-Type: application/json\r\n" .
                      "Authorization: Bearer $api_key\r\n",
        "method"  => "POST",
        "content" => $json_payload
    )
);

$context  = stream_context_create($options);
$result = @file_get_contents("https://api.openai.com/v1/chat/completions", false, $context);

if ($result === false) {
    $error = error_get_last();
    error_log("Error occurred during file_get_contents: " . $error['message']);
    echo json_encode(["error_message" => "Failed to connect to OpenAI"]);
    exit;
}

error_log("API Response: " . $result);

$response = json_decode($result, true);

if (isset($response["error"])) {
    echo json_encode(["error_message" => $response["error"]["message"]]);
} elseif (isset($response["choices"][0]["message"]["content"])) {
    echo json_encode(["reply" => $response["choices"][0]["message"]["content"]]);
} else {
    echo json_encode(["error_message" => "Unexpected API response structure"]);
}
?>
