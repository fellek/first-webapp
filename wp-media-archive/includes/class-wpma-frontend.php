<?php

if (! defined('ABSPATH')) {
    exit;
}

class WPMA_Frontend
{
    public function __construct()
    {
        add_filter('template_include', [$this, 'loadTemplates']);
    }

    public function loadTemplates(string $template): string
    {
        if (is_post_type_archive('media_archive')) {
            $customTemplate = WPMA_PLUGIN_DIR . 'templates/archive-media_archive.php';
            if (file_exists($customTemplate)) {
                return $customTemplate;
            }
        }

        if (is_singular('media_archive')) {
            $customTemplate = WPMA_PLUGIN_DIR . 'templates/single-media_archive.php';
            if (file_exists($customTemplate)) {
                return $customTemplate;
            }
        }

        if (is_tax('media_tag') || is_tax('media_category') || is_tax('media_type')) {
            $customTemplate = WPMA_PLUGIN_DIR . 'templates/taxonomy-media.php';
            if (file_exists($customTemplate)) {
                return $customTemplate;
            }
        }

        return $template;
    }
}
