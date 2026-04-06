<?php
session_start();

$pdo = new PDO(
    'mysql:host=localhost;dbname=u82377',
    'u82377',
    '4d$TFWRr3'
);

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM application WHERE login=?");
$stmt->execute([$login]);

$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_login'] = $user['login'];

    header("Location: index.php");
    exit();
}

echo "Неверный логин или пароль";