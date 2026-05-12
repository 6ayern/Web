<?php
session_start();

require 'db.php';

/* CSRF */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$values = [];

if (isset($_COOKIE["errors"])) {
    $errors = json_decode($_COOKIE["errors"], true) ?? [];
    setcookie("errors", "", time() - 3600, "/");
}

if (isset($_COOKIE["values"])) {
    $values = json_decode($_COOKIE["values"], true) ?? [];
    setcookie("values", "", time() - 3600, "/");
}

if (isset($_COOKIE["success"])) {
    setcookie("success", "", time() - 3600, "/");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Форма</title>

<link rel="stylesheet" href="style.css">

<style>
.credentials {
    background:#111;
    color:#fff;
    padding:10px;
    margin-bottom:15px;
    border-radius:8px;
}
</style>
</head>

<body>

<?php if (isset($_SESSION['generated_login'])): ?>
    <div style="background:#eee;padding:10px;margin-bottom:10px;">
        <b>Ваши данные:</b><br>
        Логин: <?= htmlspecialchars($_SESSION['generated_login']) ?><br>
        Пароль: <?= htmlspecialchars($_SESSION['generated_password']) ?>
    </div>

    <?php
    unset($_SESSION['generated_login'], $_SESSION['generated_password']);
    ?>
<?php endif; ?>

<form method="POST" action="submit.php">

<input type="hidden" name="csrf_token"
       value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

<h2>Форма</h2>

<input type="text" name="fio" placeholder="ФИО"
       value="<?= htmlspecialchars($values['fio'] ?? '') ?>">

<br><br>

<input type="text" name="phone" placeholder="Телефон"
       value="<?= htmlspecialchars($values['phone'] ?? '') ?>">

<br><br>

<input type="email" name="email" placeholder="Email"
       value="<?= htmlspecialchars($values['email'] ?? '') ?>">

<br><br>

<input type="date" name="birthdate">

<br><br>

<label>
<input type="radio" name="gender" value="1"> Муж
</label>

<label>
<input type="radio" name="gender" value="2"> Жен
</label>

<br><br>

<select name="languages[]" multiple>
    <option value="1">PHP</option>
    <option value="2">C++</option>
    <option value="3">JS</option>
</select>

<br><br>

<textarea name="biography"></textarea>

<br><br>

<label>
<input type="checkbox" name="contract">
Согласен
</label>

<br><br>

<button type="submit">Отправить</button>

</form>

</body>
</html>