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

// Enqueue jQuery and main styles/scripts
function custom_enqueue_assets() {
    // Enqueue jQuery built into WordPress
    wp_enqueue_script('jquery');

    // Enqueue common styles
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

    // Enqueue assets for the home page
    if (is_page_template('page-templates/game-sign-up.php')) {
        wp_enqueue_style('home-style', get_template_directory_uri() . '/css/home.css', array(), get_file_version('/css/home.css'));

        wp_enqueue_script('home-script', get_template_directory_uri() . '/js/home.js', array('jquery'), null, true);
        wp_enqueue_script('progress-bar-script', get_template_directory_uri() . '/js/progress-bar.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'custom_enqueue_assets');

// Enqueue styles in the admin dashboard
function enqueue_admin_styles() {
    // Make sure to update the path to the correct file
    wp_enqueue_style('custom-admin-styles', get_template_directory_uri() . '/admin-styles.css');

    wp_enqueue_script('progress-bar-script', get_template_directory_uri() . '/js/progress-bar.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'enqueue_admin_styles');


// Function to create a custom post type "Players"
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
        'menu_icon'          => 'dashicons-groups', // Icon in the admin menu
    );

    register_post_type( 'players', $args );
}
// Hook to initialize the custom post type
add_action('init', 'create_custom_post_type_players');

function add_player_skills_metabox() {
    add_meta_box(
        'player_skills_metabox',
        'Volleyball Player Skills Rating',
        'render_player_skills_metabox',
        'players',
        'normal',
        'high'
    );
}
// Add the metabox to display the rating table
add_action('add_meta_boxes', 'add_player_skills_metabox');

function render_player_skills_metabox($post) {
    // Get the field values
    $attack_power = get_post_meta($post->ID, 'attack_power', true);
    $accuracy = get_post_meta($post->ID, 'accuracy', true);
    $blocking = get_post_meta($post->ID, 'blocking', true);
    $jumping = get_post_meta($post->ID, 'jumping', true);
    $defense = get_post_meta($post->ID, 'defense', true);
    $serve = get_post_meta($post->ID, 'serve', true);
    $stamina = get_post_meta($post->ID, 'stamina', true);

    // Start of the table
    echo '<table class="form-table">';
    
    // Attack power
    render_skill_row('attack_power', 'Attack Power', $attack_power);
    
    // Accuracy
    render_skill_row('accuracy', 'Accuracy', $accuracy);
    
    // Blocking
    render_skill_row('blocking', 'Blocking', $blocking);
    
    // Jumping
    render_skill_row('jumping', 'Jumping', $jumping);
    
    // Defense
    render_skill_row('defense', 'Defense', $defense);
    
    // Serve
    render_skill_row('serve', 'Serve', $serve);

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

// Saving metabox data
function save_player_skills_meta($post_id) {
    // Check if there is data to save
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
// Hook to save metadata when updating a post
add_action('save_post', 'save_player_skills_meta');

function distribute_players($players) {
    // Create an array of teams
    $teams = array(
        'Team A' => array('players' => array(), 'total_rating' => 0),
        'Team B' => array('players' => array(), 'total_rating' => 0),
        'Team C' => array('players' => array(), 'total_rating' => 0),
    );

    // Array to track preferences
    $preferences = array();

    // First, sort players by their total rating
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

        return $b_rating - $a_rating; // Sort by descending order
    });

    // Handle preferences
    foreach ($players as $player) {
        $preferred_player_id = get_post_meta($player->ID, 'preferred_player', true); // Get preference

        if ($preferred_player_id) {
            $preferences[$player->ID] = intval($preferred_player_id); // Save the preference
        }
    }

    // Distribute players into teams
    $assigned_players = array(); // Array to track assigned players

    foreach ($players as $player) {
        if (in_array($player->ID, $assigned_players)) {
            continue; // Skip already assigned players
        }

        // Get the player's total rating
        $player_rating = intval(get_post_meta($player->ID, 'blocking', true)) +
                         intval(get_post_meta($player->ID, 'defense', true)) +
                         intval(get_post_meta($player->ID, 'serve', true)) +
                         intval(get_post_meta($player->ID, 'attack_power', true)) +
                         intval(get_post_meta($player->ID, 'jumping', true)) +
                         intval(get_post_meta($player->ID, 'accuracy', true));

        // Find the team with the smallest total rating
        $selected_team = array_reduce(array_keys($teams), function($carry, $team_name) use ($teams, $player_rating) {
            if ($carry === null || $teams[$carry]['total_rating'] > $teams[$team_name]['total_rating'] + $player_rating) {
                return $team_name;
            }
            return $carry;
        });

        // Add player ID to the selected team
        $teams[$selected_team]['players'][] = $player->ID;
        $teams[$selected_team]['total_rating'] += $player_rating;
        $assigned_players[] = $player->ID; // Mark the player as assigned

        // Check if there's a player who wants to be with this player
        if (isset($preferences[$player->ID])) {
            $preferred_player_id = $preferences[$player->ID];

            // Check if the preferred player is not yet assigned
            if (!in_array($preferred_player_id, $assigned_players)) {
                // Add the preferred player to the same team
                $preferred_player_rating = intval(get_post_meta($preferred_player_id, 'blocking', true)) +
                                           intval(get_post_meta($preferred_player_id, 'defense', true)) +
                                           intval(get_post_meta($preferred_player_id, 'serve', true)) +
                                           intval(get_post_meta($preferred_player_id, 'attack_power', true)) +
                                           intval(get_post_meta($preferred_player_id, 'jumping', true)) +
                                           intval(get_post_meta($preferred_player_id, 'accuracy', true));

                $teams[$selected_team]['players'][] = $preferred_player_id;
                $teams[$selected_team]['total_rating'] += $preferred_player_rating;
                $assigned_players[] = $preferred_player_id; // Mark the preferred player as assigned
            }
        }
    }

    return $teams; // Return the distributed teams
}

// Check if the player participated on Saturday
function check_player_participation($player_id) {
    if (current_time('timestamp') > strtotime('this Saturday 18:00')) {
        return get_post_meta($player_id, 'played_saturday', true) === 'yes';
    }
    return false;
}

// Mark player participation after Saturday 18:00
function mark_player_participation() {
    if (current_time('timestamp') > strtotime('this Saturday 18:00')) {
        $players = get_posts(array('post_type' => 'players', 'posts_per_page' => -1));
        foreach ($players as $player) {
            update_post_meta($player->ID, 'played_saturday', 'yes');
        }
    }
}
add_action('wp_loaded', 'mark_player_participation');

// Reset registered players and teams
function reset_registered_players_and_teams() {
    update_option('registered_players', array());
    delete_option('current_teams');
}

// Schedule weekly reset
function schedule_reset() {
    if (!wp_next_scheduled('weekly_player_reset')) {
        wp_schedule_event(strtotime('next Sunday 10:00'), 'weekly', 'weekly_player_reset');
    }
}
add_action('wp', 'schedule_reset');

// Add player to registration list
function add_player_to_registration($player_id) {
    $registered_players = get_option('registered_players', array());
    if (!in_array($player_id, $registered_players)) {
        $registered_players[] = $player_id;
        update_option('registered_players', $registered_players);
    }
}

// Get registered player IDs
function get_registered_players_ids() {
    $registrations = get_posts(array('post_type' => 'registration', 'posts_per_page' => -1));
    return array_map(function($registration) {
        return get_post_meta($registration->ID, 'player_id', true);
    }, $registrations);
}

// Function to calculate the average rating of a team
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