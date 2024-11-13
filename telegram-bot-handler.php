<?php
// Подключаем WordPress для работы с базой данных
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );

// Замените токен на ваш собственный
$bot_token = '7787534585:AAEmwNolycwwEYgAHnLC5Lr66Fk7arcYG1s';

// Получаем входящие данные от Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update || !isset($update['message'])) {
    exit();
}

// Извлекаем имя и фамилию пользователя
$chat_id = $update['message']['chat']['id'];
$first_name = $update['message']['from']['first_name'] ?? '';
$last_name = $update['message']['from']['last_name'] ?? '';
$username = $update['message']['from']['username'] ?? '';

// Создаем нового пользователя в WordPress
$user_data = array(
    'user_login'   => $username ?: $first_name,
    'user_pass'    => wp_generate_password(),
    'first_name'   => $first_name,
    'last_name'    => $last_name,
    'user_email'   => $username ? "$username@example.com" : null
);

$user_id = wp_insert_user($user_data);

if (!is_wp_error($user_id)) {
    // Уведомляем пользователя, что он успешно добавлен
    file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=Вы успешно добавлены на сайт!");
} else {
    // Сообщаем об ошибке
    file_get_contents("https://api.telegram.org/bot$bot_token/sendMessage?chat_id=$chat_id&text=Ошибка при добавлении на сайт: " . $user_id->get_error_message());
}
?>