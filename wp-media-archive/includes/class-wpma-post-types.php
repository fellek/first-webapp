<?php

if (! defined('ABSPATH')) {
    exit;
}

class WPMA_Post_Types
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        $labels = [
            'name'               => __('Medienarchiv', 'wp-media-archive'),
            'singular_name'      => __('Medieneintrag', 'wp-media-archive'),
            'menu_name'          => __('Medienarchiv', 'wp-media-archive'),
            'add_new'            => __('Neuen Eintrag erstellen', 'wp-media-archive'),
            'add_new_item'       => __('Neuen Medieneintrag erstellen', 'wp-media-archive'),
            'edit_item'          => __('Medieneintrag bearbeiten', 'wp-media-archive'),
            'new_item'           => __('Neuer Medieneintrag', 'wp-media-archive'),
            'view_item'          => __('Medieneintrag ansehen', 'wp-media-archive'),
            'search_items'       => __('Medieneinträge durchsuchen', 'wp-media-archive'),
            'not_found'          => __('Keine Medieneinträge gefunden', 'wp-media-archive'),
            'not_found_in_trash' => __('Keine Medieneinträge im Papierkorb', 'wp-media-archive'),
            'all_items'          => __('Alle Einträge', 'wp-media-archive'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_rest'        => true,
            'query_var'           => true,
            'rewrite'             => ['slug' => 'medienarchiv', 'with_front' => false],
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-format-gallery',
            'supports'            => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'taxonomies'          => ['media_tag', 'media_category', 'media_type'],
        ];

        register_post_type('media_archive', $args);
    }
}
