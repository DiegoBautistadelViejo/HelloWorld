<?php
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$message = $input["message"];

$api_key = getenv("OPENAI_API_KEY");

$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful AI assistant."],
        ["role" => "user", "content" => $message]
    ]
];

$options = [
    "http" => [
        "header"  => "Content-Type: application/json\r\nAuthorization: Bearer $api_key\r\n",
        "method"  => "POST",
        "content" => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents("https://api.openai.com/v1/chat/completions", false, $context);
$response = json_decode($result, true);

// Check for errors in the API response
if (isset($response["error"])) {
    // If there's an error, return just the error message
    echo json_encode(["error_message" => $response["error"]["message"]]);
} else {
    // If no error, return the generated reply
    echo json_encode(["reply" => $response["choices"][0]["message"]["content"]]);
}
?>
