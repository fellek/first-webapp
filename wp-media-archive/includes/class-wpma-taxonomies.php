<?php

if (! defined('ABSPATH')) {
    exit;
}

class WPMA_Taxonomies
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        $this->registerMediaTags();
        $this->registerMediaCategories();
        $this->registerMediaType();
    }

    private function registerMediaTags(): void
    {
        $labels = [
            'name'              => __('Medien-Tags', 'wp-media-archive'),
            'singular_name'     => __('Medien-Tag', 'wp-media-archive'),
            'search_items'      => __('Tags durchsuchen', 'wp-media-archive'),
            'all_items'         => __('Alle Tags', 'wp-media-archive'),
            'edit_item'         => __('Tag bearbeiten', 'wp-media-archive'),
            'update_item'       => __('Tag aktualisieren', 'wp-media-archive'),
            'add_new_item'      => __('Neuen Tag hinzufügen', 'wp-media-archive'),
            'new_item_name'     => __('Neuer Tag-Name', 'wp-media-archive'),
            'menu_name'         => __('Tags', 'wp-media-archive'),
        ];

        register_taxonomy('media_tag', 'media_archive', [
            'labels'            => $labels,
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'medien-tag'],
        ]);
    }

    private function registerMediaCategories(): void
    {
        $labels = [
            'name'              => __('Medien-Kategorien', 'wp-media-archive'),
            'singular_name'     => __('Medien-Kategorie', 'wp-media-archive'),
            'search_items'      => __('Kategorien durchsuchen', 'wp-media-archive'),
            'all_items'         => __('Alle Kategorien', 'wp-media-archive'),
            'parent_item'       => __('Übergeordnete Kategorie', 'wp-media-archive'),
            'parent_item_colon' => __('Übergeordnete Kategorie:', 'wp-media-archive'),
            'edit_item'         => __('Kategorie bearbeiten', 'wp-media-archive'),
            'update_item'       => __('Kategorie aktualisieren', 'wp-media-archive'),
            'add_new_item'      => __('Neue Kategorie hinzufügen', 'wp-media-archive'),
            'new_item_name'     => __('Neuer Kategoriename', 'wp-media-archive'),
            'menu_name'         => __('Kategorien', 'wp-media-archive'),
        ];

        register_taxonomy('media_category', 'media_archive', [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'medien-kategorie'],
        ]);
    }

    private function registerMediaType(): void
    {
        $labels = [
            'name'              => __('Medientyp', 'wp-media-archive'),
            'singular_name'     => __('Medientyp', 'wp-media-archive'),
            'search_items'      => __('Medientypen durchsuchen', 'wp-media-archive'),
            'all_items'         => __('Alle Medientypen', 'wp-media-archive'),
            'edit_item'         => __('Medientyp bearbeiten', 'wp-media-archive'),
            'update_item'       => __('Medientyp aktualisieren', 'wp-media-archive'),
            'add_new_item'      => __('Neuen Medientyp hinzufügen', 'wp-media-archive'),
            'new_item_name'     => __('Neuer Medientypname', 'wp-media-archive'),
            'menu_name'         => __('Medientyp', 'wp-media-archive'),
        ];

        register_taxonomy('media_type', 'media_archive', [
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'medientyp'],
        ]);

        // Register default media types on activation
        if (! term_exists('Bild', 'media_type')) {
            wp_insert_term(__('Bild', 'wp-media-archive'), 'media_type', [
                'slug' => 'bild',
                'description' => __('Bilder und Fotografien', 'wp-media-archive'),
            ]);
        }
        if (! term_exists('Tonaufnahme', 'media_type')) {
            wp_insert_term(__('Tonaufnahme', 'wp-media-archive'), 'media_type', [
                'slug' => 'tonaufnahme',
                'description' => __('Audio- und Tonaufnahmen', 'wp-media-archive'),
            ]);
        }
    }
}
