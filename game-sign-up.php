<?php
/*
Template Name: game-sign-up
*/
get_header(); 
$current_teams = get_option('current_teams', array());
$registered_players = get_option('registered_players', array());

$player_ratings = array();

// Include the player registration handling file
include_once(get_template_directory() . '/includes/player-registration.php');

// Include the player ratings calculation file
include_once(get_template_directory() . '/includes/player-ratings.php');
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
                            <span id="<?php echo esc_attr($team_name); ?>_rating" class="rating-display body-small-semibold"><?php echo esc_html($team_average_rating); ?></span>
                        </div>
                    </div>
                    <?php foreach ($team['players'] as $player): ?>
                        <li class="body-medium-regular">
                            <a href="<?php echo esc_url(get_permalink($player)); ?>">
                                <?php echo esc_html(get_the_title($player)); ?>
                            </a>
                        </li>
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
                            <span id="<?php echo esc_attr($team_name); ?>_rating" class="rating-display body-small-semibold"><?php echo esc_html($team_average_rating); ?></span>
                        </div>
                    </div>
                    <?php foreach ($team['players'] as $player): ?>
                        <li class="body-medium-regular">
                            <a href="<?php echo esc_url(get_permalink($player)); ?>">
                                <?php echo esc_html(get_the_title($player)); ?>
                            </a>
                        </li>
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
                                <a href="<?php echo esc_url(get_permalink($player->ID)); ?>">
                                    <?php
                                    $played = check_player_participation($player->ID) ? ' (Играл в субботу)' : '';
                                    echo esc_html(get_the_title($player)) . $played;
                                    ?>
                                </a>
                            </li>
                            <div class="team-rating">
                                <canvas id="<?php echo esc_attr($player->ID); ?>_canvas" width="80" height="40"></canvas>
                                <span id="<?php echo esc_attr($player->ID); ?>_rating" class="rating-display body-small-semibold"><?php echo esc_html($player_ratings[$player_id] ?? 0); ?></span>
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
