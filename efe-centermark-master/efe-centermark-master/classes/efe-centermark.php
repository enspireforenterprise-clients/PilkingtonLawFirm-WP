<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct access allowed' );

if( ! class_exists( 'EFE_Centermark' ) ) {

	class EFE_Centermark {
		
		protected static $_instance = null;
        protected static $_settings = null;
        protected static $_api      = null;

    function __construct() {

            self::settings();
            self::api();

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_yotrack_script' ] );
            add_action( 'wp_footer', [ $this, 'enqueue_frontend_assets' ] );

            // Register Gravity Forms submission handler only when Enspire API is enabled in settings
            // if ( ( class_exists( 'GFForms' ) || class_exists( 'RGFormsModel' ) ) && 'enabled' === self::settings()->get_enspire_api_enabled() ) {
            //     add_action( 'gform_after_submission', [ $this, 'send_lead_to_yodle' ], 10, 2 );
            // }


            if ( class_exists( 'GFForms' ) || class_exists( 'RGFormsModel' ) ) {
                add_action( 'gform_after_submission', [ $this, 'send_lead_to_yodle' ], 10, 2 );
            }

            // Prevent sending spam/blocklisted entries to Yodle CRM
            if ( apply_filters( 'gpb_enable_blocklist_spam', false ) && rgar( $entry, 'is_spam' ) ) {
                $this->yodle_custom_log( 'Yodle: Blocklisted/spam email detected, skipping CRM send' );
                return;
            }
                        
            if( is_admin() ) {

				add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

			}

        }
        
        function enqueue_admin_assets() {

			//wp_enqueue_script( 'efe-centermark-admin', path_join( EFE_CENTERMARK_ADMIN_ASSETS_URL, 'js/app.js' ), '',  EFE_CENTERMARK_VERSION  );
			//wp_enqueue_style(  'efe-centermark-admin', path_join( EFE_CENTERMARK_ADMIN_ASSETS_URL, 'css/style.css' ), '', EFE_CENTERMARK_VERSION  );

        }
        
        function enqueue_frontend_assets() {

            $yotrack_status = self::$_settings->get_yotrack_status();

            if( 'enabled' != $yotrack_status ) {

                return;
                
            }
			
            //wp_enqueue_style(  'efe-centermark', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'css/style.css' ), '', EFE_CENTERMARK_VERSION  );

            $version = self::$_settings->get_yotrack_version();

            wp_enqueue_script( 'efe-centermark-global', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/global.js' ), '', EFE_CENTERMARK_VERSION, true );

            if( class_exists( 'RGFormsModel' ) ) {

                if( '2.3' == $version ) {

                    wp_enqueue_script( 'efe-centermark-gravity', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/gravity-legacy.js' ), '', EFE_CENTERMARK_VERSION, true );

                } else {

                    wp_enqueue_script( 'efe-centermark-gravity', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/gravity-auto.js' ), '', EFE_CENTERMARK_VERSION, true );

                }

            }

            // Expose Enspire API enabled flag to the gravity script via localization
            if ( wp_script_is( 'efe-centermark-gravity', 'registered' ) || wp_script_is( 'efe-centermark-gravity', 'enqueued' ) ) {
                wp_localize_script( 'efe-centermark-gravity', 'efeCentermarkSettings', [
                    'enspire_api_enabled' => ( 'enabled' === self::settings()->get_enspire_api_enabled() )
                ] );
            }

            if( class_exists( 'Ninja_Forms' ) ) { 

                if('2.3' == $version ) {

                    wp_enqueue_script( 'efe-centermark-ninja', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/ninja-legacy.js' ), '', EFE_CENTERMARK_VERSION, true );

                } else {

                    wp_enqueue_script( 'efe-centermark-ninja', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/ninja-auto.js' ), '', EFE_CENTERMARK_VERSION, true );

                }

            }


            if('2.3' == $version ) {

                wp_enqueue_script( 'efe-centermark', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/jotform-legacy.js' ), '', EFE_CENTERMARK_VERSION, true );

            } else {

                wp_enqueue_script( 'efe-centermark', path_join( EFE_CENTERMARK_FRONT_ASSETS_URL, 'js/jotform-auto.js' ), '', EFE_CENTERMARK_VERSION, true );

            }

        }
        
        function enqueue_yotrack_script() {

            $yotrack_status = self::$_settings->get_yotrack_status();

            if( 'enabled' != $yotrack_status ) {

                return;

            }
            
            $version = self::$_settings->get_yotrack_version();

            if( '2.3' == $version ) {

                wp_enqueue_script( 'efe-centermark-yotrack',  '//yotrack.cdn.ybn.io/yotrack.min.js', '', EFE_CENTERMARK_VERSION, true );

            } else {

                wp_enqueue_script( 'efe-centermark-yotrack-auto',  '//yotrack.cdn.ybn.io/yotrack_auto.min.js', '', EFE_CENTERMARK_VERSION, true );

            }

		}

		public static function instance() {

			if( null == self::$_instance ) {

				self::$_instance = new self;

			}

			return self::$_instance;

		}

		public static function settings() {

			if( null == self::$_settings ) {

				self::$_settings = new EFE_Centermark_Settings;

			}

			return self::$_settings;

        }
        
        public static function api() {

			if( null == self::$_api ) {

                if( is_multisite() ) {

                    self::$_api = new EFE_Centermark_Api;

                }

			}

			return self::$_api;

        }

        /**
         * Log helper (writes to theme dir yodle-log.txt for parity with previous implementation)
         */
        private function yodle_custom_log( $message ) {
            $log_file = get_stylesheet_directory() . '/yodle-log.txt';
            $timestamp = date("Y-m-d H:i:s");
            $formatted_message = "[" . $timestamp . "] " . print_r( $message, true ) . PHP_EOL;
            file_put_contents( $log_file, $formatted_message, FILE_APPEND );
        }

        /**
         * Gravity Forms after submission handler moved from theme functions.php
         */

        public function send_lead_to_yodle( $entry, $form ) {

            if ( 'enabled' !== self::settings()->get_enspire_api_enabled() ) {
                $this->yodle_custom_log('Yodle: Enspire disabled, skipping server-side send');
                return;
            }

            // Prevent duplicate sending for the same entry
            if ( gform_get_meta( $entry['id'], 'sent_to_yodle' ) ) {
                $this->yodle_custom_log('Yodle: Already sent, skipping');
                return;
            }

            /**
             * GP Blocklist marks blocked emails as spam.
             * Stop CRM submission immediately if entry is spam.
             */
            if ( isset( $entry['status'] ) && 'spam' === $entry['status'] ) {
                $this->yodle_custom_log( 'Yodle: Spam entry detected via entry status, skipping CRM send' );
                return;
            }

            // Gravity Forms spam check
            if ( rgar( $entry, 'is_spam' ) ) {
                $this->yodle_custom_log('Yodle: Entry flagged as spam, not sending');
                return;
            }

            // -----------------------------
            // Map fields dynamically
            // -----------------------------
            $formData = [];
            $metadata = '';

            foreach ( $form['fields'] as $field ) {

                $label = trim( $field->label );
                $value = rgar( $entry, (string) $field->id );

                // Skip empty fields
                if ( $value === '' ) {
                    continue;
                }

                // Use hidden metadata field
                if ( strtolower($label) === 'metadata' ) {
                    $metadata = $value;
                    continue;
                }

                // Add all other fields to formData
                $formData[ $label ] = $value;
            }

            // -----------------------------
            // Build payload
            // -----------------------------
            $payload = [
                [
                    "metadata"        => $metadata,
                    "formData"        => $formData,
                    "externalLeadId"  => "pilkington",
                    "franchiseNumber" => "1"
                ]
            ];

            // Log payload
            $this->yodle_custom_log( $payload );

            // Basic Auth credentials
            $api_username = self::settings()->get_enspire_api_user();
            $api_password = self::settings()->get_enspire_api_password();

            $headers = [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode( $api_username . ':' . $api_password )
            ];

            $fma_id = self::$_settings->get_enspire_fma_id();

            $endpoint = "https://api.yodle.com/api/v1/fma/{$fma_id}/leads/bulk";

            // Send API request
            $this->yodle_custom_log('Sending lead to API');

            $response = wp_remote_post(
                $endpoint,
                [
                    'headers' => $headers,
                    'body'    => json_encode( $payload ),
                    'timeout' => 20
                ]
            );

            if ( is_wp_error( $response ) ) {

                $this->yodle_custom_log( 'Yodle API Error: ' . $response->get_error_message() );

            } else {

                $response_body = wp_remote_retrieve_body( $response );

                $this->yodle_custom_log( 'Yodle API Response: ' . $response_body );

                // Mark as sent only if API call succeeds
                gform_update_meta( $entry['id'], 'sent_to_yodle', true );
            }
        }

	}
	
}
