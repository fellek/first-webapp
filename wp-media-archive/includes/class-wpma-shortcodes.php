<?php

if (! defined('ABSPATH')) {
    exit;
}

class WPMA_Shortcodes
{
    public function __construct()
    {
        add_shortcode('medienarchiv', [$this, 'renderArchive']);
        add_shortcode('medienarchiv_suche', [$this, 'renderSearch']);
    }

    public function renderArchive(array $atts = []): string
    {
        $atts = shortcode_atts([
            'typ'       => '',
            'kategorie' => '',
            'tag'       => '',
            'anzahl'    => 12,
            'spalten'   => 3,
            'sortierung' => 'date',
            'reihenfolge' => 'DESC',
        ], $atts, 'medienarchiv');

        $args = [
            'post_type'      => 'media_archive',
            'posts_per_page' => (int) $atts['anzahl'],
            'orderby'        => sanitize_text_field($atts['sortierung']),
            'order'          => $atts['reihenfolge'] === 'ASC' ? 'ASC' : 'DESC',
        ];

        $taxQuery = [];
        if (! empty($atts['typ'])) {
            $taxQuery[] = [
                'taxonomy' => 'media_type',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['typ'])),
            ];
        }
        if (! empty($atts['kategorie'])) {
            $taxQuery[] = [
                'taxonomy' => 'media_category',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['kategorie'])),
            ];
        }
        if (! empty($atts['tag'])) {
            $taxQuery[] = [
                'taxonomy' => 'media_tag',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['tag'])),
            ];
        }
        if (! empty($taxQuery)) {
            $taxQuery['relation'] = 'AND';
            $args['tax_query']    = $taxQuery;
        }

        $query = new WP_Query($args);

        ob_start();
        echo '<div class="wpma-grid wpma-columns-' . (int) $atts['spalten'] . '">';

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->renderCard(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<p class="wpma-no-results">' . esc_html__('Keine Medieneinträge gefunden.', 'wp-media-archive') . '</p>';
        }

        echo '</div>';
        return ob_get_clean();
    }

    public function renderSearch(array $atts = []): string
    {
        $atts = shortcode_atts([
            'placeholder' => __('Medienarchiv durchsuchen...', 'wp-media-archive'),
        ], $atts, 'medienarchiv_suche');

        ob_start();
        ?>
        <div class="wpma-search-form">
            <form role="search" method="get" action="<?php echo esc_url(get_post_type_archive_link('media_archive')); ?>">
                <div class="wpma-search-row">
                    <input type="search" name="s" class="wpma-search-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
                    <input type="hidden" name="post_type" value="media_archive">

                    <div class="wpma-filter-row">
                        <?php
                        $this->renderTaxonomyFilter('media_type', __('Alle Medientypen', 'wp-media-archive'));
                        $this->renderTaxonomyFilter('media_category', __('Alle Kategorien', 'wp-media-archive'));
                        ?>
                    </div>

                    <button type="submit" class="wpma-search-btn"><?php esc_html_e('Suchen', 'wp-media-archive'); ?></button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderTaxonomyFilter(string $taxonomy, string $defaultLabel): void
    {
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true]);
        if (empty($terms) || is_wp_error($terms)) {
            return;
        }
        $selected = isset($_GET[$taxonomy]) ? sanitize_text_field(wp_unslash($_GET[$taxonomy])) : '';
        echo '<select name="' . esc_attr($taxonomy) . '" class="wpma-filter-select">';
        echo '<option value="">' . esc_html($defaultLabel) . '</option>';
        foreach ($terms as $term) {
            echo '<option value="' . esc_attr($term->slug) . '"' . selected($selected, $term->slug, false) . '>';
            echo esc_html($term->name);
            echo '</option>';
        }
        echo '</select>';
    }

    private function renderCard(int $postId): void
    {
        $fileId   = get_post_meta($postId, '_wpma_file_id', true);
        $mimeType = $fileId ? get_post_mime_type((int) $fileId) : '';
        $isAudio  = str_starts_with($mimeType, 'audio/');
        $isImage  = str_starts_with($mimeType, 'image/');
        $author   = get_post_meta($postId, '_wpma_author', true);
        $date     = get_post_meta($postId, '_wpma_date', true);
        $tags     = get_the_terms($postId, 'media_tag');
        ?>
        <div class="wpma-card <?php echo $isAudio ? 'wpma-card--audio' : 'wpma-card--image'; ?>">
            <a href="<?php the_permalink(); ?>" class="wpma-card__link">
                <div class="wpma-card__media">
                    <?php if ($isImage && has_post_thumbnail($postId)): ?>
                        <?php echo get_the_post_thumbnail($postId, 'medium', ['class' => 'wpma-card__img']); ?>
                    <?php elseif ($isImage && $fileId): ?>
                        <img src="<?php echo esc_url(wp_get_attachment_image_url((int) $fileId, 'medium')); ?>" class="wpma-card__img" alt="<?php the_title_attribute(); ?>">
                    <?php elseif ($isAudio): ?>
                        <div class="wpma-card__audio-icon">
                            <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor">
                                <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                            </svg>
                        </div>
                    <?php else: ?>
                        <div class="wpma-card__placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="wpma-card__body">
                    <h3 class="wpma-card__title"><?php the_title(); ?></h3>
                    <?php if ($author): ?>
                        <p class="wpma-card__meta"><?php echo esc_html($author); ?></p>
                    <?php endif; ?>
                    <?php if ($date): ?>
                        <p class="wpma-card__date"><?php echo esc_html($date); ?></p>
                    <?php endif; ?>
                    <?php if ($tags && ! is_wp_error($tags)): ?>
                        <div class="wpma-card__tags">
                            <?php foreach ($tags as $tag): ?>
                                <span class="wpma-tag"><?php echo esc_html($tag->name); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php
    }
}
