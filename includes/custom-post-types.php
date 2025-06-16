<?php
function create_custom_post_type_players() {
    $labels = array(
        'name'               => 'Players',
        'singular_name'      => 'Player',
        'menu_name'          => 'Players',
        'name_admin_bar'     => 'Player',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Player',
        'new_item'           => 'New Player',
        'edit_item'          => 'Edit Player',
        'view_item'          => 'View Player',
        'all_items'          => 'All Players',
        'search_items'       => 'Search Players',
        'not_found'          => 'No players found',
        'not_found_in_trash' => 'No players found in Trash',
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
        'menu_icon'          => 'dashicons-groups',
    );

    register_post_type( 'players', $args );
}
add_action('init', 'create_custom_post_type_players');

add_filter('manage_players_posts_columns', function($columns) {
    $new_columns = [];
    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'title') {
            $new_columns['rating_teamplay'] = 'Командная игра';
            $new_columns['rating_mechanics'] = 'Механики';
            $new_columns['rating_stability'] = 'Стабильность';
            $new_columns['overall_rating'] = 'Общая оценка';
        }
    }
    return $new_columns;
});

add_action('manage_players_posts_custom_column', function($column, $post_id) {
    $criteria_keys = [
        'rating_teamplay' => 0,
        'rating_mechanics' => 1,
        'rating_stability' => 2,
    ];

    $ratings = get_post_meta($post_id, 'player_ratings', true);

    if (!is_array($ratings)) {
        if (in_array($column, array_keys($criteria_keys)) || $column === 'overall_rating') {
            echo '—';
        }
        return;
    }

    $sums = [0, 0, 0];
    $counts = [0, 0, 0];

    foreach ($ratings as $raterScores) {
        foreach ($raterScores as $index => $score) {
            $score = (int)$score;
            if ($score > 0) {
                $sums[$index] += $score;
                $counts[$index]++;
            }
        }
    }

    if (array_key_exists($column, $criteria_keys)) {
        $i = $criteria_keys[$column];
        $avg = ($counts[$i] > 0) ? round($sums[$i] / $counts[$i], 2) : 0;

        echo '<canvas class="admin-gauge" width="50" height="75" data-value="' . esc_attr($avg) . '"></canvas><br>';
    }

    if ($column === 'overall_rating') {
        $totalSum = array_sum($sums);
        $totalCount = array_sum($counts);
        $overall = $totalCount > 0 ? round($totalSum / $totalCount, 2) : 0;

        echo '<canvas class="admin-gauge" width="75" height="75" data-value="' . esc_attr($overall) . '"></canvas><br>';
    }
}, 10, 2);

add_action('admin_head-edit.php', function() {
    $screen = get_current_screen();
    if ($screen->post_type !== 'players') return;

    echo '<style>
        .column-rating_teamplay,
        .column-rating_mechanics,
        .column-rating_stability,
        .column-overall_rating {
            padding: 8px 12px;
            vertical-align: middle;
            text-align: center;
        }
        .column-rating_teamplay,
        .column-rating_mechanics,
        .column-rating_stability {
            width: 110px;
        }
        .column-overall_rating {
            width: 100px;
            font-weight: 600;
        }
    </style>';
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'players') {
        wp_enqueue_script(
            'admin-gauge-js',
            get_template_directory_uri() . '/js/gauge.js',
            [],
            null,
            true
        );
    }
});

add_action('admin_enqueue_scripts', function($hook) {
    $post_type = $_GET['post_type'] ?? get_post_type();
    if (in_array($hook, ['edit.php', 'post.php']) && $post_type === 'players') {
        wp_enqueue_script(
            'admin-gauge-js',
            get_template_directory_uri() . '/js/gauge.js',
            [],
            null,
            true
        );
    }
});

function add_player_rating_meta_box() {
    add_meta_box(
        'player_ratings',
        'Оценки от главных игроков',
        'render_player_rating_meta_box',
        'players',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_player_rating_meta_box');

function render_player_rating_meta_box($post) {
    $crit_names = ['Командная игра', 'Механики', 'Стабильность'];
    $raters = ['RoYm1ne', 'ApparentlyBilly', 'RelaXe', 'KayoVa KuroSaVa'];

    $ratings = get_post_meta($post->ID, 'player_ratings', true);
    if (!is_array($ratings)) $ratings = [];

    $sums = [0, 0, 0];
    $counts = [0, 0, 0];

    echo '<table class="widefat" style="margin-bottom:20px;"><thead><tr><th>Игрок</th>';
    foreach ($crit_names as $crit) {
        echo "<th>$crit</th>";
    }
    echo '</tr></thead><tbody>';

    foreach ($raters as $rater) {
        echo "<tr><td><strong>$rater</strong></td>";
        foreach ($crit_names as $index => $crit) {
            $value = isset($ratings[$rater][$index]) ? (int)$ratings[$rater][$index] : '';
            echo '<td><input type="number" name="player_ratings[' . esc_attr($rater) . '][' . $index . ']" value="' . esc_attr($value) . '" min="0" max="10" step="1" style="width:60px;"></td>';

            if ($value !== '' && $value > 0) {
                $sums[$index] += $value;
                $counts[$index]++;
            }
        }
        echo '</tr>';
    }

    echo '</tbody></table>';

    echo '<h4>Средние оценки:</h4><div style="display:flex; gap:30px;">';
    foreach ($crit_names as $i => $crit) {
        $avg = $counts[$i] > 0 ? round($sums[$i] / $counts[$i], 2) : 0;
        echo "<div style='text-align:center;'>
                <canvas class='admin-gauge' id='admin-gauge-$i' width='150' height='150' data-value='$avg'></canvas>
                <div><strong>$crit:</strong> $avg</div>
              </div>";
    }
    echo '</div>';
    
    $totalSum = array_sum($sums);
    $totalCount = array_sum($counts);
    $overallAvg = $totalCount > 0 ? round($totalSum / $totalCount, 2) : 0;

    echo '<h4 style="margin-top: 30px;">Общая средняя оценка:</h4>';
    echo "<div style='text-align:center;'>
            <canvas class='admin-gauge' id='admin-gauge-overall' width='200' height='200' data-value='$overallAvg'></canvas>
          </div>";

    wp_nonce_field('save_player_ratings', 'player_ratings_nonce');
}

add_action('save_post', 'save_player_rating_meta_box');
function save_player_rating_meta_box($post_id) {
    if (!isset($_POST['player_ratings_nonce']) || !wp_verify_nonce($_POST['player_ratings_nonce'], 'save_player_ratings')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['player_ratings']) && is_array($_POST['player_ratings'])) {
        $cleaned = [];
        foreach ($_POST['player_ratings'] as $rater => $scores) {
            foreach ($scores as $index => $score) {
                $cleaned[$rater][$index] = max(0, min(10, (int)$score));
            }
        }
        update_post_meta($post_id, 'player_ratings', $cleaned);
    }
}