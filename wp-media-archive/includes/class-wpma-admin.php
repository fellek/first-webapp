<?php

if (! defined('ABSPATH')) {
    exit;
}

class WPMA_Admin
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_media_archive', [$this, 'saveMetaBoxes']);
        add_filter('manage_media_archive_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_media_archive_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
    }

    public function addMetaBoxes(): void
    {
        add_meta_box(
            'wpma_media_details',
            __('Medien-Details', 'wp-media-archive'),
            [$this, 'renderMediaDetailsBox'],
            'media_archive',
            'normal',
            'high'
        );

        add_meta_box(
            'wpma_media_file',
            __('Mediendatei', 'wp-media-archive'),
            [$this, 'renderMediaFileBox'],
            'media_archive',
            'side',
            'high'
        );
    }

    public function renderMediaDetailsBox(\WP_Post $post): void
    {
        wp_nonce_field('wpma_save_meta', 'wpma_meta_nonce');

        $author      = get_post_meta($post->ID, '_wpma_author', true);
        $date        = get_post_meta($post->ID, '_wpma_date', true);
        $location    = get_post_meta($post->ID, '_wpma_location', true);
        $copyright   = get_post_meta($post->ID, '_wpma_copyright', true);
        $duration    = get_post_meta($post->ID, '_wpma_duration', true);
        $description = get_post_meta($post->ID, '_wpma_description', true);
        ?>
        <table class="form-table wpma-meta-table">
            <tr>
                <th><label for="wpma_author"><?php esc_html_e('Autor / Urheber', 'wp-media-archive'); ?></label></th>
                <td><input type="text" id="wpma_author" name="wpma_author" value="<?php echo esc_attr($author); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="wpma_date"><?php esc_html_e('Aufnahmedatum', 'wp-media-archive'); ?></label></th>
                <td><input type="date" id="wpma_date" name="wpma_date" value="<?php echo esc_attr($date); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="wpma_location"><?php esc_html_e('Aufnahmeort', 'wp-media-archive'); ?></label></th>
                <td><input type="text" id="wpma_location" name="wpma_location" value="<?php echo esc_attr($location); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="wpma_copyright"><?php esc_html_e('Copyright / Lizenz', 'wp-media-archive'); ?></label></th>
                <td><input type="text" id="wpma_copyright" name="wpma_copyright" value="<?php echo esc_attr($copyright); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="wpma_duration"><?php esc_html_e('Dauer (Audio)', 'wp-media-archive'); ?></label></th>
                <td><input type="text" id="wpma_duration" name="wpma_duration" value="<?php echo esc_attr($duration); ?>" class="regular-text" placeholder="z.B. 03:45"></td>
            </tr>
            <tr>
                <th><label for="wpma_description"><?php esc_html_e('Detailbeschreibung', 'wp-media-archive'); ?></label></th>
                <td><textarea id="wpma_description" name="wpma_description" rows="4" class="large-text"><?php echo esc_textarea($description); ?></textarea></td>
            </tr>
        </table>
        <?php
    }

    public function renderMediaFileBox(\WP_Post $post): void
    {
        $fileId  = get_post_meta($post->ID, '_wpma_file_id', true);
        $fileUrl = $fileId ? wp_get_attachment_url((int) $fileId) : '';
        $fileType = $fileId ? get_post_mime_type((int) $fileId) : '';
        ?>
        <div class="wpma-file-upload">
            <input type="hidden" id="wpma_file_id" name="wpma_file_id" value="<?php echo esc_attr($fileId); ?>">

            <div id="wpma-file-preview" class="wpma-file-preview">
                <?php if ($fileUrl && str_starts_with($fileType, 'image/')): ?>
                    <img src="<?php echo esc_url($fileUrl); ?>" style="max-width:100%;height:auto;">
                <?php elseif ($fileUrl && str_starts_with($fileType, 'audio/')): ?>
                    <audio controls style="width:100%;">
                        <source src="<?php echo esc_url($fileUrl); ?>" type="<?php echo esc_attr($fileType); ?>">
                    </audio>
                <?php elseif ($fileUrl): ?>
                    <p><?php echo esc_html(basename($fileUrl)); ?></p>
                <?php else: ?>
                    <p class="wpma-no-file"><?php esc_html_e('Keine Datei ausgewählt', 'wp-media-archive'); ?></p>
                <?php endif; ?>
            </div>

            <p>
                <button type="button" class="button button-primary" id="wpma-upload-btn">
                    <?php esc_html_e('Datei auswählen', 'wp-media-archive'); ?>
                </button>
                <button type="button" class="button" id="wpma-remove-btn" <?php echo $fileId ? '' : 'style="display:none;"'; ?>>
                    <?php esc_html_e('Entfernen', 'wp-media-archive'); ?>
                </button>
            </p>
        </div>
        <?php
    }

    public function saveMetaBoxes(int $postId): void
    {
        if (! isset($_POST['wpma_meta_nonce']) || ! wp_verify_nonce($_POST['wpma_meta_nonce'], 'wpma_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $postId)) {
            return;
        }

        $fields = ['wpma_author', 'wpma_date', 'wpma_location', 'wpma_copyright', 'wpma_duration', 'wpma_description', 'wpma_file_id'];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = $field === 'wpma_description'
                    ? sanitize_textarea_field(wp_unslash($_POST[$field]))
                    : sanitize_text_field(wp_unslash($_POST[$field]));
                update_post_meta($postId, '_' . $field, $value);
            }
        }
    }

    public function addCustomColumns(array $columns): array
    {
        $newColumns = [];
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $newColumns[$key] = $value;
                $newColumns['wpma_preview']  = __('Vorschau', 'wp-media-archive');
                $newColumns['wpma_type']     = __('Typ', 'wp-media-archive');
                $newColumns['wpma_author']   = __('Urheber', 'wp-media-archive');
                $newColumns['wpma_date_col'] = __('Aufnahmedatum', 'wp-media-archive');
            } else {
                $newColumns[$key] = $value;
            }
        }
        return $newColumns;
    }

    public function renderCustomColumns(string $column, int $postId): void
    {
        switch ($column) {
            case 'wpma_preview':
                $fileId = get_post_meta($postId, '_wpma_file_id', true);
                if ($fileId) {
                    $mimeType = get_post_mime_type((int) $fileId);
                    if (str_starts_with($mimeType, 'image/')) {
                        $url = wp_get_attachment_image_url((int) $fileId, 'thumbnail');
                        if ($url) {
                            echo '<img src="' . esc_url($url) . '" width="60" height="60" style="object-fit:cover;border-radius:4px;">';
                        }
                    } elseif (str_starts_with($mimeType, 'audio/')) {
                        echo '<span class="dashicons dashicons-format-audio" style="font-size:30px;color:#0073aa;"></span>';
                    }
                }
                break;
            case 'wpma_type':
                $types = get_the_terms($postId, 'media_type');
                if ($types && ! is_wp_error($types)) {
                    echo esc_html(implode(', ', wp_list_pluck($types, 'name')));
                }
                break;
            case 'wpma_author':
                echo esc_html(get_post_meta($postId, '_wpma_author', true));
                break;
            case 'wpma_date_col':
                echo esc_html(get_post_meta($postId, '_wpma_date', true));
                break;
        }
    }
}
