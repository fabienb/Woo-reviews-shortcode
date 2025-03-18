<?php

/**
 * Snippet Name: Woo Reviews Shortcodes
 * Plugin URI: https://fabienb.blog
 * Description: Shows Customer Reviews in WooCommerce on themes that do not support them (via shortcodes). Add this code to your theme's functions.php or a custom plugin file or snippets plugin.
 * Version: 1.1.5
 * Author: Fabien Butazzi, improved and validated by Qwen 2.5 Coder 32B and Gemma 3 27B
 * Author URI: https://fabienb.blog
 * Text Domain: fabienb
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register the shortcode [product_reviews]
 */
function custom_product_reviews_shortcode() {
    global $product;

    if (!$product) {
        return '';
    }

    $product_id = $product->get_id();
    $reviews = fetch_reviews($product_id);

    if (empty($reviews)) {
        handle_no_reviews();
        return;
    }

    ob_start();
    ?>
    <div class="product-reviews-wrapper">
        <?php
        display_average_rating($product);
        foreach ($reviews as $review) {
            $thread = get_thread_replies($review);
            $rating = get_comment_meta($review->comment_ID, 'rating', true);

            echo '<div class="review-item">';
            render_review_header($review, $rating);
            render_review_content($review->comment_content);

            if (!empty($thread['replies'])) {
                echo '<div class="review-replies">';
                foreach ($thread['replies'] as $reply) {
                    echo '<div class="review-reply">';
                    render_reply_header($reply);
                    render_reply_content($reply->comment_content);
                    echo '</div>';
                }
                echo '</div>';
            }

            echo '</div>';
        }
        ?>
    </div>
    <?php
    add_basic_styling();
    return ob_get_clean();
}

function fetch_reviews($product_id) {
    global $wpdb; // Ensure $wpdb is available here

    return $wpdb->get_results($wpdb->prepare("
        SELECT c.*
        FROM $wpdb->comments c
        WHERE c.comment_post_ID = %d
        AND c.comment_approved = '1'
        AND c.comment_type = 'review'
        ORDER BY c.comment_date DESC
    ", $product_id));
}

function handle_no_reviews() {
    global $wpdb; // Ensure $wpdb is available here

    if (current_user_can('manage_options')) {
        echo '<!-- No reviews found in direct database query -->';
        echo '<!-- SQL Query: ' . esc_html($wpdb->last_query) . ' -->';
    }
    echo '<p>No reviews yet. Be the first to review this product!</p>';
}

function display_average_rating($product) {
    $average_rating = $product->get_average_rating();
    if ($average_rating) {
        echo '<h2 class="woocommerce-Reviews-title">Product Reviews</h2>';
        echo '<div class="average-rating">';
        echo wc_get_rating_html($average_rating);
        echo '<span class="rating-text">' . sprintf(_n('(%s rating)', '(%s ratings)', $product->get_review_count(), 'woocommerce'), esc_html($product->get_review_count())) . '</span>';
        echo '</div>';
    } else {
        echo '<h2 class="woocommerce-Reviews-title">Product Reviews</h2>';
    }
}

function get_thread_replies($review) {
    global $wpdb; // Ensure $wpdb is available here

    return array(
        'replies' => $wpdb->get_results($wpdb->prepare("
            SELECT c.*
            FROM $wpdb->comments c
            WHERE c.comment_post_ID = %d
            AND c.comment_approved = '1'
            AND c.comment_parent = %d
            ORDER BY c.comment_date ASC
        ", $review->comment_post_ID, $review->comment_ID)),
    );
}

function render_review_header($review, $rating) {
    echo '<div class="review-header">';
    if ($rating) {
        echo wc_get_rating_html($rating);
    }
    echo '<strong class="review-author">' . esc_html($review->comment_author) . '</strong>';
    echo '<span class="review-date">' . esc_html(human_time_diff(strtotime($review->comment_date))) . ' ago</span>';
    echo '</div>';
}

function render_review_content($content) {
    echo '<div class="review-content">';
    echo wpautop(wp_kses_post($content));
    echo '</div>';
}

function render_reply_header($reply) {
    // Check if the reply author is a registered user
    $user = get_user_by('login', $reply->comment_author);

    // If no user found, assume it's a guest review or another non-user authored comment.
    if ($user) {
        $is_shop_owner = user_can($user->ID, 'manage_options');
    } else {
        $is_shop_owner = false;
    }

    echo '<div class="reply-header">';
    echo '<strong class="reply-author">' . esc_html($reply->comment_author);
    if ($is_shop_owner) {
        echo ' <span class="shop-owner-badge">Shop Owner</span>';
    }
    echo '</strong>';
    echo '<span class="reply-date">' . esc_html(human_time_diff(strtotime($reply->comment_date))) . ' ago</span>';
    echo '</div>';
}


function render_reply_content($content) {
    echo '<div class="reply-content">';
    echo wpautop(wp_kses_post($content));
    echo '</div>';
}

function add_basic_styling() {
    ?>
    <style>
    .product-reviews-wrapper {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .woocommerce-Reviews-title {
        font-size: 24px;
        margin-bottom: 15px;
    }

    .average-rating {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .rating-text {
        margin-left: 10px;
    }

    .review-item {
        background: #fff;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .review-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .review-author {
        font-weight: bold;
        margin-right: 10px;
    }

    .review-date {
        color: #888;
        font-size: 14px;
    }

    .review-content {
        margin-bottom: 20px;
    }

    .review-replies {
        padding-left: 20px;
    }

    .review-reply {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
    }

    .reply-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .reply-author {
        font-weight: bold;
        margin-right: 10px;
    }

    .shop-owner-badge {
        background: #28a745;
        color: #fff;
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 12px;
        margin-left: 5px;
    }

    .reply-date {
        color: #888;
        font-size: 14px;
    }

    .reply-content {
        margin-bottom: 0;
    }
    </style>
    <?php
}

/**
 * Optional: Add review submission form
 */
function custom_review_form_shortcode($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(array(
        'product_id' => get_the_ID(), // Default to current product ID
    ), $atts, 'product_review_form');

    $product_id = intval($atts['product_id']);

    if (!is_user_logged_in()) {
        return login_prompt();
    }

    if (!is_valid_product($product_id)) {
        return '<p>Invalid product.</p>';
    }

    if (!has_purchased_product($product_id)) {
        return '<p>Only verified buyers can leave a review. Purchase this product to share your review!</p>';
    }

    if (has_already_reviewed($product_id)) {
        return '<p>You have already reviewed this product. Thank you for your feedback!</p>';
    }

    return generate_review_form($product_id);
}

function login_prompt() {
    return '<p>Please <a href="' . esc_url(wp_login_url(get_permalink())) . '">log in</a> to leave a review.</p>';
}

function is_valid_product($product_id) {
    return $product_id && wc_get_product($product_id);
}

function has_purchased_product($product_id) {
    $current_user = wp_get_current_user();
    return wc_customer_bought_product($current_user->user_email, $current_user->ID, $product_id);
}

function has_already_reviewed($product_id) {
    global $wpdb; // Ensure $wpdb is available here

    $current_user = wp_get_current_user();
    $has_reviewed = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(*)
        FROM $wpdb->comments
        WHERE comment_post_ID = %d
        AND user_id = %d
        AND comment_type = 'review'
    ", $product_id, $current_user->ID));

    return $has_reviewed > 0;
}

function generate_review_form($product_id) {
    ob_start();
    ?>
    <div class="woocommerce-review-form-wrapper">
        <h3><?php esc_html_e('Write a Review', 'woocommerce'); ?></h3>
        <form action="" method="post" id="commentform" class="comment-form" novalidate>
            <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($product_id); ?>" id="comment_post_ID">
            <input type="hidden" name="comment_parent" value="0" id="comment_parent">

            <p class="comment-form-rating">
                <label for="rating"><?php esc_html_e('Your Rating', 'woocommerce'); ?> <span class="required">*</span></label>
                <select name="rating" id="rating" required>
                    <option value=""><?php esc_html_e('Rate&hellip;', 'woocommerce'); ?></option>
                    <option value="5"><?php esc_html_e('Perfect', 'woocommerce'); ?></option>
                    <option value="4"><?php esc_html_e('Good', 'woocommerce'); ?></option>
                    <option value="3"><?php esc_html_e('Average', 'woocommerce'); ?></option>
                    <option value="2"><?php esc_html_e('Not that bad', 'woocommerce'); ?></option>
                    <option value="1"><?php esc_html_e('Very poor', 'woocommerce'); ?></option>
                </select>
            </p>

            <p class="comment-form-comment">
                <label for="comment"><?php esc_html_e('Your Review', 'woocommerce'); ?> <span class="required">*</span></label>
                <textarea id="comment" name="comment" cols="45" rows="8" required></textarea>
            </p>

            <?php wp_nonce_field('submit_review', 'review_nonce'); ?>

            <p class="form-submit">
                <input name="submit" type="submit" id="submit" class="submit button" value="<?php esc_attr_e('Submit Review', 'woocommerce'); ?>">
            </p>
        </form>
    </div>
    <?php
    // Add the form handling
    add_action('init', 'handle_review_submission');
    add_form_styling();
    return ob_get_clean();
}

function add_form_styling() {
    ?>
    <style>
    .woocommerce-review-form-wrapper {
        max-width: 800px;
        margin: 2em 0;
        padding: 20px;
        background: #f8f8f8;
        border-radius: 4px;
    }
    .comment-form-rating {
        margin: 1em 0;
    }
    .comment-form-rating select {
        display: block;
        margin-top: 5px;
    }
    .comment-form-comment textarea {
        width: 100%;
        margin-top: 5px;
    }
    .required {
        color: red;
    }
    .form-submit {
        margin-top: 1em;
    }
    .submit.button {
        background: #2c2d33;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    .submit.button:hover {
        background: #3e4046;
    }
    </style>
    <?php
}

/**
 * Handle the review form submission
 */
function handle_review_submission() {
    if (!is_valid_review_submission()) {
        return;
    }

    $product_id = intval($_POST['comment_post_ID']);
    $user = wp_get_current_user();
    $rating = intval($_POST['rating']);
    $comment = wp_kses_post($_POST['comment']);

    if (!user_can_review($user, $product_id)) {
        wp_die('You must purchase this product to review it.', 'Error', array('back_link' => true));
        return;
    }

    $comment_id = insert_review_comment($product_id, $user, $comment);
    if ($comment_id) {
        add_comment_meta($comment_id, 'rating', $rating);
        update_product_rating($product_id);
        redirect_to_product($product_id);
    }
}

function is_valid_review_submission() {
    return isset($_POST['review_nonce']) && wp_verify_nonce($_POST['review_nonce'], 'submit_review') &&
        is_user_logged_in() && !empty($_POST['comment']) && !empty($_POST['rating']) && !empty($_POST['comment_post_ID']);
}

function user_can_review($user, $product_id) {
    return wc_customer_bought_product($user->user_email, $user->ID, $product_id);
}

function insert_review_comment($product_id, $user, $comment) {
    $comment_data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_author_url' => '',
        'comment_content' => $comment,
        'comment_type' => 'review',
        'comment_parent' => 0,
        'user_id' => $user->ID,
        'comment_approved' => 1,
    );
    return wp_insert_comment($comment_data);
}

function update_product_rating($product_id) {
    $product = wc_get_product($product_id);
    if ($product) {
        $product->set_average_rating(null); // Trigger recalculation
        $product->save();
    }
}

function redirect_to_product($product_id) {
    wp_safe_redirect(get_permalink($product_id));
    exit;
}

// Add shortcodes
add_shortcode('product_reviews', 'custom_product_reviews_shortcode');
add_shortcode('product_review_form', 'custom_review_form_shortcode');
