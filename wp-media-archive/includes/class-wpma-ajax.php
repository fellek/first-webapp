<?php

if (! defined('ABSPATH')) {
    exit;
}

class WPMA_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_wpma_filter', [$this, 'filterMedia']);
        add_action('wp_ajax_nopriv_wpma_filter', [$this, 'filterMedia']);
    }

    public function filterMedia(): void
    {
        check_ajax_referer('wpma_nonce', 'nonce');

        $paged    = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
        $search   = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $type     = isset($_POST['media_type']) ? sanitize_text_field(wp_unslash($_POST['media_type'])) : '';
        $category = isset($_POST['media_category']) ? sanitize_text_field(wp_unslash($_POST['media_category'])) : '';
        $tag      = isset($_POST['media_tag']) ? sanitize_text_field(wp_unslash($_POST['media_tag'])) : '';

        $args = [
            'post_type'      => 'media_archive',
            'posts_per_page' => 12,
            'paged'          => $paged,
            'post_status'    => 'publish',
        ];

        if ($search) {
            $args['s'] = $search;
        }

        $taxQuery = [];
        if ($type) {
            $taxQuery[] = ['taxonomy' => 'media_type', 'field' => 'slug', 'terms' => $type];
        }
        if ($category) {
            $taxQuery[] = ['taxonomy' => 'media_category', 'field' => 'slug', 'terms' => $category];
        }
        if ($tag) {
            $taxQuery[] = ['taxonomy' => 'media_tag', 'field' => 'slug', 'terms' => $tag];
        }
        if (! empty($taxQuery)) {
            $taxQuery['relation'] = 'AND';
            $args['tax_query']    = $taxQuery;
        }

        $query = new WP_Query($args);
        $items = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $postId   = get_the_ID();
                $fileId   = get_post_meta($postId, '_wpma_file_id', true);
                $mimeType = $fileId ? get_post_mime_type((int) $fileId) : '';
                $thumbUrl = '';

                if (str_starts_with($mimeType, 'image/') && $fileId) {
                    $thumbUrl = wp_get_attachment_image_url((int) $fileId, 'medium');
                }

                $tags = get_the_terms($postId, 'media_tag');
                $tagNames = ($tags && ! is_wp_error($tags)) ? wp_list_pluck($tags, 'name') : [];

                $items[] = [
                    'id'        => $postId,
                    'title'     => get_the_title(),
                    'permalink' => get_permalink(),
                    'thumbnail' => $thumbUrl ?: '',
                    'isAudio'   => str_starts_with($mimeType, 'audio/'),
                    'author'    => get_post_meta($postId, '_wpma_author', true),
                    'tags'      => $tagNames,
                ];
            }
            wp_reset_postdata();
        }

        wp_send_json_success([
            'items'      => $items,
            'totalPages' => $query->max_num_pages,
            'total'      => $query->found_posts,
        ]);
    }
}
