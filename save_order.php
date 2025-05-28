<?php
// Allow CORS - adjust origin as needed for production
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "demo";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Database connection failed"]);
  exit;
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid JSON"]);
  exit;
}

// Validate required fields
$required = ['name', 'phone', 'address', 'quantity', 'delivery', 'total'];
foreach ($required as $field) {
  if (!isset($data[$field]) || $data[$field] === '') {
    http_response_code(400);
    echo json_encode(["error" => "$field is required"]);
    exit;
  }
}

// Additional validation for numeric fields


// Prepare and bind
$stmt = $conn->prepare("INSERT INTO orders (name, phone, address, quantity, price, delivery, total, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to prepare statement"]);
    exit;
}

// Bind parameters: s = string, i = integer, d = double
$stmt->bind_param(
  "sssiddd",
  $data['name'],
  $data['phone'],
  $data['address'],
$data['quantity'],
$data['price'],
$data['delivery'],
$data['total']
);

if ($stmt->execute()) {
  echo json_encode(["success" => true, "message" => "Order saved successfully"]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Failed to save order"]);
}

$stmt->close();
$conn->close();
