<?php
session_start();

require 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$adminLogin = 'admin';

$adminHash = password_hash('StrongAdminPassword', PASSWORD_DEFAULT);

if (
    empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $adminLogin ||
    !password_verify($_SERVER['PHP_AUTH_PW'], $adminHash)
) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin panel"');
    exit('Требуется авторизация');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('CSRF detected');
    }

    if (isset($_POST['delete'])) {

        $id = intval($_POST['delete']);

        $pdo->prepare("
            DELETE FROM application_language
            WHERE application_id=?
        ")->execute([$id]);

        $pdo->prepare("
            DELETE FROM application
            WHERE id=?
        ")->execute([$id]);

        header("Location: admin.php");
        exit();
    }
}

$stmt = $pdo->query("SELECT * FROM application");

$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin</title>
</head>

<body>

<h1>Админ панель</h1>

<table border="1">

<tr>
    <th>ID</th>
    <th>ФИО</th>
    <th>Email</th>
    <th>Био</th>
    <th>Действия</th>
</tr>

<?php foreach ($apps as $app): ?>

<tr>

<td><?= intval($app['id']) ?></td>

<td>
<?= htmlspecialchars($app['fio'], ENT_QUOTES, 'UTF-8') ?>
</td>

<td>
<?= htmlspecialchars($app['email'], ENT_QUOTES, 'UTF-8') ?>
</td>

<td>
<?= htmlspecialchars($app['biography'], ENT_QUOTES, 'UTF-8') ?>
</td>

<td>

<form method="POST">

<input
    type="hidden"
    name="csrf_token"
    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
>

<button
    type="submit"
    name="delete"
    value="<?= intval($app['id']) ?>"
>
    Удалить
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</table>

</body>
</html>