<?php
/*
Plugin Name:  Embed animation
Plugin URI:   https://github.com/EnrikasVai/Embed-WP-plugin
Description:  Plugin that adds your yt embed video to every post at the top and adds an animation that minimize the video and snaps it to bottom right corner
Version:      1.0
Author:       Enrikas Vaiciulis
Author URI:   https://github.com/EnrikasVai
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

//wp interface 
add_action('admin_menu', 'embed_animation_plugin_menu');

function embed_animation_plugin_menu() {
    add_options_page('Embed Animation Settings', 'Embed Animation', 'manage_options', 'embed-animation-settings', 'embed_animation_plugin_settings_page');
}

function embed_animation_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h2>Embed Animation Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('embed_animation_options_group');
            do_settings_sections('embed-animation-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'embed_animation_plugin_settings_init');

function embed_animation_plugin_settings_init() {
    register_setting('embed_animation_options_group', 'embed_animation_youtube_link', 'embed_animation_sanitize_iframe');

    add_settings_section(
        'embed_animation_setting_section',
        'YouTube Embed Settings',
        'embed_animation_setting_section_cb',
        'embed-animation-settings'
    );

    add_settings_field(
        'embed_animation_youtube_field',
        'YouTube Embed Code',
        'embed_animation_youtube_field_cb',
        'embed-animation-settings',
        'embed_animation_setting_section'
    );
}

function embed_animation_setting_section_cb() {
    echo '<p>Enter the full YouTube embed iframe code.</p>';
}

function embed_animation_youtube_field_cb() {
    $setting = get_option('embed_animation_youtube_link');
    ?>
    <textarea name="embed_animation_youtube_link" rows="5" cols="50"><?php echo isset($setting) ? esc_textarea($setting) : ''; ?></textarea>
    <?php
}

function embed_animation_sanitize_iframe($input) {
    // Use DOMDocument to parse the iframe from the input
    $dom = new DOMDocument();
    @$dom->loadHTML($input, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $iframe = $dom->getElementsByTagName('iframe')->item(0);
    
    // Extract the src attribute value if the iframe tag exists
    if ($iframe) {
        $src = $iframe->getAttribute('src');
        return $src; // Return just the src URL
    }
    
    return ''; // Return empty if no iframe src is found
}


//Js Css HTML
function my_custom_plugin_scripts() {
    // Enqueue YouTube API script
    wp_enqueue_script('youtube-api', 'https://www.youtube.com/iframe_api', array(), '1.0', false);
    wp_enqueue_style('my-custom-plugin-css', plugins_url('/style.css', __FILE__));
    wp_enqueue_script('my-custom-plugin-js', plugins_url('/script.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_scripts');


//testing
/*
function add_custom_html_after_header($content) {
    if (is_single()) {
        $custom_html = '<div class="photo-container">
                            <div class="photo-close">
                                <p id="resetPhoto" style="cursor: pointer;">✖</p>
                            </div>
                            <iframe id="player" width="560" height="315" src="https://www.youtube.com/embed/fW7cG0apZwU?enablejsapi=1&autoplay=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                </div>';
						
        // Append custom HTML before the content
        $content = $custom_html . $content;
    }
    return $content;
}
*/

function add_custom_html_after_header($content) {
    if (is_single()) {
        // Retrieve the YouTube video src from the plugin settings
        $youtube_src = get_option('embed_animation_youtube_link');
        
        // Check if the src is set and not empty
        if (!empty($youtube_src)) {
            // Ensure the src includes the necessary parameters for your needs (e.g., enablejsapi=1)
            $youtube_src_with_params = add_query_arg(['enablejsapi' => '1', 'autoplay' => '1'], $youtube_src);

            $custom_html = '<div class="photo-container">
                                <div class="photo-close">
                                    <p id="resetPhoto" style="cursor: pointer;">✖</p>
                                </div>
                                <iframe id="player" width="560" height="315" src="' . esc_url($youtube_src_with_params) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </div>';
            // Append custom HTML before the content
            $content = $custom_html . $content;
        }
    }
    return $content;
}
add_filter('the_content', 'add_custom_html_after_header');
