<?php
/*
Template Name: game-sign-up
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $registered_players = get_option('registered_players', array());
    $current_teams = get_option('current_teams', array());

    if (isset($_POST['register_player']) && !empty($_POST['player_id'])) {
        $player_id = intval($_POST['player_id']);
        if (!isset($registered_players[$player_id])) {
            $registered_players[$player_id] = true;
            update_option('registered_players', $registered_players);
        }
        wp_redirect(get_permalink());
        exit;
    }

    if (isset($_POST['remove_player']) && !empty($_POST['player_to_remove'])) {
        $player_to_remove = intval($_POST['player_to_remove']);
        if (isset($registered_players[$player_to_remove])) {
            unset($registered_players[$player_to_remove]);
            update_option('registered_players', $registered_players);
        }
        wp_redirect(get_permalink());
        exit;
    }

    if (isset($_POST['distribute_players'])) {
        $mode = $_POST['game_mode'] ?? '2x2';
        $team_size = ($mode === '3x3') ? 3 : 2;

        $registered_players = get_option('registered_players', array());
        $registered_ids = array_keys($registered_players);

        $players_with_ratings = [];
        foreach ($registered_ids as $id) {
            $players_with_ratings[] = [
                'id' => $id,
                'rating' => get_player_average_rating_by_id($id),
            ];
        }

        usort($players_with_ratings, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });

        $new_teams = [];
        $team_number = 1;
        $team_count = ceil(count($players_with_ratings) / $team_size);

        for ($i = 1; $i <= $team_count; $i++) {
            $new_teams["Команда $i"] = ['players' => [], 'rating_sum' => 0];
        }

        foreach ($players_with_ratings as $player) {
            uasort($new_teams, function($a, $b) use ($team_size) {
                if (count($a['players']) === $team_size && count($b['players']) < $team_size) return 1;
                if (count($b['players']) === $team_size && count($a['players']) < $team_size) return -1;
                return $a['rating_sum'] <=> $b['rating_sum'];
            });

            foreach ($new_teams as &$team) {
                if (count($team['players']) < $team_size) {
                    $team['players'][] = $player['id'];
                    $team['rating_sum'] += $player['rating'];
                    break;
                }
            }
            unset($team);
        }

        foreach ($new_teams as &$team) {
            unset($team['rating_sum']);
        }

        update_option('current_teams', $new_teams);
        wp_redirect(get_permalink());
        exit;
    }
}

get_header();

$current_teams = get_option('current_teams', array());
$registered_players = get_option('registered_players', array());

$players = get_posts([
    'post_type' => 'players',
    'numberposts' => -1,
]);

function get_player_average_rating_by_id($player_id) {
    $ratings = get_post_meta($player_id, 'player_ratings', true);
    if (empty($ratings) || !is_array($ratings)) return 0;

    $sum = 0;
    $count = 0;
    foreach ($ratings as $rater_scores) {
        if (!is_array($rater_scores)) continue;
        foreach ($rater_scores as $score) {
            if (is_numeric($score) && $score > 0) {
                $sum += $score;
                $count++;
            }
        }
    }
    return $count > 0 ? round($sum / $count, 2) : 0;
}

function calculate_team_average_rating($team) {
    $players = $team['players'] ?? [];
    if (empty($players)) return 0;

    $sum = 0;
    $count = 0;
    foreach ($players as $player_id) {
        $rating = get_player_average_rating_by_id($player_id);
        $sum += $rating;
        $count++;
    }
    return $count > 0 ? round($sum / $count, 2) : 0;
}
?>

<div class="container-xsmall">
    <div class="teams-display_block">
        <h2 class="title-largest">Текущие составы</h2>
        <?php if (!empty($current_teams)): ?>
            <?php foreach ($current_teams as $team_name => $team): ?>
                <?php $team_avg = calculate_team_average_rating($team); ?>
                <ul>
                    <div>
                        <h3 class="title-larger"><?php echo esc_html($team_name); ?></h3>
                        <div class="team-rating">
                            <canvas
                                class="admin-gauge"
                                width="50"
                                height="50"
                                data-value="<?php echo esc_attr($team_avg); ?>"
                            ></canvas>
                        </div>
                    </div>
                    <?php foreach ($team['players'] as $player_id):
                        $player = get_post($player_id);
                        if (!$player) continue;
                        $player_rating = get_player_average_rating_by_id($player_id);
                    ?>
                        <li>
                            <a class="player-name body-medium-regular" href="<?php echo esc_url(get_permalink($player_id)); ?>">
                                <?php echo esc_html(get_the_title($player_id)); ?>
                            </a>
                            — рейтинг: <?php echo esc_attr($player_rating); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Составы еще не созданы.</p>
        <?php endif; ?>
    </div>

    <div class="registered-cancellation_block">
        <h2 class="title-largest">Зарегистрированные игроки</h2>
        <div class="player-groups">
            <?php if (empty($registered_players)): ?>
                <p>Нет зарегистрированных игроков.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($registered_players as $player_id => $val):
                        $player = get_post($player_id);
                        if (!$player) continue;
                        $player_rating = get_player_average_rating_by_id($player_id);
                    ?>
                        <li>
                            <a class="player-name body-medium-regular" href="<?php echo esc_url(get_permalink($player->ID)); ?>">
                                <?php echo esc_html(get_the_title($player)); ?>
                            </a>
                            — рейтинг:
                            <canvas
                                class="admin-gauge"
                                width="50"
                                height="50"
                                data-value="<?php echo esc_attr($player_rating); ?>"
                            ></canvas>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="sign-up_block">
            <h3 class="title-large">Записаться на игру</h3>
            <form method="post" class="body-small-regular">
                <select name="player_id" class="body-small-regular" required>
                    <?php foreach ($players as $player): ?>
                        <?php if (!isset($registered_players[$player->ID])): ?>
                            <option value="<?php echo esc_attr($player->ID); ?>">
                                <?php echo esc_html(get_the_title($player)); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button class="button-medium" type="submit" name="register_player">Записаться</button>
            </form>

            <h3 class="title-large">Выберите режим игры</h3>
            <form method="post">
                <select name="game_mode">
                    <option value="2x2">2 на 2</option>
                    <option value="3x3">3 на 3</option>
                </select>
                <button class="button-medium" type="submit" name="distribute_players">Создать составы</button>
            </form>

            <h3 class="title-large">Выписать игрока</h3>
            <form method="post">
                <select name="player_to_remove" required>
                    <?php foreach ($registered_players as $player_id => $val): ?>
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

<script src="<?php echo get_template_directory_uri(); ?>/js/gauge.js"></script>

<?php get_footer(); ?>
