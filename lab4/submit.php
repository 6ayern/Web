<?php

$errors = [];
$values = [];

$fio = $_POST['fio'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$gender = $_POST['gender'] ?? '';
$biography = $_POST['biography'] ?? '';
$languages = $_POST['languages'] ?? [];
$contract = isset($_POST['contract']);

$values = $_POST;

// 1. Валидация ФИО: только буквы, пробелы, дефисы, до 150 символов
if (empty($fio)) {
    $errors['fio'] = "ФИО обязательно для заполнения";
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s\-]{1,150}$/u", $fio)) {
    $errors['fio'] = "ФИО должно содержать только буквы, пробелы и дефисы (не более 150 символов)";
}

// 2. Валидация телефона: +7XXXXXXXXXX, 8XXXXXXXXXX или XXXXXXXXXX
if (empty($phone)) {
    $errors['phone'] = "Телефон обязателен для заполнения";
} elseif (!preg_match("/^(\+7|8)?\d{10}$/", $phone)) {
    $errors['phone'] = "Телефон должен содержать 10 цифр (формат: 9123456789, 89123456789 или +79123456789)";
}

// 3. Валидация email
if (empty($email)) {
    $errors['email'] = "Email обязателен для заполнения";
} elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email)) {
    $errors['email'] = "Введите корректный email адрес (например: name@domain.com)";
}

// 4. Валидация даты рождения
if (empty($birthdate)) {
    $errors['birthdate'] = "Дата рождения обязательна для заполнения";
} elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $birthdate)) {
    $errors['birthdate'] = "Неверный формат даты";
} else {
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birth)->y;
    if ($age < 18) {
        $errors['birthdate'] = "Возраст должен быть не менее 18 лет";
    } elseif ($age > 120) {
        $errors['birthdate'] = "Проверьте корректность даты рождения";
    }
}

// 5. Валидация пола
if (empty($gender)) {
    $errors['gender'] = "Выберите пол";
} elseif (!in_array($gender, ['1', '2'])) {
    $errors['gender'] = "Недопустимое значение пола";
}

// 6. Валидация языков программирования
if (empty($languages)) {
    $errors['languages'] = "Выберите хотя бы один язык программирования";
} else {
    $valid_languages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    foreach ($languages as $lang) {
        if (!in_array($lang, $valid_languages)) {
            $errors['languages'] = "Выбран недопустимый язык программирования";
            break;
        }
    }
}

// 7. Валидация биографии
if (empty($biography)) {
    $errors['biography'] = "Биография обязательна для заполнения";
} elseif (strlen($biography) > 1000) {
    $errors['biography'] = "Биография не должна превышать 1000 символов";
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9\s.,!?\-()\n\r]{1,1000}$/u", $biography)) {
    $errors['biography'] = "Биография содержит недопустимые символы";
}

// 8. Валидация чекбокса
if (!$contract) {
    $errors['contract'] = "Необходимо ознакомиться с контрактом";
}

// Если есть ошибки - сохраняем в Cookies и перенаправляем
if (!empty($errors)) {
    setcookie("errors", json_encode($errors), 0, "/");
    setcookie("values", json_encode($values), 0, "/");
    header("Location: index.php");
    exit();
}

// Сохраняем в базу данных
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u82377',
        'u82377',
        '4d$TFWRr3'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO application
        (fio, phone, email, birthdate, gender, biography, contract)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $fio,
        $phone,
        $email,
        $birthdate,
        $gender,
        $biography,
        $contract ? 1 : 0
    ]);

    $app_id = $pdo->lastInsertId();

    $stmtLang = $pdo->prepare("
        INSERT INTO application_language
        (application_id, language_id)
        VALUES (?, ?)
    ");

    foreach ($languages as $lang) {
        $stmtLang->execute([$app_id, $lang]);
    }

    $pdo->commit();
    
    // Сохраняем успешные данные в Cookies на 1 год
    $cookie_expire = time() + 365 * 24 * 3600; // 1 год
    
    setcookie("saved_fio", $fio, $cookie_expire, "/");
    setcookie("saved_phone", $phone, $cookie_expire, "/");
    setcookie("saved_email", $email, $cookie_expire, "/");
    setcookie("saved_birthdate", $birthdate, $cookie_expire, "/");
    setcookie("saved_gender", $gender, $cookie_expire, "/");
    setcookie("saved_biography", $biography, $cookie_expire, "/");
    setcookie("saved_languages", json_encode($languages), $cookie_expire, "/");
    setcookie("saved_contract", $contract ? "1" : "", $cookie_expire, "/");
    
    // Очищаем временные Cookies
    setcookie("errors", "", time() - 3600, "/");
    setcookie("values", "", time() - 3600, "/");
    
    // Устанавливаем Cookie успешной отправки
    setcookie("success", "1", 0, "/");
    
    header("Location: index.php");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $errors['database'] = "Ошибка базы данных: " . $e->getMessage();
    setcookie("errors", json_encode($errors), 0, "/");
    setcookie("values", json_encode($values), 0, "/");
    header("Location: index.php");
    exit();
}