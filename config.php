<?php
/*
Plugin Name: OpenAI Chatbot Plugin
Description: This is an AI Chatbot that help to improve user experiences when interacting with a website. This is an open-source project so that everybody can contribute to this project.
Version: 1.0
Author: Toan Vo
*/



if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path(__FILE__) . 'settings\settings.php';

function enqueue_css() {
    // Enqueue your CSS file
    wp_enqueue_style('chatbot-style', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_css');


function enqueue_font() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font');

function mini_chatbot_html() {
    ob_start(); // Start output buffering
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    </head>

    <body>
        <div class="chatbot-container lwh-open-cbot">
            <div class="custom-chatbot__image" onclick="lwhOpenCbotToggleChat()">
            <dotlottie-player src="https://lottie.host/3602bea0-da9e-46c4-b18a-7f5c3b02a4d2/oiG6SbwqPh.json" background="transparent" speed="1" style="width: 130px; height: 130px;" loop autoplay></dotlottie-player>
            </div>
            <div class="custom-chatbot">
                <div class="chat">
                    <div class="feedback-form">
                        <div class="feedback-header">
                            <p>Feedback</p>
                            <p class="feedback__modal-close" onclick="lwhOpenCbotremoveFeedbackModal()"><i class="fa-solid fa-xmark"></i></p>
                        </div>
                        <form onsubmit="lwhOpenCbotsendFeedback(event)">
                            <textarea name="feedback" id="feedback" rows="4" required></textarea>
                            <button type="submit">Send Feedback</button>
                        </form>
                    </div>
                    <div class="loading">
                        <p><i class="fa-solid fa-circle-notch fa-spin"></i></p>
                        <p>Wait a moment</p>
                    </div>
                    <div class="popup">
                        <p>Oops! Something went wrong!</p>
                    </div>
                    <div class="chat__header">
                        <div>
                            <div class="chat__title">AI Chatbot</div>
                        </div>
                        <div class="chat__close-icon" onclick="lwhOpenCbotToggleChat()">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                    </div>
                    <div class="chat__messages"></div>
                    <div class="chat__input-area">
                        <form id="messageForm" onsubmit="lwhOpenCbotonFormSubmit(event)">
                            <div class="input">
                                <input type="text" id="message" name="message" placeholder="Type your message" autocomplete="off" required>
                                <button type="submit" id="submit-btn"><i class="fa-solid fa-paper-plane" style="color: #74C0FC;"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
    <?php
    echo ob_get_clean(); // Output the buffered content
}

function enqueue_scripts() {
    wp_enqueue_script(
        'mini-chatbot-script', // Handle for the script
        plugin_dir_url(__FILE__) . 'script.js', // Path to your JavaScript file
        array('jquery'), // Dependencies (if any, e.g., jQuery)
        null, // Version number (you can use null for no version)
        true // Load in the footer (true) or head (false)
    );
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');

// Shortcode to display the chatbot
function mini_chatbot_shortcode() {
    ob_start();
    mini_chatbot_html();
    return ob_get_clean();
}
add_shortcode('mini_chatbot', 'mini_chatbot_shortcode');

function generate_chat_response( $last_prompt, $conversation_history ) {

    // OpenAI API URL and key
    $api_url = 'https://api.openai.com/v1/chat/completions';
    $api_key = 'sk-...'; // Replace with your actual API key
    
    // Headers for the OpenAI API
    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    ];
    
    // Add the last prompt to the conversation history
    $conversation_history[] = [
        'role' => 'system',
        'content' => 'Answer questions only related to digital marketing, otherwise, say I dont know'
    ];
    
    $conversation_history[] = [
        'role' => 'user',
        'content' => $last_prompt
    ];
    
    // Body for the OpenAI API
    $body = [
        'model' => 'gpt-3.5-turbo', // You can change the model if needed
        'messages' => $conversation_history,
        'temperature' => 0.7 // You can adjust this value based on desired creativity
    ];
    
    // Args for the WordPress HTTP API
    $args = [
        'method' => 'POST',
        'headers' => $headers,
        'body' => json_encode($body),
        'timeout' => 120
    ];
    
    // Send the request
    $response = wp_remote_request($api_url, $args);
    
    // Handle the response
    if (is_wp_error($response)) {
        return $response->get_error_message();
    } else {
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, associative: true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid JSON in API response',
                'result' => ''
            ];
        } elseif (!isset($data['choices'])) {
            return [
                'success' => false,
                'message' => 'API request failed. Response: ' . $response_body,
                'result' => ''
            ];
        } else {
            $content = $data['choices'][0]['message']['content'];
            return [
                'success' => true,
                'message' => 'Response Generated',
                'result' => $content
            ];
        }
    }
    }
    
    function generate_dummy_response( $last_prompt, $conversation_history ) {
    // Dummy static response
    $dummy_response = array(
        'success' => true,
        'message' => 'done',
        'result' => "here is my reply"
    );
    
    // Return the dummy response as an associative array
    return $dummy_response;
    }
    
    function handle_chat_bot_request( WP_REST_Request $request ) {
    $last_prompt = $request->get_param('last_prompt');
    $conversation_history = $request->get_param('conversation_history');
    
    $response = generate_chat_response($last_prompt, $conversation_history);
    return $response;
    }
    
    function load_chat_bot_base_configuration(WP_REST_Request $request) {
    // You can retrieve user data or other dynamic information here
    $user_avatar_url = plugins_url('images/user.png', __FILE__);
    $bot_image_url = plugins_url('images/chatbot.png', __FILE__);
    // Implement this function
    
    $response = array(
    'botStatus' => 0,
    'StartUpMessage' => "Hi, How are you?",
    'fontSize' => '16',
    'userAvatarURL' => $user_avatar_url,
    'botImageURL' => $bot_image_url,
    // Adding the new field
    'commonButtons' => array(
        array(
            'buttonText' => 'I want your help!!!',
            'buttonPrompt' => 'I have a question about your courses'
        ),
        array(
            'buttonText' => 'I want a Discount',
            'buttonPrompt' => 'I want a discount'
        )
    )
    );
    $response = new WP_REST_Response($response, 200);
    return $response;
    }
    
    add_action( 'rest_api_init', function () {
    register_rest_route( 'myapi/v1', '/chat-bot/', array(
       'methods' => 'POST',
       'callback' => 'handle_chat_bot_request',
       'permission_callback' => '__return_true'
    ) );
    
    register_rest_route('myapi/v1', '/chat-bot-config', array(
        'methods' => 'GET',
        'callback' => 'load_chat_bot_base_configuration',
    ));
    } );