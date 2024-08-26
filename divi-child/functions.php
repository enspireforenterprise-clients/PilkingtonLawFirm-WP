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

function update_image_alt_tags_by_filename() {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var imagesToAltText = {
                "Block2": "Family dependents",
                "Picture4-241x300-1": "Mauritius Beachs",
                "Picture5-300x178-1": "Mauritius villa",
            };

            var imgElements = document.querySelectorAll('img');

            imgElements.forEach(function(imgElement) {
                var imgSrc = imgElement.getAttribute('src');

                if (imgSrc) {
                    var imgName = imgSrc.split('/').pop().split('.')[0];


                    if (imagesToAltText.hasOwnProperty(imgName)) {
                        imgElement.alt = imagesToAltText[imgName];
                        //console.log("Alt text set for: " + imgName);
                    } else {
                        console.log("No matching alt text found for: " + imgName);
                    }
                }
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'update_image_alt_tags_by_filename');



function add_inline_custom_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var anchorTag = document.querySelector('#block-30 a');
        if (anchorTag) {
            anchorTag.setAttribute('aria-label', 'Visit LinkedIn Profile');
        }

        var imgTag = document.querySelector('#block-30 img');
        if (imgTag) {
            imgTag.setAttribute('alt', 'LinkedIn Logo');
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'add_inline_custom_script');



?>