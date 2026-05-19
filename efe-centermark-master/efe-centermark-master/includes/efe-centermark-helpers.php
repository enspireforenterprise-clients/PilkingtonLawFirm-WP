<?php

function efe_yotrack_add_settings_output() {

    $yotrack_status = Centermark()::settings()->get_yotrack_status();

    if( 'enabled' != $yotrack_status ) {

        return;

    }

    if( '2.3' == Centermark()::settings()->get_yotrack_version() ) {

        $yotrack_cid       = Centermark()::settings()->get_yotrack_cid();
        $yotrack_shortname = Centermark()::settings()->get_yotrack_shortname();
        $phones            = Centermark()::settings()->get_yotrack_phones();

        $phones_formatted  = explode( "\n", str_replace( "\r", "", $phones ) );

        if( ! empty( $yotrack_cid ) && ! empty( $yotrack_shortname ) && ! empty( $phones ) ) {

            echo sprintf ('
                <script type="text/javascript">
                    let yoCid = "%s";
                    let yoShortname = "%s"
                    let yoPhones = %s;
                    let yoApi;
                </script>',
                esc_js( $yotrack_cid ),
                esc_js( $yotrack_shortname ),
                json_encode( $phones_formatted )
            );    

        }
        
    }

}
add_action('wp_footer', 'efe_yotrack_add_settings_output');


add_filter( 'script_loader_tag', 'efe_add_class_to_script', 10, 3 );

function efe_add_class_to_script( $tag, $handle, $source ) {

    if ( 'efe-centermark-yotrack-auto' === $handle ) {

        if( 'yotrack-corporate' === Centermark()::settings()->get_yotrack_location_type() ) {

            $tag = '<script type="text/javascript" src="' . $source . '" class="yotrack-corporate" id="efe-centermark-yotrack-auto-js"></script>';

        }

    }

    return $tag;

}

add_filter( 'wp_headers', 'efe_add_cors_header' );

function efe_add_cors_header( $headers ) {

    if( 'true' === Centermark()::settings()->get_yotrack_cors_header_status() ) {

        //Respect Header if it is already sent. Otherwise set it to *
        if( ! isset( $headers['Access-Control-Allow-Origin'] ) ) {

            $headers['Access-Control-Allow-Origin'] = '*';

        }

        $headers['Referrer-Policy'] = 'no-referrer-when-downgrade';

    }

    return $headers;
}
