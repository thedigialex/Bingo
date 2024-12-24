<?php
if (!defined('ABSPATH')) exit;

$bingo_slug = get_query_var('bingo_slug');

$bingo_card = new WP_Query([
    'post_type' => 'bingo_card',
    'name' => $bingo_slug,
    'posts_per_page' => 1
]);

get_header();

if ($bingo_card->have_posts()): $bingo_card->the_post();
?>
    <div style="width: 100%">
        <h1><?php the_title(); ?></h1>
        <?php the_content();
        $bingo_slots = get_post_meta(get_the_ID(), 'bingo_slots', true);
        $bingo_rewards = get_post_meta(get_the_ID(), 'bingo_rewards', true);
        $bingo_slots_status = get_post_meta($post->ID, 'bingo_slots_status', true);
        $bingo_rewards_claimed = get_post_meta($post->ID, 'bingo_rewards_claimed', true);
        ?>
        <h3>Bingo Slots</h3>
        <table class="bingo-table">
            <?php

            $is_author = get_current_user_id() == get_post_field('post_author', get_the_ID());

            if ($bingo_slots) {
                foreach (array_chunk($bingo_slots, 5) as $row_index => $row) {
                    echo '<tr>';
                    foreach ($row as $index => $slot) {
                        $slot_index = $row_index * 5 + $index;
                        $is_completed = isset($bingo_slots_status[$slot_index]) && $bingo_slots_status[$slot_index];
                        $cell_classes = $is_completed ? 'bingo-cell completed' : 'bingo-cell';

                        echo '<td class="' . $cell_classes . '" data-slot-index="' . $slot_index . '" data-post-id="' . esc_attr(get_the_ID()) . '">';
                        echo esc_html($slot);

                        // Add checkbox as a fallback or for JS toggling
                        echo '<input type="checkbox" class="bingo-slot-completion" name="bingo_slots_status[' . $slot_index . ']" ' . ($is_completed ? 'checked' : '') . ' style="display: none;">';

                        echo '</td>';
                    }
                    echo '</tr>';
                }
            }
            ?>
        </table>
        <?php
        if ($bingo_rewards) {
            echo '<h3>Rewards</h3>';
            echo '<div class="rewards-container">';
            foreach ($bingo_rewards as $index => $reward) {
                // Get the claimed status of the current reward
                $claimed = isset($bingo_rewards_claimed[$index]) && $bingo_rewards_claimed[$index];

                echo '<div class="reward-card">';

                // Display the appropriate icon
                if ($claimed) {
                    echo '<i class="fas fa-check-circle reward-icon"></i>';
                } else {
                    echo '<i class="fas fa-question-circle reward-icon"></i>';
                }

                // Display the reward text only if claimed
                if ($claimed) {
                    echo '<span class="reward-text">' . esc_html($reward) . '</span>';
                }

                echo '</div>';
            }
            echo '</div>';
        }
        ?>


    </div>
<?php else: ?>
    <p>Bingo card not found.</p>
<?php endif; ?>

<?php get_footer(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cells = document.querySelectorAll('.bingo-cell');
        const isAuthor = <?php echo json_encode($is_author); ?>;
        cells.forEach(cell => {
            if (isAuthor) {
                cell.addEventListener('click', function() {

                    const slotIndex = this.getAttribute('data-slot-index');
                    const postId = this.getAttribute('data-post-id');
                    const isCompleted = this.classList.toggle('completed');

                    fetch(bingo_post_data.ajaxurl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'save_bingo_slot_status',
                            post_id: postId,
                            slot_index: slotIndex,
                            is_completed: isCompleted ? 1 : 0,
                        })
                    }).then(response => response.json()).then(data => {
                        if (data.success) {
                            if (data.data.reload) {
                                location.reload();
                            }
                        } else {
                            this.classList.toggle('completed', !isCompleted);
                        }
                    }).catch(err => {
                        this.classList.toggle('completed', !isCompleted);
                    });
                });
            }
        });
    });
</script>