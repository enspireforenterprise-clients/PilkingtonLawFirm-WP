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
            // Define an object with image filenames and their corresponding alt texts
            var imagesToAltText = {
                "Block2": "Family dependents",
                "Picture4-241x300-1": "Mauritius Beachs",
                "Picture5-300x178-1": "Mauritius villa",
                // Add more image-name and alt-text pairs as needed
            };

            // Select all images on the page
            var imgElements = document.querySelectorAll('img');

            imgElements.forEach(function(imgElement) {
                // Extract the full src attribute value
                var imgSrc = imgElement.getAttribute('src');

                // If the src attribute exists, proceed
                if (imgSrc) {
                    // Extract the filename from the src attribute without the extension
                    var imgName = imgSrc.split('/').pop().split('.')[0];

                    // Log the imgName to the console for debugging
                    //console.log("Processing image: " + imgName);

                    // If the image name matches one in the imagesToAltText object, update the alt attribute
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


function add_adroll_script_after_body() {
    ?>
    <script type="text/javascript">
        adroll_adv_id = "AU233ADYPBGFDMRKLYMO7D";
        adroll_pix_id = "OEGCPLYRJJBF7OS3KLMQW2";
        adroll_version = "2.0";

        (function(w, d, e, o, a) {
            w.__adroll_loaded = true;
            w.adroll = w.adroll || [];
            w.adroll.f = [ 'setProperties', 'identify', 'track', 'identify_email' ];
            var roundtripUrl = "https://s.adroll.com/j/" + adroll_adv_id
                    + "/roundtrip.js";
            for (a = 0; a < w.adroll.f.length; a++) {
                w.adroll[w.adroll.f[a]] = w.adroll[w.adroll.f[a]] || (function(n) {
                    return function() {
                        w.adroll.push([ n, arguments ])
                    }
                })(w.adroll.f[a])
            }

            e = d.createElement('script');
            o = d.getElementsByTagName('script')[0];
            e.async = 1;
            e.src = roundtripUrl;
            o.parentNode.insertBefore(e, o);
        })(window, document);
        adroll.track("pageView");
    </script>
    <?php
}
add_action('wp_body_open', 'add_adroll_script_after_body');


function add_adroll_tracking_script() {
    ?>
    <script>
    adroll.track("pageView", {
      segment_name: "64de63ec"
    });
    </script>
    <?php
}
add_action('wp_body_open', 'add_adroll_tracking_script');

// TECH-21389 HubSpot Visitor Tracking Script
function add_custom_tracking_script() {

    // Get the current URL path
    $current_path   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $is_home        = is_home() || is_front_page();
    $is_contact_us  = ($current_path === 'contact-us');

    // Only inject the script on home page or /contact-us/
    if ($is_home || $is_contact_us) {
        $page_name = $current_path ?: 'home'; // Define page name for clarity
        ?>
        <!-- Start of HubSpot Embed Code -->
        <script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/19524562.js"></script>
        <script type="text/javascript">
            console.log('HubSpot Tracking script loaded on: <?php echo esc_js($page_name); ?>');
        </script>        
        <!-- End of HubSpot Embed Code -->
        <?php
    }
}

// Hook into wp_footer to add the script just before </body>
add_action('wp_footer', 'add_custom_tracking_script');

?>