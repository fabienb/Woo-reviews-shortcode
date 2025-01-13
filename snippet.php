/**
 * Version: 1.1.2
 * Author: Fabien Butazzi (@fabienb)
 * Author URI: https://fabienb.blog
 * Add this code to your theme's functions.php or a custom plugin file or snippets plugin
 */


if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Register the shortcode [product_reviews]
 */
function custom_product_reviews_shortcode($atts) {
	// Extract shortcode attributes
	$atts = shortcode_atts(array(
		'product_id' => get_the_ID(), // Default to current product ID
		'number' => -1, // -1 means show all reviews
	), $atts, 'product_reviews');

	// Start output buffering
	ob_start();

	// Get the product ID
	$product_id = intval($atts['product_id']);

	// Verify this is a product page and get the product
	$product = wc_get_product($product_id);
	if (!$product) {
		return '<p>Invalid product.</p>';
	}

	// Debug information for administrators
	if (current_user_can('manage_options')) {
		echo '<!-- Debug Info:' . "\n";
		echo 'Product ID: ' . $product_id . "\n";
		echo 'Review Count: ' . $product->get_review_count() . "\n";
		echo 'Rating Count: ' . $product->get_rating_count() . "\n";
		echo 'Average Rating: ' . $product->get_average_rating() . "\n";
		echo '-->';
	}

	// Get all reviews and their replies from the database
	global $wpdb;
	$comments = $wpdb->get_results($wpdb->prepare("
		SELECT * FROM $wpdb->comments
		WHERE comment_post_ID = %d
		AND (comment_type = 'review' OR comment_parent IN (
			SELECT comment_ID FROM $wpdb->comments
			WHERE comment_post_ID = %d
			AND comment_type = 'review'
		))
		AND comment_approved = '1'
		ORDER BY comment_parent ASC, comment_date_gmt DESC
	", $product_id, $product_id));

	if (!empty($comments)) {
		echo '<div class="product-reviews-wrapper">';

		// Display average rating
		$average_rating = $product->get_average_rating();
		$review_count = $product->get_review_count();

		echo '<div class="average-rating">';
		echo '<h3>Customer Reviews (' . $review_count . ')</h3>';
		echo wc_get_rating_html($average_rating);
		echo '<span class="rating-text">(' . $average_rating . ' out of 5)</span>';
		echo '</div>';

		// Organize comments into parent-child structure
		$review_threads = array();
		foreach ($comments as $comment) {
			if ($comment->comment_parent == 0) {
				$review_threads[$comment->comment_ID] = array(
					'review' => $comment,
					'replies' => array()
				);
			} else {
				if (isset($review_threads[$comment->comment_parent])) {
					$review_threads[$comment->comment_parent]['replies'][] = $comment;
				}
			}
		}

		// Loop through each review thread
		foreach ($review_threads as $thread) {
			$review = $thread['review'];
			$rating = get_comment_meta($review->comment_ID, 'rating', true);

			echo '<div class="review-item">';

			// Review header
			echo '<div class="review-header">';
			if ($rating) {
				echo wc_get_rating_html($rating);
			}
			echo '<strong class="review-author">' . esc_html($review->comment_author) . '</strong>';
			echo '<span class="review-date">' . esc_html(human_time_diff(strtotime($review->comment_date))) . ' ago</span>';
			echo '</div>';

			// Review content
			echo '<div class="review-content">';
			echo wpautop(wp_kses_post($review->comment_content));
			echo '</div>';

			// Display replies if any exist
			if (!empty($thread['replies'])) {
				echo '<div class="review-replies">';
				foreach ($thread['replies'] as $reply) {
					echo '<div class="review-reply">';
					echo '<div class="reply-header">';
					echo '<strong class="reply-author">' . esc_html($reply->comment_author);
					if (user_can(get_user_by('login', $reply->comment_author)->ID, 'manage_options')) {
						echo ' <span class="shop-owner-badge">Shop Owner</span>';
					}
					echo '</strong>';
					echo '<span class="reply-date">' . esc_html(human_time_diff(strtotime($reply->comment_date))) . ' ago</span>';
					echo '</div>';
					echo '<div class="reply-content">';
					echo wpautop(wp_kses_post($reply->comment_content));
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}

			echo '</div>';
		}

		echo '</div>';

		// Add some basic styling
		?>
		<style>
			.product-reviews-wrapper {
				margin: 20px 0;
			}
			.average-rating {
				margin-bottom: 30px;
				text-align: center;
			}
			.review-item {
				border-bottom: 1px solid #eee;
				padding: 15px 0;
			}
			.review-header {
				margin-bottom: 10px;
			}
			.review-author {
				margin-right: 10px;
			}
			.review-date {
				color: #777;
				font-size: 0.9em;
			}
			.woocommerce-Reviews-title {
				margin-bottom: 20px;
			}
			.rating-text {
				margin-left: 10px;
				color: #666;
			}
			.review-replies {
				margin-left: 30px;
				margin-top: 15px;
				border-left: 2px solid #f0f0f0;
				padding-left: 20px;
			}
			.review-reply {
				background: #f9f9f9;
				padding: 15px;
				margin-bottom: 10px;
				border-radius: 4px;
			}
			.reply-header {
				margin-bottom: 8px;
			}
			.reply-author {
				margin-right: 10px;
			}
			.reply-date {
				color: #777;
				font-size: 0.9em;
			}
			.shop-owner-badge {
				background: #4CAF50;
				color: white;
				padding: 2px 8px;
				border-radius: 3px;
				font-size: 0.8em;
				margin-left: 5px;
			}
			.reply-content p {
				margin: 0;
			}
		</style>
		<?php
	} else {
		// Additional debug information
		if (current_user_can('manage_options')) {
			echo '<!-- No reviews found in direct database query -->';
			// Show the SQL query for debugging
			echo '<!-- SQL Query: ' . $wpdb->last_query . ' -->';
		}
		echo '<p>No reviews yet. Be the first to review this product!</p>';
	}

	// Return the buffered content
	return ob_get_clean();
}
add_shortcode('product_reviews', 'custom_product_reviews_shortcode');

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
		return '<p>Please <a href="' . esc_url(wp_login_url(get_permalink())) . '">log in</a> to leave a review.</p>';
	}

	if (!$product_id || !wc_get_product($product_id)) {
		return '<p>Invalid product.</p>';
	}

	// Check if the current user has purchased the product
	$current_user = wp_get_current_user();
	$has_purchased = wc_customer_bought_product($current_user->user_email, $current_user->ID, $product_id);

	if (!$has_purchased) {
		return '<p>Only verified buyers can leave a review. Purchase this product to share your review!</p>';
	}

	// Check if user has already reviewed this product
	global $wpdb;
	$has_reviewed = $wpdb->get_var($wpdb->prepare("
		SELECT COUNT(*)
		FROM $wpdb->comments
		WHERE comment_post_ID = %d
		AND user_id = %d
		AND comment_type = 'review'
	", $product_id, $current_user->ID));

	if ($has_reviewed > 0) {
		return '<p>You have already reviewed this product. Thank you for your feedback!</p>';
	}

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

	// Add the form handling
	add_action('init', 'handle_review_submission');

	return ob_get_clean();
}

/**
 * Handle the review form submission
 */
function handle_review_submission() {
	if (!isset($_POST['review_nonce']) || !wp_verify_nonce($_POST['review_nonce'], 'submit_review')) {
		return;
	}

	if (!is_user_logged_in() || empty($_POST['comment']) || empty($_POST['rating']) || empty($_POST['comment_post_ID'])) {
		return;
	}

	$product_id = intval($_POST['comment_post_ID']);
	$user = wp_get_current_user();
	$rating = intval($_POST['rating']);
	$comment = wp_kses_post($_POST['comment']);

	// Verify purchase
	if (!wc_customer_bought_product($user->user_email, $user->ID, $product_id)) {
		wp_die('You must purchase this product to review it.', 'Error', array('back_link' => true));
		return;
	}

	// Create the comment data
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

	// Insert the comment
	$comment_id = wp_insert_comment($comment_data);

	if ($comment_id) {
		// Add the rating
		add_comment_meta($comment_id, 'rating', $rating);

		// Update product average rating
		$product = wc_get_product($product_id);
		if ($product) {
			$product->set_average_rating(null); // Trigger recalculation
			$product->save();
		}

		// Redirect to prevent double submission
		wp_safe_redirect(get_permalink($product_id));
		exit;
	}
}

// Add shortcodes
add_shortcode('product_reviews', 'custom_product_reviews_shortcode');
add_shortcode('product_review_form', 'custom_review_form_shortcode');
