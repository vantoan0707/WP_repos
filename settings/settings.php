<?php

// Hook to add the settings page to the WordPress admin menu
add_action('admin_menu', 'simple_settings_menu');

function simple_settings_menu() {
    // Add a new submenu under Settings
    add_menu_page(
        'Henz Chatbot',          // Page title
        'Henz Chatbot',          // Menu title
        'manage_options',           // Capability required to access the page
        'simple-settings',          // Menu slug
        'simple_settings_page_html',
        'dashicons-superhero', // Icon for the menu
        10 // Callback function to display the page content
    );
}

// Enqueue the media uploader script
add_action('admin_enqueue_scripts', 'enqueue_media_uploader');

function enqueue_media_uploader() {
    wp_enqueue_media();
    wp_enqueue_script('media-uploader-script', plugin_dir_url(__FILE__) . 'media-uploader.js', array('jquery'), null, true);
}

// Callback function to display the settings page
function simple_settings_page_html() {
    global $wpdb; // Access the WordPress database object

    // Check if the user has the required capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Define table name
    $table_name = $wpdb->prefix . 'base_info';

    // Check if the settings have been updated
    if (isset($_POST['submit']) && check_admin_referer('simple_settings_update', 'simple_settings_nonce')) {
        // Save the settings
        update_option('simple_setting_option', sanitize_text_field($_POST['simple_setting_option']));
        update_option('selected_model', sanitize_text_field($_POST['selected_model'])); // Save selected model
        update_option('chatbot_logo', esc_url_raw($_POST['chatbot_logo'])); // Save logo URL

        // Save company information
        $company_name = sanitize_text_field($_POST['company_name']);
        $company_address = sanitize_textarea_field($_POST['company_address']);
        $company_phone = sanitize_text_field($_POST['company_phone']);
        $company_email = sanitize_email($_POST['company_email']);
        $company_description = sanitize_textarea_field($_POST['company_description']);

        update_option('company_name', $company_name);
        update_option('company_address', $company_address);
        update_option('company_phone', $company_phone);
        update_option('company_email', $company_email);
        update_option('company_description', $company_description);

        // Insert or update the database table
        $existing_entry = $wpdb->get_row("SELECT * FROM $table_name WHERE id = 1"); // Assume a single row with id=1
        if ($existing_entry) {
            // Update the existing row
            $wpdb->update(
                $table_name,
                [
                    'company_name' => $company_name,
                    'company_address' => $company_address,
                    'company_phone' => $company_phone,
                    'company_email' => $company_email,
                    'company_description' => $company_description
                ],
                ['id' => 1], // WHERE clause
                ['%s', '%s', '%s', '%s'], // Data types for the columns
                ['%d'] // Data type for the WHERE clause
            );
        } else {
            // Insert a new row
            $wpdb->insert(
                $table_name,
                [
                    'id' => 1, // Set id to 1 if it's a single-row table
                    'company_name' => $company_name,
                    'company_address' => $company_address,
                    'company_phone' => $company_phone,
                    'company_email' => $company_email,
                    'company_description' => $company_description
                ],
                ['%d', '%s', '%s', '%s', '%s'] // Data types for the columns
            );
        }

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Retrieve the current setting values
    $current_value = get_option('simple_setting_option', '');
    $selected_model = get_option('selected_model', 'OpenAi'); // Default model
    $chatbot_logo = get_option('chatbot_logo', ''); // Logo URL

    // Retrieve company information
    $company_name = get_option('company_name', '');
    $company_address = get_option('company_address', '');
    $company_phone = get_option('company_phone', '');
    $company_email = get_option('company_email', '');
    $company_description = get_option('company_description', '');

    
    ?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <div class="wrap">
        <h1 style="font-family: 'Silkscreen', cursive; font-size: 50px;">Henz Chatbot</h1>
        <hr style="border: 3px solid black; width: 80%;">
        <!-- Navigation Bar -->
        <div class="nav-tab-wrapper">
            <a href="#base-knowledge" class="nav-tab nav-tab-active">Base Knowledge</a>
            <a href="#target" class="nav-tab">Target Pages</a>
            <a href="#display" class="nav-tab">Display</a>
            <a href="#options" class="nav-tab">Customization</a>
        </div>

        <form method="post" action="" id="settings-form" onsubmit="reloadAfterSubmit()">
            <?php wp_nonce_field('simple_settings_update', 'simple_settings_nonce'); ?>

            <!-- Base Knowledge Section -->
            <div class="tab-content" id="base-knowledge">
                <h2>Company Information</h2>
                <br>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="company_name">Company Name:</label></th>
                        <td><input type="text" id="company_name" name="company_name" value="<?php echo esc_attr($company_name); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_address">Company Address:</label></th>
                        <td><textarea id="company_address" name="company_address" rows="3" class="large-text"><?php echo esc_textarea($company_address); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_phone">Company Phone:</label></th>
                        <td><input type="text" id="company_phone" name="company_phone" value="<?php echo esc_attr($company_phone); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_email">Company Email:</label></th>
                        <td><input type="email" id="company_email" name="company_email" value="<?php echo esc_attr($company_email); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="company_description">Company Address:</label></th>
                        <td><textarea id="company_description" name="company_description" rows="3" class="large-text"><?php echo esc_textarea($company_description); ?></textarea></td>
                    </tr>
                </table>
            </div>

            <!-- Target Section -->
            <div class="tab-content" id="target" style="display:none;">
                <h3>Select pages to display the chatbot</h3>
                <br>
                <!-- Table to list pages -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Page Title</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all published pages
                        $pages = get_posts([
                            'post_type'   => 'page',
                            'post_status' => 'publish',
                            'orderby'     => 'title',
                            'order'       => 'ASC',
                            'numberposts' => -1, // Get all pages
                        ]);

                        // Fetch selected pages from the database
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'target_pages';
                        $stored_pages = $wpdb->get_results("SELECT page_id FROM $table_name");

                        // Convert stored pages into an array of IDs
                        $stored_page_ids = array_map(function ($page) {
                            return $page->page_id;
                        }, $stored_pages);

                        // Check if pages have been selected
                        $selected_pages = isset($_POST['selected_pages']) ? $_POST['selected_pages'] : $stored_page_ids;

                        // Loop through each page and create a row in the table
                        foreach ($pages as $page) {
                            $page_title = esc_html($page->post_title);
                            $page_id = $page->ID;
                            $checked = in_array($page_id, $selected_pages) ? 'checked' : ''; // Check if page is selected

                            echo "<tr>";
                            echo "<td>$page_title</td>";
                            echo "<td><input type='checkbox' name='selected_pages[]' value='$page_id' $checked></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <?php
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simple_settings_nonce']) && wp_verify_nonce($_POST['simple_settings_nonce'], 'simple_settings_update')) {
                if (isset($_POST['selected_pages'])) {
                    global $wpdb;
                    $selected_pages = $_POST['selected_pages'];
                    $table_name = $wpdb->prefix . 'target_pages';

                    // Step 1: Remove unchecked pages from the database
                    $wpdb->query("DELETE FROM $table_name WHERE page_id NOT IN (" . implode(',', array_map('intval', $selected_pages)) . ")");

                    // Step 2: Insert or update selected pages
                    foreach ($selected_pages as $page_id) {
                        // Get page details
                        $page = get_post($page_id);
                        $page_title = esc_html($page->post_title);
                        $page_url = get_permalink($page_id);

                        // Check if the page already exists in the database, if not, insert it
                        $existing_page = $wpdb->get_var($wpdb->prepare("SELECT page_id FROM $table_name WHERE page_id = %d", $page_id));

                        if (!$existing_page) {
                            // Insert into the table if not already present
                            $wpdb->insert(
                                $table_name,
                                [
                                    'page_id' => $page_id,
                                    'page_title' => $page_title,
                                    'page_url' => $page_url,
                                ]
                            );
                        }

                        // Step 3: Insert the shortcode into the page content
                        $shortcode = '[mini_chatbot]'; // Replace with your actual shortcode
                        $content = $page->post_content;

                        // Check if the shortcode already exists in the content to avoid duplicate
                        if (strpos($content, $shortcode) === false) {
                            $content .= "\n" . $shortcode; // Append the shortcode to the content

                            // Update the page content
                            wp_update_post([
                                'ID' => $page_id,
                                'post_content' => $content,
                            ]);
                        }
                    }

                    // Step 4: Remove the shortcode from unchecked pages
                    $unchecked_pages = array_diff($stored_page_ids, $selected_pages);
                    foreach ($unchecked_pages as $page_id) {
                        $page = get_post($page_id);
                        $content = $page->post_content;

                        // Remove the shortcode if it exists
                        if (strpos($content, '[mini_chatbot]') !== false) {
                            // Remove the shortcode from the content
                            $content = str_replace('[mini_chatbot]', '', $content);

                            // Update the page content
                            wp_update_post([ 
                                'ID' => $page_id, 
                                'post_content' => $content, 
                            ]);
                        }

                        // Remove from the database if the page is unchecked
                        $wpdb->delete($table_name, ['page_id' => $page_id]);
                    }
                } else {
                    // If no pages are selected, remove all pages from the database
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'target_pages';
                    $wpdb->query("DELETE FROM $table_name");

                    // Also remove the shortcode from all pages
                    $pages = get_posts([
                        'post_type'   => 'page',
                        'post_status' => 'publish',
                        'orderby'     => 'title',
                        'order'       => 'ASC',
                        'numberposts' => -1, // Get all pages
                    ]);

                    foreach ($pages as $page) {
                        $content = $page->post_content;

                        // Remove the shortcode if it exists
                        if (strpos($content, '[mini_chatbot]') !== false) {
                            // Remove the shortcode from the content
                            $content = str_replace('[mini_chatbot]', '', $content);

                            // Update the page content
                            wp_update_post([
                                'ID' => $page->ID,
                                'post_content' => $content,
                            ]);
                        }
                    }
                }
            }
            ?>
            



            <!-- Display Section -->
            <div class="tab-content" id="display" style="display:none;">
                <h2>Display</h2>
                <p>This is the display section.</p>
            </div>

            <!-- Options Section -->
            <div class="tab-content" id="options" style="display:none;">
                <h2>Options</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="selected_model">Select AI Model:</label></th>
                        <td>
                            <select id="selected_model" name="selected_model">
                                <option value="OpenAi" <?php selected($selected_model, 'OpenAi'); ?>>OpenAI</option>
                                <option value="Gemini" <?php selected($selected_model, 'Gemini'); ?>>Gemini</option>
                                <option value="Custom API" <?php selected($selected_model, 'Custom API'); ?>>Custom API</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="simple_setting_option">Enter API link:</label></th>
                        <td><input type="text" id="simple_setting_option" name="simple_setting_option" value="<?php echo esc_attr($current_value); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="chatbot_logo">Chatbot Logo:</label></th>
                        <td>
                            <input type="text" id="chatbot_logo" name="chatbot_logo" value="<?php echo esc_url($chatbot_logo); ?>" class="regular-text">
                            <button type="button" class="button" id="upload_logo_button">Upload Logo</button>
                            <br><br>
                            <img id="chatbot_logo_preview" src="<?php echo esc_url($chatbot_logo); ?>" style="max-width: 200px; <?php echo $chatbot_logo ? '' : 'display:none;'; ?>" />
                        </td>
                    </tr>
                </table>
            </div>

            <?php submit_button('Save Settings');?>        
        </form>


    <script>

        function reloadAfterSubmit() {
            // Delay the reload to allow form submission to complete
            setTimeout(() => {
                window.location.reload();
            }, 200); // Adjust delay as needed
        }


        // Tab navigation functionality
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.nav-tab');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();

                    tabs.forEach(t => t.classList.remove('nav-tab-active'));
                    this.classList.add('nav-tab-active');

                    contents.forEach(content => content.style.display = 'none');
                    const activeContent = document.querySelector(this.getAttribute('href'));
                    if (activeContent) {
                        activeContent.style.display = 'block';
                    }
                });
            });

            // Display the first tab content by default
            document.querySelector('.nav-tab-active').click();
        });
    </script>

    <script>
        // WordPress Media Uploader for chatbot logo
        document.addEventListener('DOMContentLoaded', function () {
            const uploadButton = document.getElementById('upload_logo_button');
            const logoInput = document.getElementById('chatbot_logo');
            const logoPreview = document.getElementById('chatbot_logo_preview');

            if (uploadButton) {
                uploadButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    const frame = wp.media({
                        title: 'Select or Upload Chatbot Logo',
                        button: { text: 'Use this logo' },
                        multiple: false
                    });

                    frame.on('select', function () {
                        const attachment = frame.state().get('selection').first().toJSON();
                        logoInput.value = attachment.url;
                        logoPreview.src = attachment.url;
                        logoPreview.style.display = 'block';
                    });

                    frame.open();
                });
            }
        });
    </script>


    <style>
        /* Navigation Bar Styles */
        .nav-tab-wrapper {
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }

        .nav-tab {
            padding: 10px 15px;
            margin-right: 5px;
            display: inline-block;
            background: #f1f1f1;
            color: #333;
            text-decoration: none;
            border-radius: 3px 3px 0 0;
        }

        .nav-tab:hover {
            background: #eaeaea;
        }

        .nav-tab-active {
            background: #fff;
            border-bottom: 1px solid #fff;
            font-weight: bold;
        }

        /* Tab Content Styles */
        .tab-content {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 3px;
            background: #fff;
            display: none; /* Hide all tabs initially */
        }

        /* Show the active tab */
        .tab-content:target {
            display: block;
        }

        /* Form Table Styles */
        .form-table th {
            width: 200px;
        }

        .form-table td {
            padding: 10px 15px;
        }

        /* Logo Preview */
        #chatbot_logo_preview {
            margin-top: 10px;
            max-width: 200px;
            display: none;
        }

        #base-knowledge {
            display: block; /* Show Base Knowledge tab content by default */
        }

    </style>
    <?php
}

// Create a JavaScript file for handling the media uploader
function media_uploader_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('#upload_logo_button').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: 'Upload Logo',
                    button: {
                        text: 'Use this logo'
                    },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#chatbot_logo').val(attachment.url);
                    $('#chatbot_logo_preview').attr('src', attachment.url).show();
                });
                mediaUploader.open();
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'media_uploader_script');

function nav_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#base-knowledge').show();
            // Navigation Tab Script
            $('.nav-tab').click(function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                var activeTab = $(this).attr('href');
                $(activeTab).show();
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'nav_script');



