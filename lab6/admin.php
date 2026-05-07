<?php
if (
    empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    $_SERVER['PHP_AUTH_PW'] != '123'
) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin panel"');
    echo "Требуется авторизация";
    exit();
}


$pdo = new PDO(
    'mysql:host=localhost;dbname=u82377',
    'u82377',
    '4d$TFWRr3'
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $pdo->prepare("DELETE FROM application_language WHERE application_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM application WHERE id=?")->execute([$id]);

    header("Location: admin.php");
    exit();
}

if (isset($_POST['update'])) {

    $id = $_POST['id'];

    $pdo->prepare("
        UPDATE application 
        SET fio=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract=?
        WHERE id=?
    ")->execute([
                $_POST['fio'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['birthdate'] ?: null,
                $_POST['gender'],
                $_POST['biography'],
                isset($_POST['contract']) ? 1 : 0,
                $id
            ]);

    
    $pdo->prepare("DELETE FROM application_language WHERE application_id=?")->execute([$id]);

    if (!empty($_POST['languages'])) {
        $stmt = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
        foreach ($_POST['languages'] as $lang) {
            $stmt->execute([$id, $lang]);
        }
    }

    header("Location: admin.php");
    exit();
}


$stmt = $pdo->query("SELECT * FROM application");
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);


$languages_list = [
    1 => "Pascal",
    2 => "C",
    3 => "C++",
    4 => "JavaScript",
    5 => "PHP",
    6 => "Python",
    7 => "Java",
    8 => "Haskell",
    9 => "Clojure",
    10 => "Prolog",
    11 => "Scala",
    12 => "Go"
];


$edit_id = $_GET['edit'] ?? null;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Админ панель</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }

        
        .container {
            max-width: 1400px;
            width: 95%;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        h1 {
            margin-bottom: 20px;
        }

        
        .table-wrapper {
            overflow-x: auto;
        }

        
        table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        th {
            background: #eaeaea;
        }

        td,
        th {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            text-align: left;
        }

        
        .btn {
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            display: inline-block;
        }

        .edit {
            background: #ddd;
            color: black;
        }

        .delete {
            background: #ccc;
            color: black;
        }

        .save {
            background: #bbb;
            color: black;
        }

        input,
        select {
            width: 100%;
            font-size: 13px;
        }
    </style>

</head>

<body>

    <div class="container">

        <h1>Админ панель</h1>

        <div class="table-wrapper">

            <table>

                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата</th>
                    <th>Пол</th>
                    <th>Языки</th>
                    <th>Био</th>
                    <th>Контракт</th>
                    <th>Действия</th>
                </tr>

                <?php foreach ($apps as $app): ?>

                    <?php
                    $stmt = $pdo->prepare("SELECT language_id FROM application_language WHERE application_id=?");
                    $stmt->execute([$app['id']]);
                    $user_langs = array_column($stmt->fetchAll(), 'language_id');
                    ?>

                    <tr>

                        <?php if ($edit_id == $app['id']): ?>

                            <form method="POST">

                                <td>
                                    <?= $app['id'] ?>
                                    <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                </td>

                                <td><input name="fio" value="<?= htmlspecialchars($app['fio']) ?>"></td>
                                <td><input name="phone" value="<?= htmlspecialchars($app['phone']) ?>"></td>
                                <td><input name="email" value="<?= htmlspecialchars($app['email']) ?>"></td>
                                <td><input type="date" name="birthdate" value="<?= $app['birthdate'] ?>"></td>

                                <td>
                                    <select name="gender">
                                        <option value="1" <?= $app['gender'] == 1 ? 'selected' : '' ?>>М</option>
                                        <option value="2" <?= $app['gender'] == 2 ? 'selected' : '' ?>>Ж</option>
                                    </select>
                                </td>

                                <td>
                                    <select name="languages[]" multiple>
                                        <?php foreach ($languages_list as $id => $name): ?>
                                            <option value="<?= $id ?>" <?= in_array($id, $user_langs) ? 'selected' : '' ?>>
                                                <?= $name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td><input name="biography" value="<?= htmlspecialchars($app['biography']) ?>"></td>

                                <td>
                                    <input type="checkbox" name="contract" <?= $app['contract'] ? 'checked' : '' ?>>
                                </td>

                                <td>
                                    <button class="btn save" name="update">Сохранить</button>
                                </td>

                            </form>

                        <?php else: ?>

                            <td><?= $app['id'] ?></td>
                            <td><?= htmlspecialchars($app['fio']) ?></td>
                            <td><?= htmlspecialchars($app['phone']) ?></td>
                            <td><?= htmlspecialchars($app['email']) ?></td>
                            <td><?= $app['birthdate'] ?></td>
                            <td><?= $app['gender'] == 1 ? 'М' : 'Ж' ?></td>

                            <td>
                                <?php
                                $names = [];
                                foreach ($user_langs as $l) {
                                    $names[] = $languages_list[$l] ?? 'Unknown';
                                }
                                echo implode(", ", $names);
                                ?>
                            </td>

                            <td><?= htmlspecialchars($app['biography']) ?></td>
                            <td><?= $app['contract'] ? 'Да' : 'Нет' ?></td>

                            <td>
                                <a class="btn edit" href="?edit=<?= $app['id'] ?>">Редактировать</a>
                                <a class="btn delete" href="?delete=<?= $app['id'] ?>">Удалить</a>
                            </td>

                        <?php endif; ?>

                    </tr>

                <?php endforeach; ?>

            </table>

        </div>

    </div>

</body>

</html>