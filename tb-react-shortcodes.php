<?php
/*
Plugin Name: TB React Shortcodes
Description: A plugin to add a night mode toggle React app (developed with Vite) to a WordPress site using a shortcode.
Version: 1.0
Author: TB Tech
*/

// Enqueue styles
function tb_react_enqueue_styles() {
    // Enqueue your plugin's CSS
    wp_enqueue_style('tb-react-styles', plugin_dir_url(__FILE__) . 'night-toggle/dist/assets/index.css');
}
add_action('wp_enqueue_scripts', 'tb_react_enqueue_styles');

// Enqueue scripts
function tb_react_enqueue_scripts() {
    // Enqueue your plugin's JavaScript
    wp_enqueue_script('tb-react-scripts', plugin_dir_url(__FILE__) . 'night-toggle/dist/assets/index.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'tb_react_enqueue_scripts');

// Shortcode function to display the night mode toggle
function tb_display_night_mode_toggle() {
    ob_start();
    ?>
    <div id="root"></div> <!-- This is where your React app will render -->
    <?php
    return ob_get_clean();
}
add_shortcode('tb-night-mode', 'tb_display_night_mode_toggle');

// Register the admin menu item for the settings page
function tb_react_shortcodes_menu() {
    add_menu_page(
        'TB React Settings',        // Page title
        'TB React',                 // Menu title
        'manage_options',           // Capability required to access the menu
        'tb-react-settings',        // Menu slug
        'tb_react_settings_page_callback', // Callback function to display the page content
        80                          // Menu position (optional)
    );
}
add_action('admin_menu', 'tb_react_shortcodes_menu');

// Callback function to display the settings page content
function tb_react_settings_page_callback() {
    ?>
    <div class="wrap">
        <h1>Night Toggle Settings</h1><br>
        <h2 style="font-weight:100; font-size:14px; padding-bottom:60px;" > Use the shortcode [tb-night-mode] to inject night mode toggle</h2>

        <!-- Light Mode Colors Section -->
        <h2>Light Mode Colors</h2>
        <form method="post" action="options.php">
            <?php settings_fields('tb_react_light_colors'); ?>
            <?php do_settings_sections('tb_react_light_colors'); ?>

            <table class="form-table">
                <?php tb_react_color_input('tb_react_light_background_color', 'Background Color', '#ffffff'); ?>
                <?php tb_react_color_input('tb_react_light_foreground_color', 'Foreground Color', '#162140'); ?>
                <?php tb_react_color_input('tb_react_light_primary_text_color', 'Primary Text Color', '#000000'); ?>
                <?php tb_react_color_input('tb_react_light_secondary_text_color', 'Secondary Text Color', '#ffffff'); ?>
            </table>

            <?php submit_button(); ?>
        </form>

        <!-- Dark Mode Colors Section -->
        <h2>Dark Mode Colors</h2>
        <form method="post" action="options.php">
            <?php settings_fields('tb_react_dark_colors'); ?>
            <?php do_settings_sections('tb_react_dark_colors'); ?>

            <table class="form-table">
                <?php tb_react_color_input('tb_react_dark_background_color', 'Background Color', '#162140'); ?>
                <?php tb_react_color_input('tb_react_dark_foreground_color', 'Foreground Color', '#1f2e3e'); ?>
                <?php tb_react_color_input('tb_react_dark_primary_text_color', 'Primary Text Color', '#ffffff'); ?>
                <?php tb_react_color_input('tb_react_dark_secondary_text_color', 'Secondary Text Color', '#000000'); ?>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Function to generate the color input fields with hex input
function tb_react_color_input($option_name, $label, $default) {
    $color_value = esc_attr(get_option($option_name, $default));
    ?>
    <tr valign="top">
        <th scope="row"><?php echo $label; ?></th>
        <td>
            <input type="color" name="<?php echo $option_name; ?>" value="<?php echo $color_value; ?>" oninput="updateHexInput(this)">
            <input type="text" class="hex-input" name="<?php echo $option_name; ?>_hex" value="<?php echo $color_value; ?>" pattern="#[a-fA-F0-9]{6}" title="Hexadecimal color code" oninput="updateColorPicker(this)">
        </td>
    </tr>
    <script>
        function updateHexInput(colorInput) {
            colorInput.nextElementSibling.value = colorInput.value;
        }

        function updateColorPicker(hexInput) {
            hexInput.previousElementSibling.value = hexInput.value;
        }
    </script>
    <?php
}

// Register settings and default values
function tb_react_register_settings() {
    // Light mode colors
    register_setting('tb_react_light_colors', 'tb_react_light_background_color');
    register_setting('tb_react_light_colors', 'tb_react_light_foreground_color');
    register_setting('tb_react_light_colors', 'tb_react_light_primary_text_color');
    register_setting('tb_react_light_colors', 'tb_react_light_secondary_text_color');

    // Dark mode colors
    register_setting('tb_react_dark_colors', 'tb_react_dark_background_color');
    register_setting('tb_react_dark_colors', 'tb_react_dark_foreground_color');
    register_setting('tb_react_dark_colors', 'tb_react_dark_primary_text_color');
    register_setting('tb_react_dark_colors', 'tb_react_dark_secondary_text_color');
}
add_action('admin_init', 'tb_react_register_settings');

// Enqueue styles for the front end based on settings
function enqueue_react_app_conditional() {
    global $post;

    // Default colors
    $default_light_colors = array(
        'background_color' => '#ffffff',
        'foreground_color' => '#162140',
        'primary_text_color' => '#000000',
        'secondary_text_color' => '#ffffff'
    );

    $default_dark_colors = array(
        'background_color' => '#162140',
        'foreground_color' => '#1f2e3e',
        'primary_text_color' => '#ffffff',
        'secondary_text_color' => '#000000'
    );

    // Get user-defined colors from options
    $light_background_color = get_option('tb_react_light_background_color', $default_light_colors['background_color']);
    $light_foreground_color = get_option('tb_react_light_foreground_color', $default_light_colors['foreground_color']);
    $light_primary_text_color = get_option('tb_react_light_primary_text_color', $default_light_colors['primary_text_color']);
    $light_secondary_text_color = get_option('tb_react_light_secondary_text_color', $default_light_colors['secondary_text_color']);

    $dark_background_color = get_option('tb_react_dark_background_color', $default_dark_colors['background_color']);
    $dark_foreground_color = get_option('tb_react_dark_foreground_color', $default_dark_colors['foreground_color']);
    $dark_primary_text_color = get_option('tb_react_dark_primary_text_color', $default_dark_colors['primary_text_color']);
    $dark_secondary_text_color = get_option('tb_react_dark_secondary_text_color', $default_dark_colors['secondary_text_color']);

    // Enqueue styles
    wp_register_style('tb-react-light-mode', false);
    wp_enqueue_style('tb-react-light-mode');
    $light_mode_css = "
        :root {
            --background-color: {$light_background_color};
            --foreground-color: {$light_foreground_color};
            --primary-text-color: {$light_primary_text_color};
            --secondary-text-color: {$light_secondary_text_color};
            --toggle-bg: #d2d2b6; /* Default toggle colors */
            --toggle-fg: #90ab9e;
        }
    ";
    wp_add_inline_style('tb-react-light-mode', $light_mode_css);

    wp_register_style('tb-react-dark-mode', false);
    wp_enqueue_style('tb-react-dark-mode');
    $dark_mode_css = "
        [data-theme='dark'] {
            --background-color: {$dark_background_color};
            --foreground-color: {$dark_foreground_color};
            --primary-text-color: {$dark_primary_text_color};
            --secondary-text-color: {$dark_secondary_text_color};
            --toggle-bg: #1f2e3e; /* Default toggle colors */
            --toggle-fg: #162140;
        }
    ";
    wp_add_inline_style('tb-react-dark-mode', $dark_mode_css);
}
add_action('wp_enqueue_scripts', 'enqueue_react_app_conditional');
?>
