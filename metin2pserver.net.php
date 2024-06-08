<?php
// API Token
$api_token = ''; 

// Database Config
$host = 'ip';
$username = 'user';
$password = 'password';
$database = 'account';

// Security Check
if (!isset($_POST['api_token']) || $_POST['api_token'] != $api_token ||
  !isset($_POST['account_id']) || !is_numeric($_POST['account_id'])) {
  die("Security check failed");
}

try {
  // Connect to database using PDO
  $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);

  $account_id = intval($_POST['account_id']);

  // Retrieve hwid from account table
  $stmt = $pdo->prepare("SELECT hwid FROM account WHERE id = :account_id");
  $stmt->execute(['account_id' => $account_id]);
  $row = $stmt->fetch();

  if (!$row) {
    die("No account found with the given ID");
  }

  $hwid = $row['hwid'];
  $timestamp = date('Y-m-d H:i:s');

  // Insert or update the vote4buff table
  $insert_query = "
    INSERT INTO vote4buff (account_id, hwid, time) 
    VALUES (:account_id, :hwid, :time) 
    ON DUPLICATE KEY UPDATE 
    hwid = VALUES(hwid), 
    time = VALUES(time)
  ";
  $stmt = $pdo->prepare($insert_query);
  $stmt->execute([
    'account_id' => $account_id,
    'hwid' => $hwid,
    'time' => $timestamp,
  ]);

  echo 'OK';
} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}
?>
