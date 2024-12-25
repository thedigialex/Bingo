<?php
if (!defined('ABSPATH')) exit;

get_header();
?>

<div class="bingo-content">
    <div class="bingo-header">
        <h1>All Bingo Cards</h1>
        <a href="/create-card" class="bingo-button">
            Create New
        </a>
    </div>
    <h2 style="margin-right:20px; margin-left:20px;">Your Bingo Cards</h2>
    <div id="bingo-cards-grid" class="bingo-cards-grid">
        <?php
        $current_user_id = get_current_user_id();
        $my_bingo_cards = new WP_Query([
            'post_type' => 'bingo_card',
            'posts_per_page' => -1,
            'author' => $current_user_id,
        ]);
        if ($my_bingo_cards->have_posts()):
            while ($my_bingo_cards->have_posts()): $my_bingo_cards->the_post();
        ?>
                <a href="<?php echo site_url('/bingo-card/' . get_post_field('post_name')); ?>" class="bingo-card-link">
                    <div class="bingo-card-item">
                        <i class="fa fa-th-large bingo-card-icon" aria-hidden="true"></i>
                        <h2 class="bingo-card-title"><?php the_title(); ?></h2>
                    </div>
                </a>
        <?php
            endwhile;
            wp_reset_postdata();
        else:
            echo '<p>You don\'t have any bingo cards.</p>';
        endif;

        $other_bingo_cards = new WP_Query([
            'post_type' => 'bingo_card',
            'posts_per_page' => -1,
            'author__not_in' => [$current_user_id],
        ]);
        ?>
    </div>
    <br>
    <h2 style="margin-right:20px; margin-left:20px;">Other Bingo Cards</h2>
    <div id="bingo-cards-grid" class="bingo-cards-grid">
        <?php
        if ($other_bingo_cards->have_posts()):
            while ($other_bingo_cards->have_posts()): $other_bingo_cards->the_post();
        ?>
                <a href="<?php echo site_url('/bingo-card/' . get_post_field('post_name')); ?>" class="bingo-card-link">
                    <div class="bingo-card-item">
                        <i class="fa fa-th-large bingo-card-icon" aria-hidden="true"></i>
                        <h2 class="bingo-card-title"><?php the_title(); ?></h2>
                    </div>
                </a>
        <?php
            endwhile;
            wp_reset_postdata();
        else:
            echo '<p>No other bingo cards found.</p>';
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>