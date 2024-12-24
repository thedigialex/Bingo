# Bingo Game with Rewards

This is a simple bingo game that allows users to mark bingo slots and claim rewards upon achieving a bingo. It is built with PHP (WordPress), JavaScript, and AJAX to provide real-time interactivity.

## Features
- **Bingo Grid:** A 5x5 bingo grid where users can mark their slots.
- **Rewards System:** Users can claim rewards when they achieve bingo.
- **AJAX Interactivity:** The game grid updates dynamically without page reloads.
- **Page Reload:** After claiming a reward, the page automatically reloads to reflect the changes.

## Requirements
- WordPress (for the backend).
- Basic knowledge of PHP, JavaScript, and AJAX.
- A theme or plugin in which this code can be integrated.

## Installation
1. Clone or download the repository.
2. Place the code inside your WordPress theme or plugin directory.
3. Ensure that you have the appropriate hooks to load the `bingo_save_slot_status` function via `wp_ajax_save_bingo_slot_status` in WordPress.
4. Add the appropriate CSS to style the bingo grid and cells.
5. Ensure WordPress AJAX is set up for handling requests.

## Usage

### Bingo Grid Setup:
- The bingo grid is rendered on the front-end with HTML, and each cell is clickable. 
- The grid uses the `bingo_cell` class to mark and track the completion of each slot.
- The grid data (e.g., whether a slot is completed) is saved to post metadata in WordPress.

### Marking Slots:
- When a user clicks on a bingo cell, it toggles its completion status and sends an AJAX request to save the updated status.

### Claiming Rewards:
- If the user achieves a bingo (either horizontally, vertically, or diagonally), the system checks for unclaimed rewards.
- If a reward is available, it is claimed, and the system marks it as claimed.

### Page Reload:
- Once a bingo is achieved and a reward is claimed, the page reloads automatically to reflect the updated state.

## Example Code Snippets

### PHP Code for Saving Bingo Slot Status and Checking for Bingo

```php
function bingo_save_slot_status() {
    // Validate required POST parameters
    if (!isset($_POST['post_id'], $_POST['slot_index'], $_POST['is_completed'])) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $post_id = intval($_POST['post_id']);
    $slot_index = intval($_POST['slot_index']);
    $is_completed = intval($_POST['is_completed']);

    // Retrieve current bingo slots status
    $bingo_slots_status = get_post_meta($post_id, 'bingo_slots_status', true);
    if (!is_array($bingo_slots_status)) {
        $bingo_slots_status = array_fill(0, 25, 0); // Initialize 5x5 grid
    }

    // Update the slot status
    $bingo_slots_status[$slot_index] = $is_completed;
    update_post_meta($post_id, 'bingo_slots_status', $bingo_slots_status);

    // Check if there is a bingo
    if (check_for_bingo($bingo_slots_status)) {
        // Retrieve available rewards
        $rewards = get_post_meta($post_id, 'bingo_rewards', true);
        if (!is_array($rewards)) {
            $rewards = []; // Initialize empty rewards array if none exists
        }

        // Retrieve or initialize rewards claimed
        $rewards_claimed = get_post_meta($post_id, 'bingo_rewards_claimed', true);
        if (!is_array($rewards_claimed)) {
            $rewards_claimed = array_fill(0, count($rewards), 0); // Match the rewards array size
        }

        // Ensure rewards_claimed matches the rewards array length
        $rewards_claimed = array_pad($rewards_claimed, count($rewards), 0);

        // Find an unclaimed reward
        $reward_index = array_search(0, $rewards_claimed);
        if ($reward_index !== false && isset($rewards[$reward_index])) {
            // Mark reward as claimed
            $rewards_claimed[$reward_index] = 1;
            update_post_meta($post_id, 'bingo_rewards_claimed', $rewards_claimed);

            wp_send_json_success([
                'message' => 'Bingo achieved! Reward claimed: ' . esc_html($rewards[$reward_index]),
                'reward' => $rewards[$reward_index],
                'reload' => true // Add reload flag
            ]);
        } else {
            wp_send_json_success([
                'message' => 'Bingo achieved! But no rewards available.',
                'reload' => true // Add reload flag
            ]);
        }
    }

    wp_send_json_success(['message' => 'Slot status updated.']);
}
add_action('wp_ajax_save_bingo_slot_status', 'bingo_save_slot_status');
