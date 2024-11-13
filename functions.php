<?php
// Enqueue support for selecting a featured image for posts
function my_theme_setup(){
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'my_theme_setup');

// Function to get file version
function get_file_version($file_path) {
    $file = get_template_directory() . $file_path;
    return file_exists($file) ? filemtime($file) : '1.0.0';
}

// Enqueue support for adding SVG files
function rmn_custom_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'rmn_custom_mime_types' );

// Include jQuery built into WordPress
function custom_enqueue_assets() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Connect common styles
    $common_styles = array(
        'reset-style'       => '/css/reset.css',
        'fonts-style'       => '/css/fonts.css',
        'main-style'        => '/style.css',
    );

    foreach ($common_styles as $handle => $path) {
        wp_enqueue_style($handle, get_template_directory_uri() . $path, array(), get_file_version($path));
    }

    // Enqueue UI Kit styles
    $ui_kit_styles = array(
        'typography-kit-style'      => '/css/ui-kit/typography.css',
        'pallete-collors-kit-style' => '/css/ui-kit/pallete-collors.css',
    );

    foreach ($ui_kit_styles as $handle => $path) {
        wp_enqueue_style($handle, get_template_directory_uri() . $path, array(), get_file_version($path));
    }

    // Enqueue home page assets
    if (is_page_template('page-templates/game-sign-up.php')) {
        // Подключаем стили и скрипты для главной страницы
        wp_enqueue_style('home-style', get_template_directory_uri() . '/css/home.css', array(), get_file_version('/css/home.css'));
        // Например, вы можете также подключить JavaScript для главной страницы
        wp_enqueue_script('home-script', get_template_directory_uri() . '/js/home.js', array('jquery'), null, true);
    }

    // Enqueue styles and scripts for 404 error, search, blog
    if (is_404()) {
        wp_enqueue_style('error-404-style', get_template_directory_uri() . '/css/template/error-404.css', array(), get_file_version('/css/template/error-404.css'));
    }
}
add_action('wp_enqueue_scripts', 'custom_enqueue_assets');

// Подключение стилей в админке
function enqueue_admin_styles() {
    // Убедитесь, что вы изменили путь к файлу на правильный
    wp_enqueue_style('custom-admin-styles', get_template_directory_uri() . '/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_styles');

// Функция для создания кастомного типа записи "Игроки"
function create_custom_post_type_players() {
    $labels = array(
        'name'               => 'Игроки',
        'singular_name'      => 'Игрок',
        'menu_name'          => 'Игроки',
        'name_admin_bar'     => 'Игрок',
        'add_new'            => 'Добавить нового',
        'add_new_item'       => 'Добавить нового игрока',
        'new_item'           => 'Новый игрок',
        'edit_item'          => 'Редактировать игрока',
        'view_item'          => 'Просмотреть игрока',
        'all_items'          => 'Все игроки',
        'search_items'       => 'Поиск игроков',
        'not_found'          => 'Игроков не найдено',
        'not_found_in_trash' => 'В корзине игроков не найдено',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'players' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array( 'title' ),
        'menu_icon'          => 'dashicons-groups', // Иконка в меню админки
    );

    register_post_type( 'players', $args );
}
// Хук для инициализации кастомного типа записи
add_action('init', 'create_custom_post_type_players');

// Добавляем метабокс для отображения таблицы с оценками
add_action('add_meta_boxes', 'add_player_skills_metabox');

function add_player_skills_metabox() {
    add_meta_box(
        'player_skills_metabox', // ID метабокса
        'Оценка навыков волейболиста', // Заголовок метабокса
        'render_player_skills_metabox', // Функция для отображения содержимого
        'players', // Тип записи
        'normal', // Место размещения
        'high' // Приоритет
    );
}
function render_player_skills_metabox($post) {
    // Получаем значения полей
    $attack_power = get_post_meta($post->ID, 'attack_power', true);
    $accuracy = get_post_meta($post->ID, 'accuracy', true);
    $blocking = get_post_meta($post->ID, 'blocking', true);
    $jumping = get_post_meta($post->ID, 'jumping', true);
    $defense = get_post_meta($post->ID, 'defense', true);
    $serve = get_post_meta($post->ID, 'serve', true);
    $stamina = get_post_meta($post->ID, 'stamina', true);

    // Начало таблицы
    echo '<table class="form-table">';
    
    // Сила удара
    render_skill_row('attack_power', 'Сила удара', $attack_power);
    
    // Точность
    render_skill_row('accuracy', 'Точность', $accuracy);
    
    // Блокировка
    render_skill_row('blocking', 'Блокировка', $blocking);
    
    // Прыжок
    render_skill_row('jumping', 'Прыжок', $jumping);
    
    // Защита
    render_skill_row('defense', 'Защита', $defense);
    
    // Подача
    render_skill_row('serve', 'Подача', $serve);

    echo '</table>';
}

function render_skill_row($field_id, $label, $value) {
    echo '<tr>';
    echo '<th><label for="' . esc_attr($field_id) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<div class="custom-number-input">';
    echo '<button type="button" class="minus" onclick="updateValue(this, \'' . esc_js($field_id) . '\')">&minus;</button>';
    echo '<input type="number" id="' . esc_attr($field_id) . '" name="' . esc_attr($field_id) . '" value="' . esc_attr($value) . '" min="1" max="10" oninput="drawProgress(this, \'' . esc_js($field_id) . '_canvas\')"/>';
    echo '<button type="button" class="plus" onclick="updateValue(this, \'' . esc_js($field_id) . '\')">&plus;</button>';
    echo '<canvas id="' . esc_attr($field_id) . '_canvas" width="120" height="65"></canvas>';
    echo '</div>';
    echo '</td></tr>';

    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var input = document.getElementById("' . esc_attr($field_id) . '");
            drawProgress(input, "' . esc_js($field_id) . '_canvas");
        });
    </script>';
}

// Сохранение данных метабокса
function save_player_skills_meta($post_id) {
    // Проверяем, есть ли данные для сохранения
    if (isset($_POST['attack_power'])) {
        update_post_meta($post_id, 'attack_power', intval($_POST['attack_power']));
    }
    if (isset($_POST['accuracy'])) {
        update_post_meta($post_id, 'accuracy', intval($_POST['accuracy']));
    }
    if (isset($_POST['blocking'])) {
        update_post_meta($post_id, 'blocking', intval($_POST['blocking']));
    }
    if (isset($_POST['jumping'])) {
        update_post_meta($post_id, 'jumping', intval($_POST['jumping']));
    }
    if (isset($_POST['defense'])) {
        update_post_meta($post_id, 'defense', intval($_POST['defense']));
    }
    if (isset($_POST['serve'])) {
        update_post_meta($post_id, 'serve', intval($_POST['serve']));
    }
    if (isset($_POST['stamina'])) {
        update_post_meta($post_id, 'stamina', intval($_POST['stamina']));
    }
}

// Хук для сохранения метаданных при обновлении записи
add_action('save_post', 'save_player_skills_meta');

add_action('admin_footer', 'add_custom_js');

function add_custom_js() {
    ?>
    <script>
        function drawProgress(input, canvasId) {
            var canvas = document.getElementById(canvasId);
            var ctx = canvas.getContext('2d');
            var value = parseInt(input.value);
            var maxValue = parseInt(input.max);

            // Очищаем canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Параметры центра и радиуса
            var centerX = 60; // Половина ширины
            var centerY = 65; // Чуть ниже середины высоты
            var radius = 60; // Радиус

            // Угол для заполнения (полукруг)
            var angle = (value / maxValue) * Math.PI;

            // Цвет в зависимости от значения
            ctx.fillStyle = getColor(value, maxValue);

            // Рисуем полукруг
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, Math.PI, Math.PI + angle, false); // Используем новые координаты
            ctx.lineTo(centerX, centerY); // Снизу
            ctx.fill();
            ctx.closePath();

            // Рисуем окружность
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, false);
            ctx.strokeStyle = '#ddd'; // Цвет окружности
            ctx.lineWidth = 2;
            ctx.stroke();
            ctx.closePath();
        }


        function getColor(value, maxValue) {
            // Меняем цвет в зависимости от значения
            var ratio = value / maxValue;
            var red = Math.floor((1 - ratio) * 255);
            var green = Math.floor(ratio * 255);
            return 'rgb(' + red + ', ' + green + ', 0)'; // Градиент от красного к зеленому
        }

        function updateValue(button, fieldId) {
            const input = button.parentElement.querySelector('input[type="number"]');
            let value = parseInt(input.value);
            
            if (button.classList.contains('plus')) {
                value++;
            } else if (button.classList.contains('minus')) {
                value--;
            }

            // Ограничиваем значение от 1 до 10
            if (value < 1) value = 1;
            if (value > 10) value = 10;

            input.value = value;

            // Обновляем Canvas
            drawProgress(input, fieldId + '_canvas');
        }
    </script>
    <?php
}

function distribute_players($players) {
    // Создаем массив команд
    $teams = array(
        'Team A' => array('players' => array(), 'total_rating' => 0),
        'Team B' => array('players' => array(), 'total_rating' => 0),
        'Team C' => array('players' => array(), 'total_rating' => 0),
    );

    // Массив для отслеживания предпочтений
    $preferences = array();

    // Сначала сортируем игроков по их общему рейтингу
    usort($players, function($a, $b) {
        $a_rating = intval(get_post_meta($a->ID, 'blocking', true)) +
                    intval(get_post_meta($a->ID, 'defense', true)) +
                    intval(get_post_meta($a->ID, 'serve', true)) +
                    intval(get_post_meta($a->ID, 'attack_power', true)) +
                    intval(get_post_meta($a->ID, 'jumping', true)) +
                    intval(get_post_meta($a->ID, 'accuracy', true));

        $b_rating = intval(get_post_meta($b->ID, 'blocking', true)) +
                    intval(get_post_meta($b->ID, 'defense', true)) +
                    intval(get_post_meta($b->ID, 'serve', true)) +
                    intval(get_post_meta($b->ID, 'attack_power', true)) +
                    intval(get_post_meta($b->ID, 'jumping', true)) +
                    intval(get_post_meta($b->ID, 'accuracy', true));

        return $b_rating - $a_rating; // Сортируем по убыванию
    });

    // Обрабатываем предпочтения
    foreach ($players as $player) {
        $preferred_player_id = get_post_meta($player->ID, 'preferred_player', true); // Получаем предпочтение

        if ($preferred_player_id) {
            $preferences[$player->ID] = intval($preferred_player_id); // Сохраняем предпочтение
        }
    }

    // Распределяем игроков по командам
    $assigned_players = array(); // Массив для отслеживания назначенных игроков

    foreach ($players as $player) {
        if (in_array($player->ID, $assigned_players)) {
            continue; // Пропускаем уже назначенных игроков
        }

        // Получаем общую оценку игрока
        $player_rating = intval(get_post_meta($player->ID, 'blocking', true)) +
                         intval(get_post_meta($player->ID, 'defense', true)) +
                         intval(get_post_meta($player->ID, 'serve', true)) +
                         intval(get_post_meta($player->ID, 'attack_power', true)) +
                         intval(get_post_meta($player->ID, 'jumping', true)) +
                         intval(get_post_meta($player->ID, 'accuracy', true));

        // Ищем команду с наименьшей общей оценкой
        $selected_team = array_reduce(array_keys($teams), function($carry, $team_name) use ($teams, $player_rating) {
            if ($carry === null || $teams[$carry]['total_rating'] > $teams[$team_name]['total_rating'] + $player_rating) {
                return $team_name;
            }
            return $carry;
        });

        // Добавляем ID игрока в выбранную команду
        $teams[$selected_team]['players'][] = $player->ID;
        $teams[$selected_team]['total_rating'] += $player_rating;
        $assigned_players[] = $player->ID; // Отмечаем игрока как назначенного

        // Проверяем, есть ли игрок, который хочет быть с этим игроком
        if (isset($preferences[$player->ID])) {
            $preferred_player_id = $preferences[$player->ID];

            // Проверяем, что предпочтительный игрок еще не назначен
            if (!in_array($preferred_player_id, $assigned_players)) {
                // Добавляем предпочтительного игрока в ту же команду
                $preferred_player_rating = intval(get_post_meta($preferred_player_id, 'blocking', true)) +
                                           intval(get_post_meta($preferred_player_id, 'defense', true)) +
                                           intval(get_post_meta($preferred_player_id, 'serve', true)) +
                                           intval(get_post_meta($preferred_player_id, 'attack_power', true)) +
                                           intval(get_post_meta($preferred_player_id, 'jumping', true)) +
                                           intval(get_post_meta($preferred_player_id, 'accuracy', true));

                $teams[$selected_team]['players'][] = $preferred_player_id;
                $teams[$selected_team]['total_rating'] += $preferred_player_rating;
                $assigned_players[] = $preferred_player_id; // Отмечаем предпочтительного игрока как назначенного
            }
        }
    }

    return $teams; // Возвращаем распределенные команды
}

// Проверка, играл ли игрок в субботу
function check_player_participation($player_id) {
    $current_time = current_time('timestamp');
    $saturday_time = strtotime('this Saturday 18:00');

    // Если текущий момент позже субботы 18:00
    if ($current_time > $saturday_time) {
        $played = get_post_meta($player_id, 'played_saturday', true);
        return $played === 'yes';
    }

    return false;
}

// Хук для установки метки об участии игрока в субботу
function mark_player_participation() {
    $current_time = current_time('timestamp');
    $saturday_time = strtotime('this Saturday 18:00');

    if ($current_time > $saturday_time) {
        $args = array(
            'post_type' => 'players',
            'posts_per_page' => -1,
        );
        $players = get_posts($args);

        foreach ($players as $player) {
            update_post_meta($player->ID, 'played_saturday', 'yes');
        }
    }
}
add_action('wp_loaded', 'mark_player_participation');

// Функция для сброса зарегистрированных игроков и составов
function reset_registered_players_and_teams() {
    // Очистка массива зарегистрированных игроков
    update_option('registered_players', array());
    // Удаление текущих составов
    delete_option('current_teams');
}

function schedule_reset() {
    if (!wp_next_scheduled('weekly_player_reset')) {
        wp_schedule_event(strtotime('next Sunday 10:00'), 'weekly', 'weekly_player_reset');
    }
}
add_action('wp', 'schedule_reset');


// Подключение функции сброса к хуку
add_action('weekly_player_reset', 'reset_registered_players_and_teams');

function add_player_to_registration($player_id) {
    $registered_players = get_option('registered_players', array());
    if (!in_array($player_id, $registered_players)) {
        $registered_players[] = $player_id;
        update_option('registered_players', $registered_players);
    }
}

function get_registered_players_ids() {
    
    $args = array(
        'post_type' => 'registration', // Замените на ваш пост-тип регистрации
        'posts_per_page' => -1,
    );
    $registrations = get_posts($args);
    
    $registered_ids = array();
    foreach ($registrations as $registration) {
        $registered_ids[] = get_post_meta($registration->ID, 'player_id', true); // Предполагаем, что ID игрока сохраняется в метаполе
    }
    
    return $registered_ids;
}
?>