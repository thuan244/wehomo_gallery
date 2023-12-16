<?php
/*
*Plugin Name: Wehomo Gallery Field
*Description: Custom Gallery
*Plugin URI: https://wehomo.co
*Version:  1.0.0
*Author: Wehomo
*Author URI: https://wehomo.co
*License:  GPL2
*Text Domain: wehomo
*/

define('PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

function wehomo_enqueue_assets(){
    wp_enqueue_style('wehomo-flexslider', PLUGIN_DIR_URL.'flexslider/flexslider.css');
    wp_enqueue_style('wehomo-style', PLUGIN_DIR_URL.'style.css');
    wp_enqueue_script( 'wehomo-flexslider', PLUGIN_DIR_URL.'flexslider/jquery.flexslider-min.js', array('jquery'), null, true );
    wp_enqueue_script( 'wehomo-script', PLUGIN_DIR_URL.'wehomo-script.js', array('jquery'), null, true );

}
add_action( 'wp_enqueue_scripts', 'wehomo_enqueue_assets', 11 );
// Thêm trường gallery vào trang tạo và chỉnh sửa bài viết
function custom_gallery_field_meta_box() {
    add_meta_box(
        'custom_gallery_field', // ID của meta box
        'Gallery', // Tên hiển thị của meta box
        'render_custom_gallery_field', // Callback function để hiển thị nội dung của meta box
        'post', // Post type
        'normal', // Vị trí của meta box: normal, side, advanced
        'default' // Ưu tiên của meta box: default, high, low
    );
}
add_action('add_meta_boxes', 'custom_gallery_field_meta_box');

// Hiển thị trường gallery
function render_custom_gallery_field($post) {
    $gallery_images = get_post_meta($post->ID, 'custom_gallery_images', true);
    $image_ids = explode(',', $gallery_images);
    ?>
    <label for="custom_gallery_images">Gallery Images</label>
    <input type="hidden" id="custom_gallery_images" name="custom_gallery_images" value="<?php echo esc_attr($gallery_images); ?>" />
    <div id="image-preview-container">
        <?php
        if (!empty($image_ids)) {
            foreach ($image_ids as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                echo '<img src="' . esc_url($image_url) . '" class="image-preview" />';
            }
        }
        ?>
    </div>
    <button type="button" id="upload-image-button" class="button">Upload Images</button>
    <p>Add image URLs or use the "Upload Images" button.</p>
    <script>
    jQuery(document).ready(function($) {
        $('#upload-image-button').click(function() {
            var customUploader = wp.media({
                title: 'Choose Images for Gallery',
                multiple: true
            });
            customUploader.on('select', function() {
                var attachments = customUploader.state().get('selection').map(function(attachment) {
                    attachment = attachment.toJSON();
                    return attachment.id;
                });
                var existingImagesValue = $('#custom_gallery_images').val();
                var existingImages = []
                if ( existingImagesValue ){
                    existingImages = existingImagesValue.split(',');
                }
                
                var newImages = existingImages.concat(attachments);
                $('#custom_gallery_images').val(newImages.join(','));
                $('#image-preview-container').empty();
                
                if (newImages.length > 0) {
                    newImages.forEach(function(imageId) {
                        var image = wp.media.attachment(imageId);
                        if (image) {
                            var imageUrl = image.attributes.sizes && image.attributes.sizes.thumbnail ? image.attributes.sizes.thumbnail.url : image.attributes.url;
                            $('#image-preview-container').append('<img src="' + imageUrl + '" class="image-preview" />');
                        }
                    });
                }
            });
            customUploader.open();
        });
    });
    </script>
    <?php
}

// Lưu trường gallery khi bài viết được lưu
function save_custom_gallery_field($post_id) {
    if (array_key_exists('custom_gallery_images', $_POST)) {
        update_post_meta(
            $post_id,
            'custom_gallery_images',
            sanitize_text_field($_POST['custom_gallery_images'])
        );
    }
}
add_action('save_post', 'save_custom_gallery_field');

function display_gallery_slider(){
    global $post;
    $gallery_images = get_post_meta($post->ID, 'custom_gallery_images', true);
    if (!empty($gallery_images)) {
        $image_ids = explode(',', $gallery_images);
        if (!empty($image_ids)) {
            echo '<div class="woocommerce-product-gallery">';
            echo '<div class="flexslider">';
            echo '<ul class="slides">';
            foreach ($image_ids as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'full');
                echo '<li data-thumb='.esc_url($image_url).'>';
                echo '<img src="' . esc_url($image_url) . '" alt="" />';
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
            echo '</div>';
        }
    }
}
add_shortcode('Wehomo_Gallery', 'display_gallery_slider');