<?php
/*
Plugin Name: Video Grid Shortcode (PHP with Thumbnails)
Description: Creates a shortcode to display a grid of YouTube video thumbnails from an ACF repeater field with PHP-based pagination and inline CSS.
Version: 1.5
Author: Your Name
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue JavaScript for click-to-play
function vgs_enqueue_assets() {
    if (!is_admin()) {
        wp_enqueue_script('vgs-script', plugins_url('video-grid-plugin.js', __FILE__), [], '1.5', true);
    }
}
add_action('wp_enqueue_scripts', 'vgs_enqueue_assets');

// Shortcode to display video grid
function vgs_video_grid_shortcode($atts) {
    // Shortcode attributes
    $atts = shortcode_atts([
        'videos_per_page' => 6
    ], $atts, 'video_grid');

    $videos_per_page = max(1, intval($atts['videos_per_page']));

    // Get repeater field data
    $repeater = get_field('field_6870692d4dd84');
    
    if (!$repeater || !is_array($repeater)) {
        return '<p>No videos found.</p>';
    }

    // Extract video URLs
    $video_urls = [];
    foreach ($repeater as $row) {
        $url = !empty($row['video_url']) ? esc_url_raw($row['video_url']) : '';
        if ($url) {
            $video_urls[] = $url;
        }
    }

    if (empty($video_urls)) {
        return '<p>No valid video URLs found.</p>';
    }

    // Pagination setup
    $total_videos = count($video_urls);
    $total_pages = ceil($total_videos / $videos_per_page);
    $current_page = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['vgs_page']) ? intval($_GET['vgs_page']) : 1));

    // Slice videos for current page
    $offset = ($current_page - 1) * $videos_per_page;
    $current_videos = array_slice($video_urls, $offset, $videos_per_page);

    // Output video grid with inline CSS
    ob_start();
    ?>
    <style>
.vgs-video-grid {
    max-width: 1280px; /* Increased to better fit 16:9 videos in 3 columns */
    margin: 0 auto;
    padding: 20px;
}

.vgs-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
}

.vgs-video-item {
    flex: 1 1 calc(33.333% - 14px); /* 3 columns, accounting for gap */
    min-width: 300px;
    position: relative;
    overflow: hidden;
    border-radius: 3px;
}

.vgs-video-wrapper {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    height: 0;
}

.vgs-video-wrapper img,
.vgs-video-wrapper iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures images and videos maintain aspect ratio */
}

@media (max-width: 960px) {
    .vgs-video-item {
        flex: 1 1 calc(50% - 10px); /* 2 columns on medium screens */
    }
}

@media (max-width: 600px) {
    .vgs-video-item {
        flex: 1 1 100%; /* 1 column on small screens */
    }
}

.vgs-pagination {
    text-align: center;
    margin-top: 20px;
}

.vgs-pagination a {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 5px;
    background-color: #f5f5f5;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.vgs-pagination a:hover {
    background-color: #ddd;
}

.vgs-pagination .current {
    color: #000;
}
    </style>
    <div class="vgs-video-grid">
        <div class="vgs-grid">
            <?php foreach ($current_videos as $url) : ?>
                <div class="vgs-video-item" data-video-id="<?php
                    $video_id = '';
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                        $video_id = $matches[1];
                    }
                    echo esc_attr($video_id);
                ?>">
                    <div class="vgs-video-wrapper">
                        <?php if ($video_id) : ?>
                            <img 
                                loading="lazy" 
                                src="https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/hqdefault.jpg" 
                                alt="Video thumbnail"
                            >
                            <div class="vgs-play-button"></div>
                        <?php else : ?>
                            <p>Invalid YouTube URL: <?php echo esc_url($url); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($total_pages > 1) : ?>
            <div class="vgs-pagination">
                <?php
                echo paginate_links([
                    'base' => add_query_arg('vgs_page', '%#%', esc_url(get_permalink())),
                    'format' => '?vgs_page=%#%',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text' => __('« Prev'),
                    'next_text' => __('Next »'),
                    'type' => 'plain',
                    'add_args' => false,
                    'add_fragment' => ''
                ]);
                ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('video_grid', 'vgs_video_grid_shortcode');

// Ensure pagination works on static pages
function vgs_add_query_vars($vars) {
    $vars[] = 'vgs_page';
    return $vars;
}
add_filter('query_vars', 'vgs_add_query_vars');
?>
