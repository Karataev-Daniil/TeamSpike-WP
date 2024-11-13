<?php
if (!empty($registered_players)) {
    foreach ($registered_players as $player_id) {
        // Get player stats
        $attack_power = intval(get_post_meta($player_id, 'attack_power', true));
        $accuracy = intval(get_post_meta($player_id, 'accuracy', true));
        $blocking = intval(get_post_meta($player_id, 'blocking', true));
        $jumping = intval(get_post_meta($player_id, 'jumping', true));
        $defense = intval(get_post_meta($player_id, 'defense', true));
        $serve = intval(get_post_meta($player_id, 'serve', true));

        // Debug: Check if player stats are being retrieved correctly
        error_log("Player ID: $player_id | Attack: $attack_power, Accuracy: $accuracy, Blocking: $blocking, Jumping: $jumping, Defense: $defense, Serve: $serve");

        // Calculate total and average rating
        $total_rating = $attack_power + $accuracy + $blocking + $jumping + $defense + $serve;
        $average_rating = $total_rating / 6;

        // Round the rating
        $player_ratings[$player_id] = round($average_rating, 2);

        // Update the player's average rating
        $updated = update_post_meta($player_id, 'average_rating', $average_rating);

        // Debug: Check if the update is successful
        if (!$updated) {
            error_log("Failed to update average rating for player ID: $player_id");
        }
    }
} else {
    error_log("No registered players found.");
}
?>
