<?php
session_start();

$errors = [];
$values = [];
$success = false;

/* cookies */
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

/* пользователь */
$user = null;
$languages_user = [];

if (isset($_SESSION['user_id'])) {

    $pdo = new PDO('mysql:host=localhost;dbname=u82377', 'u82377', '4d$TFWRr3');

    $stmt = $pdo->prepare("SELECT * FROM application WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT language_id FROM application_language WHERE application_id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $languages_user = array_column($stmt->fetchAll(), 'language_id');
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Форма</title>
<link rel="stylesheet" href="style.css">

<style>
/* верхняя панель */
.top-bar {
    display: flex;
    justify-content: flex-end;
    padding: 15px 30px;
}

/* логин форма */
.login-inline {
    display: flex;
    gap: 10px;
    align-items: center;
}

.login-inline input {
    padding: 6px 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* блок с логином/паролем */
.credentials {
    position: absolute;
    right: 30px;
    top: 70px;
    background: #fff3cd;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 14px;
}
</style>
</head>

    <body>
        <div class="top-bar">

        <?php if (!isset($_SESSION['user_id'])): ?>

            <form method="POST" action="login.php" class="login-inline">
                <input type="text" name="login" placeholder="Логин">
                <input type="password" name="password" placeholder="Пароль">
                <button>Войти</button>
            </form>

        <?php else: ?>

            <div>
                Вы вошли как <b><?= htmlspecialchars($user['login'] ?? '') ?></b>
                <a href="logout.php">Выйти</a>
            </div>

        <?php endif; ?>

        </div>

        <?php if (isset($_SESSION['generated_login'])): ?>
        <div class="credentials">
            <b>Ваши данные:</b><br>
            Логин: <?= $_SESSION['generated_login'] ?><br>
            Пароль: <?= $_SESSION['generated_password'] ?>
        </div>

        <?php unset($_SESSION['generated_login'], $_SESSION['generated_password']); endif; ?>



        <form method="POST" action="submit.php">

        <h2>Форма заявки</h2>

        <?php if ($success): ?>
        <div class="success">Заявка успешно отправлена ✅</div>
        <?php endif; ?>

        
        <label>ФИО</label>
        <input type="text" name="fio"
        value="<?= htmlspecialchars($user['fio'] ?? $values['fio'] ?? '') ?>"
        class="<?= isset($errors['fio']) ? 'error' : '' ?>">

        <?php if (isset($errors['fio'])): ?>
        <div class="error-text"><?= $errors['fio'] ?></div>
        <?php endif; ?>


        
        <label>Телефон</label>
        <input type="tel" name="phone"
        value="<?= htmlspecialchars($user['phone'] ?? $values['phone'] ?? '') ?>"
        class="<?= isset($errors['phone']) ? 'error' : '' ?>">

        <?php if (isset($errors['phone'])): ?>
        <div class="error-text"><?= $errors['phone'] ?></div>
        <?php endif; ?>


        
        <label>Email</label>
        <input type="email" name="email"
        value="<?= htmlspecialchars($user['email'] ?? $values['email'] ?? '') ?>"
        class="<?= isset($errors['email']) ? 'error' : '' ?>">

        <?php if (isset($errors['email'])): ?>
        <div class="error-text"><?= $errors['email'] ?></div>
        <?php endif; ?>


        <label>Дата рождения</label>
        <input type="date" name="birthdate"
        value="<?= htmlspecialchars($user['birthdate'] ?? $values['birthdate'] ?? '') ?>">


        <label>Пол</label>

        <div class="gender-group">

        <label>
        <input type="radio" name="gender" value="1"
        <?= (($user['gender'] ?? $values['gender'] ?? '') == '1') ? 'checked' : '' ?>>
        Мужской
        </label>

        <label>
        <input type="radio" name="gender" value="2"
        <?= (($user['gender'] ?? $values['gender'] ?? '') == '2') ? 'checked' : '' ?>>
        Женский
        </label>

        </div>

        <?php if (isset($errors['gender'])): ?>
        <div class="error-text"><?= $errors['gender'] ?></div>
        <?php endif; ?>


        
        <label>Любимые языки</label>

        <select name="languages[]" multiple
        class="<?= isset($errors['languages']) ? 'error' : '' ?>">

        <?php
        $languages = [
        1=>"Pascal",2=>"C",3=>"C++",4=>"JS",
        5=>"PHP",6=>"Python"
        ];

        foreach ($languages as $id=>$name):

        $selected = in_array($id, $languages_user ?? ($values['languages'] ?? [])) ? "selected" : "";
        ?>

        <option value="<?= $id ?>" <?= $selected ?>><?= $name ?></option>

        <?php endforeach; ?>

        </select>

        <?php if (isset($errors['languages'])): ?>
        <div class="error-text"><?= $errors['languages'] ?></div>
        <?php endif; ?>


        
        <label>Биография</label>

        <textarea name="biography"
        class="<?= isset($errors['biography']) ? 'error' : '' ?>">
        <?= htmlspecialchars($user['biography'] ?? $values['biography'] ?? '') ?>
        </textarea>

        <?php if (isset($errors['biography'])): ?>
        <div class="error-text"><?= $errors['biography'] ?></div>
        <?php endif; ?>


        
        <label>
        <input type="checkbox" name="contract"
        <?= ($user['contract'] ?? $values['contract'] ?? false) ? "checked" : "" ?>>
        С контрактом ознакомлен
        </label>

        <?php if (isset($errors['contract'])): ?>
        <div class="error-text"><?= $errors['contract'] ?></div>
        <?php endif; ?>


        <br><br>
        <button type="submit">Сохранить</button>
        </form>
    </body>
</html>