<?php
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
    if (is_page_template('game-sign-up.php')) {
        wp_enqueue_style('home-style', get_template_directory_uri() . '/css/template/home.css', array(), get_file_version('/css/template/home.css'));

        wp_enqueue_script('home-script', get_template_directory_uri() . '/js/home.js', array('jquery'), null, true);
        wp_enqueue_script('progress-bar-script', get_template_directory_uri() . '/js/progress-bar.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'custom_enqueue_assets');