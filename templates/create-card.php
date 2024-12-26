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

        wp_redirect('/bingo/bingo-card');
        exit;
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
            <h3>Bingo Card Title:</h3>
            <input type="text" name="bingo_card_title" id="bingo_card_title" class="regular-text" required>
        </div>

        <br>
        <h3>Bingo Slots</h3>
        <p>Enter the 25 values for the bingo card slots:</p>

        <button class="accordion bingo-button" style="width: 100%;">Tips for Bingo Slots</button>
        <div class="panel" style="display: none;">
            <p>Achieving a big, ambitious goal can feel overwhelming, but breaking it down into smaller, manageable tasks makes the journey more achievable. By focusing on one step at a time, you'll build momentum and make steady progress toward reaching your ultimate objective, no matter how challenging it may seem.</p>
            <p><strong>Goal:</strong> 'Be More Active'</p>
            <ul>
                <li>Go on a hike</li>
                <li>Go on a hike 5 times</li>
                <li>Go on a hike 10 times</li>
            </ul>
        </div>

        <table>
            <?php for ($i = 0; $i < 25; $i++): ?>
                <tr>
                    <td>
                        <input
                            type="text"
                            name="bingo_slots[]"
                            placeholder="Slot <?php echo $i + 1; ?>"
                            required>
                    </td>
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
            <button type="submit" class="bingo-button">Create Bingo Card</button>
        </div>
        <br>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var acc = document.getElementsByClassName("accordion");
        for (var i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            });
        }
    });
</script>
<?php get_footer(); ?>