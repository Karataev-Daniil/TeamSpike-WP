<?php
get_header(); ?>

<section id="main-content">
    <div class="container">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                // Get player statistics
                $attack_power = intval(get_post_meta(get_the_ID(), 'attack_power', true));
                $accuracy = intval(get_post_meta(get_the_ID(), 'accuracy', true));
                $blocking = intval(get_post_meta(get_the_ID(), 'blocking', true));
                $jumping = intval(get_post_meta(get_the_ID(), 'jumping', true));
                $defense = intval(get_post_meta(get_the_ID(), 'defense', true));
                $serve = intval(get_post_meta(get_the_ID(), 'serve', true));

                // Calculate the average score
                $total_stats = $attack_power + $accuracy + $blocking + $jumping + $defense + $serve;
                $num_stats = 6; // Number of statistics
                $average_score = $num_stats > 0 ? round($total_stats / $num_stats, 2) : 0; // Round to two decimal places
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <h1 class="entry-title">
                            <?php the_title(); ?>
                            <span class="average-score">(Average Score: <?php echo esc_html($average_score); ?>)</span>
                        </h1>
                    </header>

                    <div class="player-stats">
                        <h2>Player Statistics</h2>
                        <ul>
                            <li>Attack Power: <?php echo esc_html($attack_power); ?></li>
                            <li>Accuracy: <?php echo esc_html($accuracy); ?></li>
                            <li>Blocking: <?php echo esc_html($blocking); ?></li>
                            <li>Jumping: <?php echo esc_html($jumping); ?></li>
                            <li>Defense: <?php echo esc_html($defense); ?></li>
                            <li>Serve: <?php echo esc_html($serve); ?></li>
                        </ul>
                    </div>

                    <?php if (check_player_participation(get_the_ID())) : ?>
                        <p class="played-saturday">Played on Saturday</p>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    // Get the games the player participated in
                    $games = get_post_meta(get_the_ID(), 'participated_games', true); // Assumed to be an array of game IDs
                    if (!empty($games) && is_array($games)): ?>
                        <div class="participation-history">
                            <h2>Game Participation History</h2>
                            <ul>
                                <?php foreach ($games as $game_id): ?>
                                    <li>
                                        <?php
                                        $game = get_post($game_id);
                                        if ($game) {
                                            echo esc_html(get_the_title($game)); // Game title
                                            echo ' (Participated before 18:00 Saturday)';
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
            echo '<p>No players found.</p>';
        endif;
        ?>
    </div>
</section>

<?php get_footer(); ?>