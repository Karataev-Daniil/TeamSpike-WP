<?php
get_header(); ?>

<section id="main-content">
    <div class="container">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                // Получаем статистику игрока
                $attack_power = intval(get_post_meta(get_the_ID(), 'attack_power', true));
                $accuracy = intval(get_post_meta(get_the_ID(), 'accuracy', true));
                $blocking = intval(get_post_meta(get_the_ID(), 'blocking', true));
                $jumping = intval(get_post_meta(get_the_ID(), 'jumping', true));
                $defense = intval(get_post_meta(get_the_ID(), 'defense', true));
                $serve = intval(get_post_meta(get_the_ID(), 'serve', true));

                // Вычисляем среднюю оценку
                $total_stats = $attack_power + $accuracy + $blocking + $jumping + $defense + $serve;
                $num_stats = 6; // Количество статистик
                $average_score = $num_stats > 0 ? round($total_stats / $num_stats, 2) : 0; // Округляем до двух знаков после запятой
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title">
                            <?php the_title(); ?>
                            <span class="average-score">(Средняя оценка: <?php echo esc_html($average_score); ?>)</span>
                        </h1>
                    </header>

                    <div class="player-stats">
                        <h2>Статистика игрока</h2>
                        <ul>
                            <li>Сила удара: <?php echo esc_html($attack_power); ?></li>
                            <li>Точность: <?php echo esc_html($accuracy); ?></li>
                            <li>Блокировка: <?php echo esc_html($blocking); ?></li>
                            <li>Прыжок: <?php echo esc_html($jumping); ?></li>
                            <li>Защита: <?php echo esc_html($defense); ?></li>
                            <li>Подача: <?php echo esc_html($serve); ?></li>
                        </ul>
                    </div>

                    <?php if (check_player_participation(get_the_ID())) : ?>
                        <p class="played-saturday">Играл в субботу</p>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    // Получаем игры, в которых участвовал игрок
                    $games = get_post_meta(get_the_ID(), 'participated_games', true); // Предполагается, что это массив с ID игр
                    if (!empty($games) && is_array($games)): ?>
                        <div class="participation-history">
                            <h2>История участия в играх</h2>
                            <ul>
                                <?php foreach ($games as $game_id): ?>
                                    <li>
                                        <?php
                                        $game = get_post($game_id);
                                        if ($game) {
                                            echo esc_html(get_the_title($game)); // Название игры
                                            echo ' (Участвовал до 18:00 субботы)';
                                        }
                                        ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </article>
            <?php
            endwhile;
        else :
            echo '<p>Игроков не найдено.</p>';
        endif;
        ?>
    </div>
</section>

<?php get_footer(); ?>
