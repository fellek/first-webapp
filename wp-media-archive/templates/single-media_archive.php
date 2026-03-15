<?php get_header(); ?>

<?php while (have_posts()): the_post(); ?>
<?php
$postId    = get_the_ID();
$fileId    = get_post_meta($postId, '_wpma_file_id', true);
$fileUrl   = $fileId ? wp_get_attachment_url((int) $fileId) : '';
$mimeType  = $fileId ? get_post_mime_type((int) $fileId) : '';
$isAudio   = str_starts_with($mimeType, 'audio/');
$isImage   = str_starts_with($mimeType, 'image/');

$author    = get_post_meta($postId, '_wpma_author', true);
$date      = get_post_meta($postId, '_wpma_date', true);
$location  = get_post_meta($postId, '_wpma_location', true);
$copyright = get_post_meta($postId, '_wpma_copyright', true);
$duration  = get_post_meta($postId, '_wpma_duration', true);
$desc      = get_post_meta($postId, '_wpma_description', true);

$tags       = get_the_terms($postId, 'media_tag');
$categories = get_the_terms($postId, 'media_category');
$types      = get_the_terms($postId, 'media_type');
?>

<article class="wpma-single">
    <header class="wpma-single__header">
        <h1 class="wpma-single__title"><?php the_title(); ?></h1>

        <div class="wpma-single__meta-bar">
            <?php if ($types && ! is_wp_error($types)): ?>
                <span class="wpma-single__type">
                    <?php echo esc_html(implode(', ', wp_list_pluck($types, 'name'))); ?>
                </span>
            <?php endif; ?>
            <?php if ($author): ?>
                <span class="wpma-single__author"><?php echo esc_html($author); ?></span>
            <?php endif; ?>
            <?php if ($date): ?>
                <span class="wpma-single__date"><?php echo esc_html($date); ?></span>
            <?php endif; ?>
        </div>
    </header>

    <div class="wpma-single__content">
        <div class="wpma-single__media">
            <?php if ($isImage && $fileUrl): ?>
                <figure class="wpma-single__figure">
                    <img src="<?php echo esc_url($fileUrl); ?>" alt="<?php the_title_attribute(); ?>" class="wpma-single__img">
                    <?php if ($copyright): ?>
                        <figcaption class="wpma-single__caption">&copy; <?php echo esc_html($copyright); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php elseif ($isAudio && $fileUrl): ?>
                <div class="wpma-single__player">
                    <audio controls preload="metadata" class="wpma-audio-player">
                        <source src="<?php echo esc_url($fileUrl); ?>" type="<?php echo esc_attr($mimeType); ?>">
                        <?php esc_html_e('Ihr Browser unterstützt kein Audio.', 'wp-media-archive'); ?>
                    </audio>
                    <?php if ($duration): ?>
                        <p class="wpma-single__duration"><?php esc_html_e('Dauer:', 'wp-media-archive'); ?> <?php echo esc_html($duration); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="wpma-single__details">
            <?php the_content(); ?>

            <?php if ($desc): ?>
                <div class="wpma-single__description">
                    <h3><?php esc_html_e('Beschreibung', 'wp-media-archive'); ?></h3>
                    <p><?php echo nl2br(esc_html($desc)); ?></p>
                </div>
            <?php endif; ?>

            <dl class="wpma-single__info">
                <?php if ($location): ?>
                    <dt><?php esc_html_e('Aufnahmeort', 'wp-media-archive'); ?></dt>
                    <dd><?php echo esc_html($location); ?></dd>
                <?php endif; ?>
                <?php if ($copyright): ?>
                    <dt><?php esc_html_e('Copyright', 'wp-media-archive'); ?></dt>
                    <dd><?php echo esc_html($copyright); ?></dd>
                <?php endif; ?>
                <?php if ($categories && ! is_wp_error($categories)): ?>
                    <dt><?php esc_html_e('Kategorien', 'wp-media-archive'); ?></dt>
                    <dd>
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="wpma-category-link"><?php echo esc_html($cat->name); ?></a>
                        <?php endforeach; ?>
                    </dd>
                <?php endif; ?>
            </dl>

            <?php if ($tags && ! is_wp_error($tags)): ?>
                <div class="wpma-single__tags">
                    <h3><?php esc_html_e('Tags', 'wp-media-archive'); ?></h3>
                    <div class="wpma-tag-list">
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="wpma-tag"><?php echo esc_html($tag->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <nav class="wpma-single__nav">
        <?php
        $prev = get_previous_post();
        $next = get_next_post();
        ?>
        <?php if ($prev): ?>
            <a href="<?php echo esc_url(get_permalink($prev)); ?>" class="wpma-nav-link wpma-nav-link--prev">
                &laquo; <?php echo esc_html(get_the_title($prev)); ?>
            </a>
        <?php endif; ?>
        <a href="<?php echo esc_url(get_post_type_archive_link('media_archive')); ?>" class="wpma-nav-link wpma-nav-link--archive">
            <?php esc_html_e('Zum Archiv', 'wp-media-archive'); ?>
        </a>
        <?php if ($next): ?>
            <a href="<?php echo esc_url(get_permalink($next)); ?>" class="wpma-nav-link wpma-nav-link--next">
                <?php echo esc_html(get_the_title($next)); ?> &raquo;
            </a>
        <?php endif; ?>
    </nav>
</article>

<?php endwhile; ?>

<?php get_footer(); ?>
