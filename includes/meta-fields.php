<?php
// Add custom meta box for Bingo Slots and Rewards
function bingo_add_meta_box()
{
    add_meta_box(
        'bingo_card_meta',
        __('Bingo Card Details'),
        'bingo_render_meta_box',
        'bingo_card',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bingo_add_meta_box');

// Render the Meta Box
function bingo_render_meta_box($post)
{
    // Retrieve stored meta values
    $slots = get_post_meta($post->ID, 'bingo_slots', true);
    $rewards = get_post_meta($post->ID, 'bingo_rewards', true);
    $rewards_claimed = get_post_meta($post->ID, 'bingo_rewards_claimed', true); 
    $slots_status = get_post_meta($post->ID, 'bingo_slots_status', true);

    // Use nonce for verification
    wp_nonce_field('bingo_save_meta_box', 'bingo_meta_box_nonce');

    // Render the fields for Bingo Slots
    echo '<h3>Bingo Slots</h3>';
    echo '<table>';
    for ($i = 0; $i < 5; $i++) {
        echo '<tr>';
        for ($j = 0; $j < 5; $j++) {
            $index = ($i * 5) + $j;
            $value = isset($slots[$index]) ? esc_attr($slots[$index]) : '';
            $is_completed = isset($slots_status[$index]) && $slots_status[$index];
            echo '<td>';
            echo '<input type="text" name="bingo_slots[]" value="' . $value . '" placeholder="Slot ' . ($index + 1) . '" style="width: 100%;">';
            echo '<br>';
            echo '<input type="checkbox" name="bingo_slots_status[' . $index . ']"' . ($is_completed ? ' checked' : '') . '> Completed';
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';


    echo '<h3>Rewards</h3>';
    echo '<table>';
    for ($i = 0; $i < 12; $i++) {
        $reward = isset($rewards[$i]) ? esc_attr($rewards[$i]) : '';
        $claimed = isset($rewards_claimed[$i]) && $rewards_claimed[$i];
        echo '<td>';
        echo '<input type="text" name="bingo_rewards[]" value="' . $reward . '" placeholder="Reward ' . ($i + 1) . '" style="width: 100%;">';
        echo '<br>';
        echo '<input type="checkbox" name="bingo_rewards_claimed[' . $i . ']"' . ($claimed ? ' checked' : '') . '> Claimed';
        echo '</td>';
    }
    echo '</table>';
}

// Save the Meta Box Data
function bingo_save_meta_box($post_id)
{
    // Check nonce for security
    if (!isset($_POST['bingo_meta_box_nonce']) || !wp_verify_nonce($_POST['bingo_meta_box_nonce'], 'bingo_save_meta_box')) {
        return;
    }

    // Check for auto-save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (isset($_POST['post_type']) && $_POST['post_type'] === 'bingo_card') {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Save Bingo Slots
    if (isset($_POST['bingo_slots']) && is_array($_POST['bingo_slots'])) {
        $slots = array_map('sanitize_text_field', $_POST['bingo_slots']);
        update_post_meta($post_id, 'bingo_slots', $slots);
    }

    // Save Bingo Slots Completion Status
    if (isset($_POST['bingo_slots_status']) && is_array($_POST['bingo_slots_status'])) {
        $slots_status = $_POST['bingo_slots_status'];
        update_post_meta($post_id, 'bingo_slots_status', $slots_status);
    }

    // Save Rewards
    if (isset($_POST['bingo_rewards']) && is_array($_POST['bingo_rewards'])) {
        $rewards = array_map('sanitize_text_field', $_POST['bingo_rewards']);
        update_post_meta($post_id, 'bingo_rewards', $rewards);
    }

    // Save Rewards Claim Status (No need to sanitize checkboxes, store as 1 or 0)
    if (isset($_POST['bingo_rewards_claimed']) && is_array($_POST['bingo_rewards_claimed'])) {
        $rewards_claimed = $_POST['bingo_rewards_claimed'];
        update_post_meta($post_id, 'bingo_rewards_claimed', $rewards_claimed);
    }
}
add_action('save_post', 'bingo_save_meta_box');

