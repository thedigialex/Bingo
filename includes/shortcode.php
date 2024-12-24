<?php
function bingo_card_shortcode($atts)
{
    $atts = shortcode_atts([
        'user_id' => get_current_user_id()
    ], $atts);

    $args = [
        'post_type' => 'bingo_card',
        'author' => $atts['user_id']
    ];

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $bingo_items = get_post_meta(get_the_ID(), 'bingo_items', true) ?: [];
            $bingo_prizes = get_post_meta(get_the_ID(), 'bingo_prizes', true) ?: [];

            echo '<div class="bingo-card">';
            echo '<h3>' . get_the_title() . '</h3>';
            echo '<ul class="bingo-items">';

            foreach ($bingo_items as $index => $item) {
                echo '<li>';
                echo '<input type="checkbox" data-item-index="' . $index . '">';
                echo esc_html($item);
                echo '</li>';
            }

            echo '</ul>';

            echo '<h4>Prizes</h4>';
            echo '<ul class="bingo-prizes">';

            foreach ($bingo_prizes as $prize) {
                echo '<li>' . esc_html($prize) . '</li>';
            }

            echo '</ul>';
            echo '</div>';
        }
    } else {
        echo '<p>No Bingo Cards found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('bingo_card', 'bingo_card_shortcode');
