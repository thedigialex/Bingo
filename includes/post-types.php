<?php
function bingo_register_post_type() {
    register_post_type('bingo_card', [
        'labels' => [
            'name' => __('Bingo Cards'),
            'singular_name' => __('Bingo Card'),
            'add_new_item' => __('Add New Bingo Card'),
            'edit_item' => __('Edit Bingo Card'),
            'new_item' => __('New Bingo Card'),
            'view_item' => __('View Bingo Card'),
            'search_items' => __('Search Bingo Cards'),
            'not_found' => __('No Bingo Cards Found'),
            'not_found_in_trash' => __('No Bingo Cards Found in Trash'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'bingo-cards'],
        'supports' => ['title', 'editor', 'custom-fields'],
        'show_in_rest' => false, 
        'menu_icon' => 'dashicons-grid-view', 
        'capability_type' => 'post',
    ]);
}
add_action('init', 'bingo_register_post_type');
