<?php

/**
 * Plugin Name: Video Upload Plugin
 * Description: A plugin to upload videos and store their URLs in a custom post type 'videos'.
 * Version: 1.0
 * Author: Ramzan
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// enqueue styles and scripts
function vup_enqueue_scripts(){
    wp_enqueue_style('video-upload', plugins_url('assets/vup-style.css?v'. time(), __FILE__), array(), '1.0');
}
add_action('wp_enqueue_scripts', 'vup_enqueue_scripts');

// Register Custom Post Type
function vup_register_custom_post_type()
{
    $args = array(
        'label'               => 'Videos',
        'description'         => 'Custom Post Type for videos',
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'videos'),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'supports'            => array('title', 'editor'),
        'show_in_rest'        => true,
    );
    register_post_type('videos', $args);
}
add_action('init', 'vup_register_custom_post_type');

// Shortcode for Video Upload Form
// Shortcode for Video Upload Form
function vup_video_upload_form_shortcode()
{
    // Initialize message variable
    $message = '';

    // Check if a success or error message is set (after form submission)
    if (isset($_POST['submit_video'])) {
        $message = vup_handle_video_upload();
    }

    ob_start();
    ?>


    <div class="video-upload-wrapper">
        <form id="video-upload-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('video_upload_nonce'); ?>
            <div class="form-group">
                <label for="video-upload" class="upload-label">Upload Video</label>
                <input type="file" name="video_file" id="video-upload" accept="video/*" required>
            </div>
            <div class="form-group">
                <input type="submit" name="submit_video" value="Upload Video" class="submit-btn">
            </div>
        </form>

        <!-- Display success or error messages here -->
        <div id="upload-status">
            <?php echo $message; ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('video_upload_form', 'vup_video_upload_form_shortcode');


// Handle File Upload and Save to Custom Post Type
// Handle File Upload and Save to Custom Post Type
function vup_handle_video_upload()
{
    if (isset($_POST['submit_video']) && isset($_FILES['video_file'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Check nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'video_upload_nonce')) {
            return '<div class="upload-error">Security check failed</div>';
        }

        $file = $_FILES['video_file'];
        $upload = wp_handle_upload($file, array('test_form' => false));

        // If the upload is successful, save to custom post type
        if ($upload && !isset($upload['error'])) {
            $video_url = $upload['url'];
            $post_id = wp_insert_post(array(
                'post_title'   => basename($video_url),
                'post_type'    => 'videos',
                'post_status'  => 'publish',
                'post_content' => $video_url,
            ));

            if ($post_id) {
                return '<div class="upload-success">Video uploaded successfully!</div>';
            } else {
                return '<div class="upload-error">Failed to save video.</div>';
            }
        } else {
            return '<div class="upload-error">Upload error: ' . $upload['error'] . '</div>';
        }
    }
}



// Add Nonce Field for Security
function vup_add_nonce_field()
{
    wp_nonce_field('video_upload_nonce');
}
add_action('wp_footer', 'vup_add_nonce_field');




