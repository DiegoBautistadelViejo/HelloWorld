<?php
// Set the response header to return JSON
header("Content-Type: application/json");

// Retrieve and decode the JSON input
$input = json_decode(file_get_contents("php://input"), true);
$message = $input["message"] ?? ""; // Default to empty string if not provided

// Retrieve API key from environment variables
$api_key = getenv("OPENAI_API_KEY");

// Check if the API key is set
if (!$api_key) {
    error_log("API Key not found");
    echo json_encode(["error_message" => "API Key is not configured"]);
    exit;
}

// Prepare the request payload
$data = array(
    "model" => "gpt-3.5-turbo",
    "messages" => array(
        array("role" => "system", "content" => "You are a helpful AI assistant."),
        array("role" => "user", "content" => $message)
    ),
    "max_tokens" => 100,
    "temperature" => 0.7
);

// Convert the data array to JSON format
$json_payload = json_encode($data, JSON_PRETTY_PRINT);
error_log("Payload sent: " . $json_payload);

// Set up the HTTP request headers and options
$options = array(
    "http" => array(
        "header"  => "Content-Type: application/json\r\n" .
                      "Authorization: Bearer $api_key\r\n", // API authentication
        "method"  => "POST",
        "content" => $json_payload // Request body
    )
);

// Create a stream context for the API request
$context  = stream_context_create($options);

// Make the request to OpenAI's API
$result = file_get_contents("https://api.openai.com/v1/chat/completions", false, $context);

// Handle request failure
if ($result === false) {
    error_log("Failed to connect to OpenAI API");
    echo json_encode(["error_message" => "Failed to connect to OpenAI"]);
    exit;
}

// Decode the API response
$response = json_decode($result, true);

// Check if the API returned an error
if (isset($response["error"])) {
    echo json_encode(["error_message" => $response["error"]["message"]]);
} else {
    echo json_encode(["reply" => $response["choices"][0]["message"]["content"]]);
}
?>
