<?php
session_start();

require 'db.php';

ini_set('display_errors', 0);

/* =========================
   CSRF защита
========================= */

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die('CSRF attack detected');
}

/* =========================
   Получение данных
========================= */

$errors = [];
$values = $_POST;

$fio = trim($_POST['fio'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$birthdate = $_POST['birthdate'] ?? '';
$gender = $_POST['gender'] ?? '';
$biography = trim($_POST['biography'] ?? '');
$languages = $_POST['languages'] ?? [];
$contract = isset($_POST['contract']);

/* =========================
   Валидация
========================= */

if (!preg_match("/^[a-zA-Zа-яА-Я\s]{1,150}$/u", $fio)) {
    $errors['fio'] = "Допустимы только буквы и пробелы";
}

if (!preg_match("/^\+?[0-9\s\-]{7,20}$/", $phone)) {
    $errors['phone'] = "Некорректный телефон";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Некорректный email";
}

if (!in_array($gender, ['1', '2'])) {
    $errors['gender'] = "Выберите пол";
}

if (empty($languages)) {
    $errors['languages'] = "Выберите язык";
}

if (!$contract) {
    $errors['contract'] = "Подтвердите контракт";
}

/* mb_strlen заменён */
if (strlen($biography) > 1000) {
    $errors['biography'] = "Слишком длинная биография";
}

/* =========================
   Ошибки
========================= */

if (!empty($errors)) {

    setcookie(
        "errors",
        json_encode($errors),
        0,
        "/",
        "",
        false,
        true
    );

    setcookie(
        "values",
        json_encode($values),
        0,
        "/",
        "",
        false,
        true
    );

    header("Location: index.php");
    exit();
}

/* =========================
   Обновление пользователя
========================= */

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        UPDATE application SET
        fio=?,
        phone=?,
        email=?,
        birthdate=?,
        gender=?,
        biography=?,
        contract=?
        WHERE id=?
    ");

    $stmt->execute([
        $fio,
        $phone,
        $email,
        $birthdate ?: null,
        $gender,
        $biography,
        $contract ? 1 : 0,
        $_SESSION['user_id']
    ]);

} else {

    /* =========================
       Создание пользователя
    ========================= */

    $login = "user" . random_int(1000, 9999);

    $password = bin2hex(random_bytes(4));

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO application
        (
            fio,
            phone,
            email,
            birthdate,
            gender,
            biography,
            contract,
            login,
            password_hash
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $fio,
        $phone,
        $email,
        $birthdate ?: null,
        $gender,
        $biography,
        $contract ? 1 : 0,
        $login,
        $hash
    ]);

    $app_id = $pdo->lastInsertId();

    session_regenerate_id(true);

    $_SESSION['user_id'] = $app_id;

    $_SESSION['generated_login'] = $login;
    $_SESSION['generated_password'] = $password;
}

/* =========================
   Языки
========================= */

$pdo->prepare("
    DELETE FROM application_language
    WHERE application_id=?
")->execute([$_SESSION['user_id']]);

$stmtLang = $pdo->prepare("
    INSERT INTO application_language
    (application_id, language_id)
    VALUES (?, ?)
");

foreach ($languages as $lang) {

    $lang = intval($lang);

    $stmtLang->execute([
        $_SESSION['user_id'],
        $lang
    ]);
}

/* =========================
   Success
========================= */

setcookie(
    "success",
    "1",
    0,
    "/",
    "",
    false,
    true
);

header("Location: index.php");
exit();
