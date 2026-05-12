<?php
session_start();

require 'db.php';

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("
    SELECT *
    FROM application
    WHERE login=?
");

$stmt->execute([$login]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    $user &&
    password_verify($password, $user['password_hash'])
) {

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];

    header("Location: index.php");
    exit();
}

die('Неверный логин или пароль');