<?php
require "db.php";

$type = $_POST['type'];
$coin = $_POST['coin'];
$amount = $_POST['amount'];
$price = $_POST['price'];

$sql = "INSERT INTO transactions (type, coin, amount, price) VALUES (:type, :coin, :amount, :price)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'type' => $type,
    'coin' => $coin,
    'amount' => $amount,
    'price' => $price
]);

echo "Transaction recorded successfully.";
?>