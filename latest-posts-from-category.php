<?php
/*
Plugin Name: Latest Posts from Category Widget
Plugin URI: https://github.com/kumawatashok21
Description: Latest Posts from Category to show latest posts from a selected category with number of post setting!
Version: 1.0
Author: Ashok Kumawat
Author URI: https://github.com/kumawatashok21
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  lpfc-widget
Domain Path:  /languages
*/

// Block direct access for safety
if (!defined('ABSPATH')) exit;

// Add widget to WordPress admin
function add_category_posts_widget() {
    register_widget('Custom_Posts_Widget');
}
add_action('widgets_init', 'add_category_posts_widget');

// Widget class to show posts from a chosen category
class Custom_Posts_Widget extends WP_Widget {

    // Sets up widget name and ID for WordPress Admin 
    public function __construct() {
        
        parent::__construct(
            'custom_posts_widget', // Widget ID
            __('Latest Posts from Category', 'lpfc-widget'), // Name in admin
            array('description' => __('Shows recent posts from any category selected category by you.'))
        );
        
    }

    // Display widget on the website sidebar
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $num_posts = !empty($instance['num_posts']) ? absint($instance['num_posts']) : 5;
        $category_id = !empty($instance['category_id']) ? absint($instance['category_id']) : 0;

        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // Check if category is selected, then show posts
        if ($category_id > 0) {
            
            $query = new WP_Query(array(
                'cat' => $category_id,
                'posts_per_page' => $num_posts,
                'post_status' => 'publish'
            ));

            if ($query->have_posts()) {
                echo '<ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li><a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No posts found in selected category.</p>';
            }
            wp_reset_postdata();
        } else {
            echo '<p>Please select a category to show post!</p>';
        }

        echo $args['after_widget'];
    }

    // setting form in WordPress admin 
    public function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : __('Latest Posts from Category', 'lpfc-widget');
        $num_posts = isset($instance['num_posts']) ? absint($instance['num_posts']) : 5;
        $category_id = isset($instance['category_id']) ? absint($instance['category_id']) : 0;
        $categories = get_categories(array('hide_empty' => false));
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Title:</label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('num_posts')); ?>">Posts to show:</label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('num_posts')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('num_posts')); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($num_posts); ?>" 
                   size="3" />
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('category_id')); ?>">Category:</label>
            <select class="widefat" 
                    id="<?php echo esc_attr($this->get_field_id('category_id')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('category_id')); ?>">
                <option value="">-- Choose --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($cat->term_id, $category_id); ?>>
                        <?php echo esc_html($cat->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    // Save and update widget setting
    public function update($new_instance, $old_instance) {
        $instance = array();
        // Clean title and numbers to keep things safe
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['num_posts'] = !empty($new_instance['num_posts']) ? absint($new_instance['num_posts']) : 5;
        $instance['category_id'] = !empty($new_instance['category_id']) ? absint($new_instance['category_id']) : 0;
        return $instance;
    }
}
?>