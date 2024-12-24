<?php
if (!defined('ABSPATH')) exit;

// Query all bingo cards
$bingo_cards = new WP_Query([
    'post_type' => 'bingo_card', // Replace 'bingo_card' with your custom post type name
    'posts_per_page' => -1
]);

get_header();
?>

<h1>All Bingo Cards</h1>
<ul>
    <?php if ($bingo_cards->have_posts()): ?>
        <?php while ($bingo_cards->have_posts()): $bingo_cards->the_post(); ?>
            <li>
                <a href="<?php echo site_url('/bingo-card/' . get_post_field('post_name')); ?>">
                    <?php the_title(); ?>
                </a>
            </li>
        <?php endwhile; ?>
        <?php wp_reset_postdata(); ?>
    <?php else: ?>
        <li>No bingo cards found.</li>
    <?php endif; ?>
</ul>

<?php get_footer(); ?>
