<?php
session_start();

require 'db.php';

/* CSRF */
if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('CSRF error');
}

/* данные */
$fio = trim($_POST['fio'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$birthdate = $_POST['birthdate'] ?: null;
$gender = $_POST['gender'] ?? '';
$biography = trim($_POST['biography'] ?? '');
$languages = $_POST['languages'] ?? [];
$contract = isset($_POST['contract']);

$errors = [];

/* валидация */
if ($fio === '') $errors['fio'] = 'fio';
if ($phone === '') $errors['phone'] = 'phone';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'email';
if (!in_array($gender, ['1','2'])) $errors['gender'] = 'gender';
if (!$contract) $errors['contract'] = 'contract';
if (strlen($biography) > 1000) $errors['biography'] = 'bio';

if ($errors) {
    setcookie("errors", json_encode($errors), 0, "/");
    setcookie("values", json_encode($_POST), 0, "/");
    header("Location: index.php");
    exit();
}

/* если пользователь есть */
if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        UPDATE application SET
        fio=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract=?
        WHERE id=?
    ");

    $stmt->execute([
        $fio, $phone, $email, $birthdate,
        $gender, $biography, $contract ? 1 : 0,
        $_SESSION['user_id']
    ]);

} else {

    $login = "user" . random_int(1000, 9999);
    $password = bin2hex(random_bytes(4));
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO application
        (fio, phone, email, birthdate, gender, biography, contract, login, password_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $fio, $phone, $email, $birthdate,
        $gender, $biography, $contract ? 1 : 0,
        $login, $hash
    ]);

    $id = $pdo->lastInsertId();

    $_SESSION['user_id'] = $id;

    /* ВОТ ЭТО ГЛАВНОЕ — теперь логин всегда показывается */
    $_SESSION['generated_login'] = $login;
    $_SESSION['generated_password'] = $password;
}

setcookie("success", "1", 0, "/");

header("Location: index.php");
exit();