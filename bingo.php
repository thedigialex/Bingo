<?php

/**
 * Plugin Name: Bingo 
 * Description: A plugin for creating and managing customizable bingo cards.
 * Version: 1.0
 * Author: TheDigiAlex
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-fields.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';

// Enqueue scripts and styles
function bingo_enqueue_scripts()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], null);
    $js_version = filemtime(plugin_dir_path(__FILE__) . 'js/bingo.js');
    wp_enqueue_script('bingo-script', plugin_dir_url(__FILE__) . 'js/bingo.js', ['jquery'], $js_version, true);
    wp_enqueue_style('bingo-style', plugin_dir_url(__FILE__) . 'css/bingo.css', [], time());
    wp_localize_script('bingo-script', 'bingo_post_data', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'bingo_enqueue_scripts');

// Add rewrite rules
function bingo_add_rewrite_rules()
{
    add_rewrite_rule('^bingo-card/?$', 'index.php?bingo_page=bingo-cards', 'top');
    add_rewrite_rule('^bingo-card/([^/]+)/?$', 'index.php?bingo_page=bingo-card&bingo_slug=$matches[1]', 'top');
    add_rewrite_rule('^create-card/?$', 'index.php?bingo_page=create-card', 'top');
}
add_action('init', 'bingo_add_rewrite_rules');

// Register query vars
function bingo_query_vars($vars)
{
    $vars[] = 'bingo_page';
    $vars[] = 'bingo_slug';
    return $vars;
}
add_filter('query_vars', 'bingo_query_vars');

// Flush rewrite rules on activation
function bingo_flush_rewrite_rules()
{
    bingo_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'bingo_flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

// Load custom templates
function bingo_template_include($template)
{
    $bingo_page = get_query_var('bingo_page');

    if (($bingo_page === 'bingo-cards' || $bingo_page === 'bingo-card' || $bingo_page === 'create-card') && !is_user_logged_in()) {
        wp_redirect(home_url());
        exit;
    }

    if ($bingo_page === 'bingo-cards') {
        return plugin_dir_path(__FILE__) . 'templates/bingo-cards.php';
    } elseif ($bingo_page === 'bingo-card') {
        return plugin_dir_path(__FILE__) . 'templates/bingo-card.php';
    } elseif ($bingo_page === 'create-card') {
        return plugin_dir_path(__FILE__) . 'templates/create-card.php';
    }

    return $template;
}
add_filter('template_include', 'bingo_template_include');

function bingo_save_slot_status()
{
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

    if (!empty($bingo_conditions = check_for_bingo($bingo_slots_status, $slot_index))) {
        // Retrieve rewards and claimed status
        $rewards = get_post_meta($post_id, 'bingo_rewards', true);
        if (!is_array($rewards)) {
            $rewards = [];
        }

        $rewards_claimed = get_post_meta($post_id, 'bingo_rewards_claimed', true);
        if (!is_array($rewards_claimed)) {
            $rewards_claimed = array_fill(0, count($rewards), 0);
        }
        $rewards_claimed = array_pad($rewards_claimed, count($rewards), 0);

        $claimed_rewards = [];

        // Claim a reward for each bingo achieved
        foreach ($bingo_conditions as $condition) {
            $reward_index = array_search(0, $rewards_claimed);
            if ($reward_index !== false && isset($rewards[$reward_index])) {
                $rewards_claimed[$reward_index] = 1;
                $claimed_rewards[] = $rewards[$reward_index];
            }
        }

        update_post_meta($post_id, 'bingo_rewards_claimed', $rewards_claimed);

        if (!empty($claimed_rewards)) {
            wp_send_json_success([
                'message' => 'Bingo achieved! Rewards claimed: ' . implode(', ', array_map('esc_html', $claimed_rewards)),
                'rewards' => $claimed_rewards,
                'reload' => true,
            ]);
        } else {
            wp_send_json_success([
                'message' => 'Bingo achieved! But no rewards available.',
                'reload' => false,
            ]);
        }
    }


    wp_send_json_success(['message' => 'Slot status updated.']);
}
add_action('wp_ajax_save_bingo_slot_status', 'bingo_save_slot_status');

function check_for_bingo($bingo_slots_status, $slot_index)
{
    $grid_size = 5;
    $row = intdiv($slot_index, $grid_size); // Row of the updated slot
    $column = $slot_index % $grid_size;    // Column of the updated slot

    $bingo_conditions = [];

    // Check the updated row
    $row_bingo = true;
    for ($j = 0; $j < $grid_size; $j++) {
        if (!$bingo_slots_status[$row * $grid_size + $j]) {
            $row_bingo = false;
            break;
        }
    }
    if ($row_bingo) {
        $bingo_conditions[] = "row-$row";
    }

    // Check the updated column
    $column_bingo = true;
    for ($i = 0; $i < $grid_size; $i++) {
        if (!$bingo_slots_status[$i * $grid_size + $column]) {
            $column_bingo = false;
            break;
        }
    }
    if ($column_bingo) {
        $bingo_conditions[] = "column-$column";
    }

    // Check the main diagonal (if applicable)
    $diagonal1_bingo = true;
    if ($row == $column) {
        for ($i = 0; $i < $grid_size; $i++) {
            if (!$bingo_slots_status[$i * $grid_size + $i]) {
                $diagonal1_bingo = false;
                break;
            }
        }
        if ($diagonal1_bingo) {
            $bingo_conditions[] = "diagonal-1";
        }
    }

    $diagonal2_bingo = true;
    if ($row + $column == $grid_size - 1) {
        for ($i = 0; $i < $grid_size; $i++) {
            if (!$bingo_slots_status[$i * $grid_size + ($grid_size - 1 - $i)]) {
                $diagonal2_bingo = false;
                break;
            }
        }
        if ($diagonal2_bingo) {
            $bingo_conditions[] = "diagonal-2";
        }
    }

    return $bingo_conditions; // Return all bingos achieved
}
