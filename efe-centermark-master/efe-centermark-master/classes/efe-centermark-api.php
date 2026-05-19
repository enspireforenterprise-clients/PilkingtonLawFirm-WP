<?php

if( ! class_exists( 'EFE_Centermark_Api' ) ) {

    class EFE_Centermark_Api {

        function __construct() {

            add_action( 'init',          [ $this, 'init' ] );
			add_filter( 'query_vars',    [ $this, 'query_vars' ] );
			add_action( 'parse_request', [ $this, 'parse_request' ] );

        }

        public function init() {

			add_rewrite_rule(
				'efe-centermark/sites/select-all/?$',
				'index.php?efe-centermark-api-end-point=select-all-sites',
				'top'
            );

          /*  add_rewrite_rule(
				'efe-centermark/pages/select-all/?$',
				'index.php?efe-centermark-api-end-point=select-all-pages',
				'top'
            );
          */
        }
        
        public function query_vars( $query_vars ) {

			$query_vars[] = 'efe-centermark-api-end-point';

			return $query_vars;

        }
        
        public function parse_request( &$wp ) {

			if( isset( $wp->query_vars['efe-centermark-api-end-point'] ) ) {

				$end_point = $wp->query_vars['efe-centermark-api-end-point'];

				switch( $end_point ) {

					case 'select-all-sites':

						$this->select_all_sites();
						break;

                    case 'select-all-pages':
                        $this->select_all_pages();
                        break;

				}

			}

		}

        public function select_all_sites() {

            if( ! isset( $_REQUEST['bucket'] ) ) {

				$this->send_error_response( array( 'The bucket parameter is invalid or missing.' ) );

            }
            
            $bucket = (string) $_REQUEST['bucket'];

            $this->get_all_sites( $bucket );

        }

        public function select_all_pages() {

            if( ! isset( $_REQUEST['bucket'] ) ) {

				$this->send_error_response( array( 'The bucket parameter is invalid or missing.' ) );

            }
            
            $bucket = (string) $_REQUEST['bucket'];

            $this->get_all_pages( $bucket );

        }

        protected function send_response( $raw_data ) {

			$response = (object) array(
				'success' => true,
				'payload' => $raw_data
			);

			$json = json_encode( $response );

			if( false === $json ) {

				$this->send_error_response();

			}

			header( 'Content-type: application/json' );
			echo $json;

			exit;

		}


        protected function send_error_response( $errors = array() ) {

			$response = (object) array( 
				'success' => false, 
				'errors'  => $errors
			);

			header( 'Content-type: application/json' );
			echo json_encode( $response );

			exit;

        }

        public function get_all_sites( $bucket ) {

            $network_data = $this->get_network_data();

            $client_ids = array_column($network_data, 'client_id');

            $numbers_to_swap = [];

            if( 0 < sizeof( $network_data ) ) {

                $client_ids = array_column($network_data, 'client_id');

                $numbers_to_swap = $this->get_numbers_to_swap( $client_ids, $bucket );

                if ( is_wp_error( $numbers_to_swap ) || wp_remote_retrieve_response_code( $numbers_to_swap ) != 200 ) {
                    error_log( print_r( $numbers_to_swap, true ) );
                }

                $numbers_to_swap = wp_remote_retrieve_body( $numbers_to_swap );

                $numbers_to_swap = json_decode( $numbers_to_swap );

            }

            foreach( $network_data as $site ) {

                $sites_array[] = $this->get_site_object( $site->blog_id, $site->client_id );

            }

            foreach($sites_array as &$value2) {

                foreach($numbers_to_swap as $value1) {

                    if($value2->clientId == $value1->clientId) {

                        $value2 = (object) array_merge((array) $value2, (array) $value1);

                    }
                    
                }

            }

            $this->send_response( (object) [ 'sites' => $sites_array ] );

           // $this->send_response( (object) [ 'sites' => $network_data ] );
        }
        
        public function get_network_data() {

            $sites = get_sites([
				'update_site_cache' => true,
				'number'            => 1000,
				'public'			=> 1
            ]);

            $network_data = [];

			foreach( $sites as $site ) {

                $client_id = get_blog_option( $site->blog_id, 'efe_yotrack_cid', '');

                

                if( ! empty( $client_id ) ) {

                    $network_data[] = (object) [
                        'client_id' => $client_id,
                        'blog_id'   => $site->blog_id
                    ];

                }

            }
            
            
            
            return $network_data;

        }

        private function get_numbers_to_swap( $client_ids, $bucket ) {

            $endpoint_url = 'https://labs.natpal.com/multisite/phonenumbers';

            $body = array(
                'bucket' => $bucket,
                'jsonp' => false,
                'clientIds' => $client_ids,
            );

            $body = wp_json_encode( $body );

            $options = [
                'body'        => $body,
                'headers'     => [
                    'Content-Type' => 'application/json',
                ],
                'timeout'     => 60,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.0',
                'sslverify'   => false,
                'data_format' => 'body',
            ];

            $response = wp_remote_post( $endpoint_url, $options );

            return $response;

        }

        public function get_site_object( $blog_id, $client_id ) {

			$blog_details = get_blog_details( $blog_id );
			$site_object  = (object) [
                'blog_id'         => $blog_id,
                'title'           => $blog_details->blogname,
                'url'             => get_blogaddress_by_id( $blog_id ),
                'shortname'       => get_blog_option( $blog_id, 'efe_yotrack_shortname', '' ),
				'clientId'        => $client_id,
                'original_phones' => explode("\n", str_replace("\r", "", get_blog_option( $blog_id, 'efe_yotrack_phones', '' ) ) ),
                'yotrack_status'  => get_blog_option( $blog_id, 'efe_yotrack_status', '' )
			];

			return apply_filters( 'efe_centermark_site_object', $site_object );

		}

    }

}
