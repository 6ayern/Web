<?php
session_start(); // LAB7 ADDED

$errors = [];
$values = $_POST;

$fio = $_POST['fio'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$gender = $_POST['gender'] ?? '';
$biography = $_POST['biography'] ?? '';
$languages = $_POST['languages'] ?? [];
$contract = isset($_POST['contract']);

//валидация
if (!preg_match("/^[a-zA-Zа-яА-Я\s]{1,150}$/u", $fio)) {
    $errors['fio'] = "Допустимы только буквы и пробелы";
}

if (!preg_match("/^\+?[0-9\s\-]{7,20}$/", $phone)) {
    $errors['phone'] = "Некорректный телефон";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Некорректный email";
}

if (!in_array($gender, ['1','2'])) {
    $errors['gender'] = "Выберите пол";
}

if (empty($languages)) {
    $errors['languages'] = "Выберите язык";
}

if (!$contract) {
    $errors['contract'] = "Подтвердите контракт";
}

if (!empty($errors)) {
    setcookie("errors", json_encode($errors), 0, "/");
    setcookie("values", json_encode($values), 0, "/");

    header("Location: index.php");
    exit();
}

// база
$pdo = new PDO(
    'mysql:host=localhost;dbname=u82377',
    'u82377',
    '4d$TFWRr3'
);

// обновление авторизованного
if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        UPDATE application SET
        fio=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract=?
        WHERE id=?
    ");

    if (empty($birthdate)) {
        $birthdate = null;
    }
    $stmt->execute([
        $fio, $phone, $email, $birthdate, $gender,
        $biography, $contract ? 1 : 0,
        $_SESSION['user_id']
    ]);

} else {

    // генерация user
    $login = "user" . rand(1000,9999);
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

    $app_id = $pdo->lastInsertId();

    // сохранение логин/пароль
    $_SESSION['generated_login'] = $login;
    $_SESSION['generated_password'] = $password;

    $_SESSION['user_id'] = $app_id;
}

$pdo->prepare("DELETE FROM application_language WHERE application_id=?")
    ->execute([$_SESSION['user_id']]);

$stmtLang = $pdo->prepare("
    INSERT INTO application_language (application_id, language_id)
    VALUES (?, ?)
");

foreach ($languages as $lang) {
    $stmtLang->execute([$_SESSION['user_id'], $lang]);
}

setcookie("success", "1", 0, "/");

header("Location: index.php");
exit();