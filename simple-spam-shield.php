<?php
/*
Plugin Name: Simple Spam Shield
Description: Lightweight anti-spam plugin using honeypot, timing, and keyword filters. Created by Tessovate.com with help from ChatGPT.
Version: 1.4
Author: Sudakshi
Author URI: https://tessovate.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: simple-spam-shield
*/

add_action('comment_form', 'sss_add_honeypot');
function sss_add_honeypot() {
    echo '<p style="display:none;">
            <input type="text" name="sss_honey" value="" />
          </p>';
    echo '<input type="hidden" name="sss_timer" value="' . esc_attr(time()) . '" />';
    wp_nonce_field('sss_comment_nonce_action', 'sss_comment_nonce');
}

add_filter('preprocess_comment', 'sss_check_spam');
function sss_check_spam($commentdata) {
    // Verify nonce with wp_unslash and sanitize_text_field
    $nonce = isset($_POST['sss_comment_nonce']) ? sanitize_text_field(wp_unslash($_POST['sss_comment_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'sss_comment_nonce_action')) {
        wp_die(esc_html(__('Security check failed.', 'simple-spam-shield')));
    }

    // Honeypot check
    if (!empty($_POST['sss_honey'])) {
        wp_die(esc_html(__('Spam detected (honeypot).', 'simple-spam-shield')));
    }

    // Time check
    $time_posted = isset($_POST['sss_timer']) ? intval($_POST['sss_timer']) : 0;
    $time_diff = time() - $time_posted;
    if ($time_diff < 5) {
        wp_die(esc_html(__('Spam detected (submitted too quickly).', 'simple-spam-shield')));
    }

    // Keyword blacklist (example)
    $blacklist = ['viagra', 'casino', 'porn'];
    foreach ($blacklist as $word) {
        if (stripos($commentdata['comment_content'], $word) !== false) {
            wp_die(esc_html(__('Spam detected (blacklisted keyword).', 'simple-spam-shield')));
        }
    }

    return $commentdata;
}
?>
