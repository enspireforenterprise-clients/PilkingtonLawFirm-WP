<?php
function my_theme_enqueue_styles() {
 
    $parent_style = 'divi-style';
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

// Dynamic Copyright Year 
function customize_footer_info() {
    $current_year = date('Y');
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var currentYear = <?php echo json_encode($current_year); ?>;
            var footerInfo = document.getElementById('footer-info');
            if (footerInfo) {
                footerInfo.textContent = footerInfo.textContent.replace(/Copyright © \d{4}/, 'Copyright © ' + currentYear);
            }
        });
    </script>         
    <?php
}
add_action('wp_footer', 'customize_footer_info');

// Dynamic Copyright Year End here

?>