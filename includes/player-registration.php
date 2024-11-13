<?php
// Check if the player registration form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['player_id'])) {
    $player_id = intval($_POST['player_id']);
    $preferred_player = !empty($_POST['preferred_player']) ? intval($_POST['preferred_player']) : null;

    // Save preference as a custom field
    update_post_meta($player_id, 'preferred_player', $preferred_player);
    add_player_to_registration($player_id); // Add player to registration
}

// Check if the form to remove a player was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_player'])) {
    $player_to_remove = intval($_POST['player_to_remove']);
    $registered_players = get_option('registered_players', array());
    $registered_players = array_diff($registered_players, [$player_to_remove]);
    update_option('registered_players', $registered_players);
}

// Check if the form to create teams was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distribute_players'])) {
    // Get registered players
    $registered_players_ids = get_option('registered_players', array()); 
    $players = array_map('get_post', $registered_players_ids);

    // Distribute players into teams, considering preferences
    $teams = distribute_players($players);

    // Save teams in options
    update_option('current_teams', $teams);
}
?>