<?php
    $errors = [];
    $values = [];
    $success = false;

    if (isset($_COOKIE["errors"])) {
        $errors = json_decode($_COOKIE["errors"], true);
        setcookie("errors", "", time() - 3600);
    }

    if (isset($_COOKIE["values"])) {
        $values = json_decode($_COOKIE["values"], true);
        setcookie("values", "", time() - 3600);
    }

    if (isset($_COOKIE["success"])) {
        $success = true;
        setcookie("success", "", time() - 3600);
    }
    
    // Загружаем сохранённые данные из Cookies (на 1 год)
    $saved_values = [];
    $saved_fields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'biography'];
    foreach ($saved_fields as $field) {
        if (isset($_COOKIE["saved_$field"])) {
            $saved_values[$field] = $_COOKIE["saved_$field"];
        }
    }
    
    if (isset($_COOKIE["saved_languages"])) {
        $saved_values['languages'] = json_decode($_COOKIE["saved_languages"], true);
    }
    
    if (isset($_COOKIE["saved_contract"])) {
        $saved_values['contract'] = $_COOKIE["saved_contract"];
    }
    
    // Если нет временных данных из ошибок, используем сохранённые
    if (empty($values)) {
        $values = $saved_values;
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Форма заявки</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .error {
            border: 2px solid red !important;
        }

        .error-text {
            color: red;
            font-size: 13px;
            margin-top: 5px;
            display: block;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        select {
            height: 140px;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
            cursor: pointer;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #4e73df;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        button:hover {
            background: #2e59d9;
        }
        
        .error-summary {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .error-summary h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .error-summary ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>

    <form action="submit.php" method="POST">

        <h2>Форма заявки</h2>

        <?php if ($success): ?>
            <div class="success">
                Заявка успешно отправлена ✅
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-summary">
                <h3>Пожалуйста, исправьте следующие ошибки:</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>ФИО:</label>
            <input
                type="text"
                name="fio"
                value="<?= htmlspecialchars($values['fio'] ?? '') ?>"
                class="<?= isset($errors['fio']) ? 'error' : '' ?>"
            >
            <?php if (isset($errors['fio'])): ?>
                <div class="error-text"><?= $errors['fio'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Телефон:</label>
            <input
                type="tel"
                name="phone"
                value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                class="<?= isset($errors['phone']) ? 'error' : '' ?>"
                placeholder="+79123456789 или 89123456789"
            >
            <?php if (isset($errors['phone'])): ?>
                <div class="error-text"><?= $errors['phone'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                class="<?= isset($errors['email']) ? 'error' : '' ?>"
            >
            <?php if (isset($errors['email'])): ?>
                <div class="error-text"><?= $errors['email'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Дата рождения:</label>
            <input
                type="date"
                name="birthdate"
                value="<?= htmlspecialchars($values['birthdate'] ?? '') ?>"
                class="<?= isset($errors['birthdate']) ? 'error' : '' ?>"
            >
            <?php if (isset($errors['birthdate'])): ?>
                <div class="error-text"><?= $errors['birthdate'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Пол:</label>
            <div class="radio-group">
                <label>
                    <input
                        type="radio"
                        name="gender"
                        value="1"
                        <?= (isset($values['gender']) && $values['gender'] == '1') ? 'checked' : '' ?>
                    >
                    Мужской
                </label>
                <label>
                    <input
                        type="radio"
                        name="gender"
                        value="2"
                        <?= (isset($values['gender']) && $values['gender'] == '2') ? 'checked' : '' ?>
                    >
                    Женский
                </label>
            </div>
            <?php if (isset($errors['gender'])): ?>
                <div class="error-text"><?= $errors['gender'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Любимые языки программирования:</label>
            <select
                name="languages[]"
                multiple
                class="<?= isset($errors['languages']) ? 'error' : '' ?>"
            >
                <?php
                    $languages = [
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

                    foreach ($languages as $id => $name):
                        $selected = (
                            isset($values['languages']) &&
                            in_array($id, $values['languages'])
                        ) ? "selected" : "";
                ?>
                    <option value="<?= $id ?>" <?= $selected ?>>
                        <?= $name ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['languages'])): ?>
                <div class="error-text"><?= $errors['languages'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Биография:</label>
            <textarea
                name="biography"
                class="<?= isset($errors['biography']) ? 'error' : '' ?>"
            ><?= htmlspecialchars($values['biography'] ?? '') ?></textarea>
            <?php if (isset($errors['biography'])): ?>
                <div class="error-text"><?= $errors['biography'] ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>
                <input
                    type="checkbox"
                    name="contract"
                    value="1"
                    <?= isset($values['contract']) && $values['contract'] == '1' ? "checked" : "" ?>
                    class="<?= isset($errors['contract']) ? 'error' : '' ?>"
                >
                С контрактом ознакомлен
            </label>
            <?php if (isset($errors['contract'])): ?>
                <div class="error-text"><?= $errors['contract'] ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">
            Сохранить
        </button>

    </form>

</body>
</html>