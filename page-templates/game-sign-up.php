<?php
/*
Template Name: game-sign-up
*/
get_header(); 

// Проверяем, была ли отправлена форма для регистрации игрока
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['player_id'])) {
    $player_id = intval($_POST['player_id']);
    $preferred_player = !empty($_POST['preferred_player']) ? intval($_POST['preferred_player']) : null;

    // Сохраняем предпочтение как метаполе
    update_post_meta($player_id, 'preferred_player', $preferred_player);
    add_player_to_registration($player_id); // Добавляем игрока в регистрацию
}

// Проверяем, была ли отправлена форма для выписки игрока
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_player'])) {
    $player_to_remove = intval($_POST['player_to_remove']);
    $registered_players = get_option('registered_players', array());
    $registered_players = array_diff($registered_players, [$player_to_remove]);
    update_option('registered_players', $registered_players);
}

// Проверяем, была ли отправлена форма для создания составов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distribute_players'])) {
    // Получаем зарегистрированных игроков
    $registered_players_ids = get_option('registered_players', array()); 
    $players = array_map('get_post', $registered_players_ids);

    // Распределяем игроков по командам с учётом предпочтений
    $teams = distribute_players($players);

    // Сохраняем составы в опции
    update_option('current_teams', $teams);
}

$current_teams = get_option('current_teams', array());
$registered_players = get_option('registered_players', array());
$player_ratings = array();

if (!empty($registered_players)) {
    foreach ($registered_players as $player_id) {
        $attack_power = intval(get_post_meta($player_id, 'attack_power', true));
        $accuracy = intval(get_post_meta($player_id, 'accuracy', true));
        $blocking = intval(get_post_meta($player_id, 'blocking', true));
        $jumping = intval(get_post_meta($player_id, 'jumping', true));
        $defense = intval(get_post_meta($player_id, 'defense', true));
        $serve = intval(get_post_meta($player_id, 'serve', true));

        $total_rating = $attack_power + $accuracy + $blocking + $jumping + $defense + $serve;
        $average_rating = $total_rating / 6;
        $player_ratings[$player_id] = round($average_rating, 2);
        update_post_meta($player_id, 'average_rating', $average_rating);
    }
}

function calculate_team_average_rating($team, $player_ratings) {
    if (!is_array($team) || !isset($team['players']) || !is_array($team['players'])) {
        return 0;
    }

    $total_rating = 0;
    $player_count = 0;

    foreach ($team['players'] as $player_id) {
        if (isset($player_ratings[$player_id])) {
            $total_rating += $player_ratings[$player_id];
            $player_count++;
        }
    }

    return $player_count > 0 ? round($total_rating / $player_count, 2) : 0;
}

?>

<div class="container">
    <div class="teams-display_block">
        <h2 class="title-largest"><?php echo !empty($teams) ? 'Созданные составы' : 'Текущие составы'; ?></h2>
        <?php if (!empty($teams)): ?>
            <?php foreach ($teams as $team_name => $team): ?>
                <ul>
                    <?php
                    $team_average_rating = calculate_team_average_rating($team, $player_ratings);
                    ?>
                    <div>
                        <h3 class="title-larger"><?php echo esc_html($team_name); ?></h3>
                        <div class="team-rating">
                            <canvas id="<?php echo esc_attr($team_name); ?>_canvas" width="80" height="40"></canvas>
                            <span class="rating-display body-small-semibold"><?php echo esc_html($team_average_rating); ?></span>
                        </div>
                    </div>
                    <?php foreach ($team['players'] as $player): ?>
                        <li class="body-medium-regular"><?php echo esc_html(get_the_title($player)); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>

        <?php elseif (!empty($current_teams)): ?>
            <?php foreach ($current_teams as $team_name => $team): ?>
                <ul>
                    <?php
                    $team_average_rating = calculate_team_average_rating($team, $player_ratings);
                    ?>
                    <div>
                        <h3 class="title-larger"><?php echo esc_html($team_name); ?></h3>
                        <div class="team-rating">
                            <canvas id="<?php echo esc_attr($team_name); ?>_canvas" width="80" height="40"></canvas>
                            <span class="rating-display body-small-semibold"><?php echo esc_html($team_average_rating); ?></span>
                        </div>
                    </div>
                    <?php foreach ($team['players'] as $player): ?>
                        <li class="body-medium-regular"><?php echo esc_html(get_the_title($player)); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="registered-cancellation_block">
        <h2 class="title-largest">Зарегистрированные игроки</h2>
        <div class="player-groups">
            <?php if (empty($registered_players)): ?>
                <p>Нет зарегистрированных игроков.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($registered_players as $player_id): ?>
                        <?php $player = get_post($player_id); ?>
                        <?php if ($player): ?>
                            <li class="body-medium-regular">
                                <?php
                                $played = check_player_participation($player->ID) ? ' (Играл в субботу)' : '';
                                echo esc_html(get_the_title($player)) . $played;

                                $average_rating = isset($player_ratings[$player_id]) ? $player_ratings[$player_id] : 0;
                                ?>
                            </li>
                            <div class="team-rating">
                                <canvas id="<?php echo esc_attr($player->ID); ?>_canvas" width="80" height="40"></canvas>
                                <span id="<?php echo esc_attr($player->ID); ?>_rating" class="rating-display body-small-semibold"><?php echo esc_html($average_rating); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="sign-up_block">
            <?php
            $args = array(
                'post_type' => 'players',
                'posts_per_page' => -1,
            );
            $players = get_posts($args);
            ?>

            <?php if ($players): ?>
                <h3 class="title-large">Записаться на игру</h3>
                <form method="post">
                    <select name="player_id">
                        <?php foreach ($players as $player): ?>
                            <?php if (!in_array($player->ID, $registered_players)): ?>
                                <option value="<?php echo esc_attr($player->ID); ?>">
                                    <?php echo esc_html(get_the_title($player)); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <label for="preferred_player">Играть с:</label>
                    <select name="preferred_player">
                        <option value="">Без предпочтений</option>
                        <?php foreach ($players as $player): ?>
                            <option value="<?php echo esc_attr($player->ID); ?>">
                                <?php echo esc_html(get_the_title($player)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button-medium" type="submit">Записаться</button>
                </form>
            <?php else: ?>
                <p>Нет доступных игроков для записи.</p>
            <?php endif; ?>

            <h3 class="title-large">Создать составы</h3>
            <form method="post">
                <button class="button-medium" type="submit" name="distribute_players">Создать составы</button>
            </form>

            <h3 class="title-large">Выписать игрока</h3>
            <form method="post">
                <select name="player_to_remove">
                    <?php foreach ($registered_players as $player_id): ?>
                        <option value="<?php echo esc_attr($player_id); ?>">
                            <?php echo esc_html(get_the_title($player_id)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button-medium" type="submit" name="remove_player">Выписать игрока</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Обновляем canvas для каждого созданного состава
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($teams)): ?>
            <?php foreach ($teams as $team_name => $team): ?>
                var average_rating = <?php echo esc_js(calculate_team_average_rating($team, $player_ratings)); ?>;
                drawProgress(average_rating, '<?php echo esc_attr($team_name); ?>_canvas');
            <?php endforeach; ?>
        <?php elseif (!empty($current_teams)): ?>
            <?php foreach ($current_teams as $team_name => $team): ?>
                var average_rating = <?php echo esc_js(calculate_team_average_rating($team, $player_ratings)); ?>;
                drawProgress(average_rating, '<?php echo esc_attr($team_name); ?>_canvas');
            <?php endforeach; ?>
        <?php endif; ?>
    });
    // Обновляем canvas для каждого зарегистрированного игрока
    document.addEventListener('DOMContentLoaded', function () {
        <?php foreach ($registered_players as $player_id): ?>
            var average_rating = <?php echo esc_js(isset($player_ratings[$player_id]) ? $player_ratings[$player_id] : 0); ?>;
            drawProgress(average_rating, '<?php echo esc_attr($player_id); ?>_canvas');
        <?php endforeach; ?>
    });
</script>

<?php get_footer(); ?>