<?php
session_start();

require 'db.php';

ini_set('display_errors', 0);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$values = [];
$success = false;

if (isset($_COOKIE["errors"])) {
    $errors = json_decode($_COOKIE["errors"], true);
    setcookie("errors", "", time() - 3600, "/");
}

if (isset($_COOKIE["values"])) {
    $values = json_decode($_COOKIE["values"], true);
    setcookie("values", "", time() - 3600, "/");
}

if (isset($_COOKIE["success"])) {
    $success = true;
    setcookie("success", "", time() - 3600, "/");
}

$user = null;
$languages_user = [];

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM application
        WHERE id=?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT language_id
        FROM application_language
        WHERE application_id=?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $languages_user = array_column(
        $stmt->fetchAll(PDO::FETCH_ASSOC),
        'language_id'
    );
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Форма</title>

<link rel="stylesheet" href="style.css">
</head>

<body>

<form method="POST" action="submit.php">

<input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
>

<h2>Форма заявки</h2>

<?php if ($success): ?>
<div class="success">
    Заявка успешно отправлена
</div>
<?php endif; ?>

<label>ФИО</label>

<input
    type="text"
    name="fio"
    value="<?= htmlspecialchars($user['fio'] ?? $values['fio'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
>

<?php if (isset($errors['fio'])): ?>
<div class="error-text">
    <?= htmlspecialchars($errors['fio']) ?>
</div>
<?php endif; ?>

<label>Email</label>

<input
    type="email"
    name="email"
    value="<?= htmlspecialchars($user['email'] ?? $values['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
>

<label>Биография</label>

<textarea name="biography"><?= htmlspecialchars($user['biography'] ?? $values['biography'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

<button type="submit">
    Сохранить
</button>

</form>

</body>
</html>