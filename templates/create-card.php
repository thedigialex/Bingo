<?php
if (!defined('ABSPATH')) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bingo_nonce']) && wp_verify_nonce($_POST['bingo_nonce'], 'create_bingo_card')) {
    $title = sanitize_text_field($_POST['bingo_card_title']);
    $slots = array_map('sanitize_text_field', $_POST['bingo_slots']);
    $rewards = array_map('sanitize_text_field', $_POST['bingo_rewards']);

    shuffle($slots);
    shuffle($rewards);

    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_type' => 'bingo_card',
        'post_status' => 'publish'
    ]);

    if ($post_id) {
        update_post_meta($post_id, 'bingo_slots', $slots);
        update_post_meta($post_id, 'bingo_rewards', $rewards);

        echo '<div class="notice notice-success"><p>Bingo Card created successfully!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Failed to create Bingo Card.</p></div>';
    }
}

get_header();
?>

<div class="bingo-content">
    <div class="bingo-header">
        <h1>Create a Bingo Card</h1>
    </div>
    <form method="post">
        <?php wp_nonce_field('create_bingo_card', 'bingo_nonce'); ?>

        <div>
            <label for="bingo_card_title"><strong>Bingo Card Title:</strong></label>
            <input type="text" name="bingo_card_title" id="bingo_card_title" class="regular-text" required>
        </div>

        <h3>Bingo Slots</h3>
        <p>Enter the 25 values for the bingo card slots:</p>
        <table>
            <?php for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <?php for ($j = 0; $j < 5; $j++): ?>
                        <td>
                            <input
                                type="text"
                                name="bingo_slots[]"
                                placeholder="Slot <?php echo ($i * 5) + $j + 1; ?>"
                                required>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endfor; ?>
        </table>

        <h3>Rewards</h3>
        <p>Enter the 12 rewards for the bingo game:</p>
        <table>
            <?php for ($i = 0; $i < 12; $i++): ?>
                <tr>
                    <td>
                        <input
                            type="text"
                            name="bingo_rewards[]"
                            placeholder="Reward <?php echo $i + 1; ?>"
                            required>
                    </td>
                </tr>
            <?php endfor; ?>
        </table>

        <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="bingo-card">Create Bingo Card</button>
        </div>
        <br>
    </form>
</div>

<?php get_footer(); ?>