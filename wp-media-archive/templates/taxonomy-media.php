<?php get_header(); ?>

<div class="wpma-archive-wrap">
    <header class="wpma-archive-header">
        <h1 class="wpma-archive-title">
            <?php echo esc_html(single_term_title('', false)); ?>
        </h1>
        <?php if (term_description()): ?>
            <div class="wpma-archive-description"><?php echo wp_kses_post(term_description()); ?></div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()): ?>
    <div class="wpma-grid wpma-columns-3">
        <?php while (have_posts()): the_post(); ?>
            <?php
            $postId   = get_the_ID();
            $fileId   = get_post_meta($postId, '_wpma_file_id', true);
            $mimeType = $fileId ? get_post_mime_type((int) $fileId) : '';
            $isAudio  = str_starts_with($mimeType, 'audio/');
            $isImage  = str_starts_with($mimeType, 'image/');
            $author   = get_post_meta($postId, '_wpma_author', true);
            $tags     = get_the_terms($postId, 'media_tag');
            ?>
            <div class="wpma-card <?php echo $isAudio ? 'wpma-card--audio' : 'wpma-card--image'; ?>">
                <a href="<?php the_permalink(); ?>" class="wpma-card__link">
                    <div class="wpma-card__media">
                        <?php if ($isImage && $fileId): ?>
                            <img src="<?php echo esc_url(wp_get_attachment_image_url((int) $fileId, 'medium')); ?>" class="wpma-card__img" alt="<?php the_title_attribute(); ?>">
                        <?php elseif ($isAudio): ?>
                            <div class="wpma-card__audio-icon">
                                <svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor">
                                    <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="wpma-card__body">
                        <h3 class="wpma-card__title"><?php the_title(); ?></h3>
                        <?php if ($author): ?>
                            <p class="wpma-card__meta"><?php echo esc_html($author); ?></p>
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
        <?php endwhile; ?>
    </div>

    <div class="wpma-pagination">
        <?php the_posts_pagination([
            'mid_size'  => 2,
            'prev_text' => '&laquo; ' . __('Zurück', 'wp-media-archive'),
            'next_text' => __('Weiter', 'wp-media-archive') . ' &raquo;',
        ]); ?>
    </div>

    <?php else: ?>
        <p class="wpma-no-results"><?php esc_html_e('Keine Medieneinträge gefunden.', 'wp-media-archive'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
