<?php
session_start();

require 'db.php';

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('CSRF attack detected');
}

$errors = [];

$fio = trim($_POST['fio'] ?? '');
$email = trim($_POST['email'] ?? '');
$biography = trim($_POST['biography'] ?? '');

if (!preg_match("/^[a-zA-Zа-яА-Я\s]{1,150}$/u", $fio)) {
    $errors['fio'] = "Некорректное ФИО";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Некорректный email";
}

if (mb_strlen($biography) > 1000) {
    $errors['biography'] = "Слишком длинная биография";
}

if (!empty($errors)) {

    setcookie(
        "errors",
        json_encode($errors),
        0,
        "/",
        "",
        true,
        true
    );

    header("Location: index.php");
    exit();
}

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        UPDATE application
        SET fio=?, email=?, biography=?
        WHERE id=?
    ");

    $stmt->execute([
        $fio,
        $email,
        $biography,
        $_SESSION['user_id']
    ]);

} else {

    $login = "user" . random_int(1000, 9999);

    $password = bin2hex(random_bytes(4));

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO application
        (fio, email, biography, login, password_hash)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $fio,
        $email,
        $biography,
        $login,
        $hash
    ]);

    session_regenerate_id(true);

    $_SESSION['user_id'] = $pdo->lastInsertId();

    $_SESSION['generated_login'] = $login;
    $_SESSION['generated_password'] = $password;
}

setcookie(
    "success",
    "1",
    0,
    "/",
    "",
    true,
    true
);

header("Location: index.php");
exit();