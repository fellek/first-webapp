(function ($) {
    'use strict';

    $(document).ready(function () {
        var frame;

        $('#wpma-upload-btn').on('click', function (e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Mediendatei auswählen',
                button: { text: 'Auswählen' },
                multiple: false,
                library: {
                    type: ['image', 'audio']
                }
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#wpma_file_id').val(attachment.id);

                var preview = $('#wpma-file-preview');
                preview.empty();

                if (attachment.type === 'image') {
                    var url = attachment.sizes && attachment.sizes.medium
                        ? attachment.sizes.medium.url
                        : attachment.url;
                    preview.html('<img src="' + url + '" style="max-width:100%;height:auto;">');
                } else if (attachment.type === 'audio') {
                    preview.html(
                        '<audio controls style="width:100%;">' +
                        '<source src="' + attachment.url + '" type="' + attachment.mime + '">' +
                        '</audio>'
                    );
                } else {
                    preview.html('<p>' + attachment.filename + '</p>');
                }

                $('#wpma-remove-btn').show();
            });

            frame.open();
        });

        $('#wpma-remove-btn').on('click', function (e) {
            e.preventDefault();
            $('#wpma_file_id').val('');
            $('#wpma-file-preview').html('<p class="wpma-no-file">Keine Datei ausgewählt</p>');
            $(this).hide();
        });
    });
})(jQuery);
