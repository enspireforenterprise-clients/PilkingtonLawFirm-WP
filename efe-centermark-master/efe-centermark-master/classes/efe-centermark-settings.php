<?php

if( ! class_exists( 'EFE_Centermark_Settings' ) ) {

  class EFE_Centermark_Settings {

		CONST SLUG         = 'efe-centermark-settings';
		CONST NONCE_ACTION = 'efe-centermark-settings-admin-nonce-action';
		CONST NONCE_NAME   = 'efe-centermark-settings-admin-nonce-name';

		private $_form_errors = [];

    function __construct() {

			add_action( 'admin_init', [ $this, 'route_form_post' ] );

			if( is_admin() ) {

				add_action( 'admin_menu', [ $this, 'add_api_settings_page' ] );

			}

		}
		
		public function add_api_settings_page() {

			add_submenu_page(
				'options-general.php',
				'Centermark',
				'Centermark',
				'manage_options',
				self::SLUG,
				[ $this, 'render_centermark_settings_page' ]
			);

		}

		public function render_centermark_settings_page() {

			if( ! current_user_can( 'manage_options' ) ) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            

			$errors                = empty( $this->_form_errors ) ? '' : '<div class="notice notice-error"><ul><li>' . implode( '</li><li>', $this->_form_errors ) . '</li></ul></div>';
			$action                = admin_url( 'options-general.php?page=' . self::SLUG );
			$yotrack_cid           = get_option( 'efe_yotrack_cid', '' );
			$yotrack_shortname     = get_option( 'efe_yotrack_shortname', '' );
			$enspire_api_enabled   = get_option('efe_enspire_api_enabled', '');
			$enspire_api_user      = get_option( 'efe_enspire_api_user', '' );
			$enspire_api_password  = get_option( 'efe_enspire_api_password', '' );
			$enspire_fma_id 	   = get_option('efe_enspire_fma_id', '');
			$yotrack_ver           = get_option( 'efe_yotrack_version', '' );
			$yotrack_location_type = get_option( 'efe_yotrack_location_type', '' );
			$yotrack_phones        = get_option( 'efe_yotrack_phones', '' );
			$yotrack_status        = get_option( 'efe_yotrack_status', '' );
			$yotrack_enable_cors   = get_option( 'efe_yotrack_enable_cors', 'true' );
			$nonce                 = wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME, true, false );
			$button                = get_submit_button( 'Save Changes', 'primary large', 'submit', false );

			

			$custom_styles = '
<style>
/* General table styling */

.wrap{background-color: #ffffff;
    padding: 30px;
    max-width: 80%;
    margin: 10px auto;
    border-radius: 5px;}

.form-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1em;
}

.form-table th {
    text-align: left;
    vertical-align: top;
    padding: 12px 10px;
    font-weight: 600;
    width: 25%;
}

.form-table td {
    padding: 12px 10px;
}

/* Inputs */
.form-table input[type="text"],
.form-table input[type="password"],
.form-table textarea {
    width: 100%;
    max-width: 400px;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-table input[type="checkbox"],
.form-table input[type="radio"] {
    margin-right: 8px;
    transform: scale(1.2);
    vertical-align: middle;
}

/* Fieldset & legend */
.form-table fieldset {
    border: none;
    margin: 0;
    padding: 0;
}

.form-table legend {
    display: none;
}

/* Descriptions */
.form-table .description {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}


</style>
';


			printf('
								<div class="wrap">
					<h2 style="border-bottom: 1px solid #f0f0f1; padding-bottom: 15px;">Centermark Settings</h2>
					%s
					%s
					<form method="post" enctype="multipart/form-data" action="%s">
						<h2>YoTrack Settings</h2>
						<table class="form-table" role="presentation">
                            <tbody>
                                <tr>
									<th scope="row">
										Enable YoTrack
									</th>
									<td>
                                        <input name="yotrack-status" type="checkbox" id="yotrack-status" class="regular-text" %s value="enabled">
                                        <label for="yotrack-status">Enable YoTrack</label>
									</td>
								</tr>
								<tr>
									<th scope="row">Preferred YoTrack Version</th>
									<td>
										<fieldset>
											<legend class="screen-reader-text">Preferred YoTrack Version</legend>
											<label><input name="preferred-version" type="radio" value="auto" %s> YoTrack Auto (v2.4)</label><br>
											<label><input name="preferred-version" type="radio" value="2.3" %s> YoTrack v2.3</label><br>
										</fieldset>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="yotrack-cid">YoTrack Client ID</label>
									</th>
									<td>
										<input name="yotrack-cid" type="text" id="yotrack-cid" aria-describedby="yotrack-cid_desc" class="regular-text" value="%s">
										<p class="description" id="yotrack-cid_desc">Client ID used by YoTrack (Used by v2.3).</p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="yotrack-shortname">YoTrack Shortname</label>
									</th>
									<td>
										<input name="yotrack-shortname" type="text" id="yotrack-shortname" aria-describedby="yotrack-shortname_desc" class="regular-text" value="%s">
										<p class="description" id="yotrack-shortname_desc">Shortname used by YoTrack (Used by v2.3).</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="enspire-api-enabled">Submit leads via Enspire Leads API</label>
									</th>
									<td>
										<input name="enspire-api-enabled" type="checkbox" id="enspire-api-enabled" class="regular-text" %s value="enabled">
										<p class="description" id="enspire-api-enabled">Enable submission of leads via Enspire Leads API.</p>
									</td>
								</tr>

									<tr>
									<th scope="row">
										<label for="yotrack-enspire-api-user">Enspire API User *</label>
									</th>
									<td>
										<input name="yotrack-enspire-api-user" type="text" id="yotrack-enspire-api-user" aria-describedby="enspire-api-user" class="regular-text" value="%s"  >
										<p class="description" id="yotrack-enspire-api-user_desc">Enspire API User.</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="yotrack-enspire-api-password">Enspire API Password *</label>
									</th>
									<td>
										<input name="yotrack-enspire-api-password" type="password" id="yotrack-enspire-api-password" aria-describedby="enspire-api-password" class="regular-text" value="%s"  >
										<p class="description" id="yotrack-enspire-api-password_desc">Enspire API Password .</p>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="enspire-fma-id">Enspire FMA ID *</label>
									</th>
									<td>
										<input name="enspire-fma-id" type="text" id="enspire-fma-id" aria-describedby="enspire-fma-id" class="regular-text" value="%s"  >
										<p class="description" id="enspire-fma-id_desc">Enspire FMA ID.</p>
									</td>
								</tr>


								

								<tr>
									<th scope="row">YoTrack Auto - Location Type</th>
									<td>
										<fieldset>
											<legend class="screen-reader-text">Location Type</legend>
											<label><input name="yotrack-location-type" type="radio" value="yotrack-corporate" %s> Corporate</label><br>
											<label><input name="yotrack-location-type" type="radio" value="yotrack-local" %s> Local</label><br>
										</fieldset>
									</td>
								</tr>
							</tbody>
						</table>
						<!--
						<h2>YoTrack Location Page Settings</h2>
						<table class="form-table" role="presentation">
                            <tbody>
                                <tr>
									<th scope="row">
										<label for="yotrack-location-phones_1">Phone Numbers</label>
									</th>
									<th scope="row">
										<label for="yotrack-location-page-urls_1">Page URLs</label>
									</th>
									<th scope="row">
										<label for="yotrack-location-page-cid_1">Client ID</label>
									</th>
								</tr>
								<tr>
									<td>
										<textarea name="location-page[1][phones]" id="yotrack-location-phones_1" aria-describedby="yotrack-location-phones-desc" class="regular-text" rows="7" style="white-space:nowrap;overflow:auto;"></textarea>
										<p class="description" id="yotrack-location-phones-desc">Phone numbers to swap. Enter one number per line.</p>
									</td>
									
									<td>
										<textarea name="location-page[1][urls]" id="yotrack-location-page-urls_1" aria-describedby="yotrack-location-urls-desc" class="regular-text" rows="7" style="white-space:nowrap;overflow:auto;"></textarea>
										<p class="description" id="yotrack-location-urls-desc">Page URLs. Enter one URL per line.</p>
									</td>
									<td>
										<input name="location-page[1][cid]" type="text" id="yotrack-location-page-cid_1" class="regular-text" value="">
									</td>
								</tr>
							</tbody>
						</table>
						-->
						<h2>Tracking Settings</h2>
						<table class="form-table" role="presentation">
							<tbody>
								<tr>
									<th scope="row">
										<label for="yotrack-phones">Phone Numbers</label>
									</th>
									<td>
										<textarea name="yotrack-phones" id="yotrack-phones" aria-describedby="yotrack-phones-desc" class="regular-text" rows="7" style="white-space:nowrap;overflow:auto;">%s</textarea>
										<p class="description" id="yotrack-phones-desc">Phone numbers to swap. Enter one number per line.</p>
									</td>
								</tr>
								<tr>
									<th scope="row">Enable CORS Header</th>
									<td>
										<fieldset>
											<legend class="screen-reader-text">Enable CORS</legend>
											<label><input name="yotrack-enable-cors-header" type="radio" value="true" %s> Enable CORS Header</label><br>
											<label><input name="yotrack-enable-cors-header" type="radio" value="false" %s> Disable CORS Header</label><br>
											<p class="description" id="yotrack-phones-desc">CORS Needs to be enabled for YoTrack to work properly. <br>Some hosts already have this header, or may wish to specify this manually.<br> In this case, disable the CORS header and set it on the server level.</p>
										</fieldset>
									</td>
								</tr>
							</tbody>
						</table>
						<input type="hidden" name="efe-centermark-api-action" value="save-centermark-settings" />
						%s
						%s
					</form>
				</div>
				<style>
				#yotrack-phones {
					white-space: pre-wrap !important;
				}
				</style>',
				$errors,
				$custom_styles,
                $action,
                'enabled' === $yotrack_status ? 'checked="checked"' : '',
				'auto' === $yotrack_ver ? 'checked="checked"' : '',
				'2.3' === $yotrack_ver ? 'checked="checked"' : '',
				esc_attr( $yotrack_cid ),
				esc_attr( $yotrack_shortname ),
				'enabled' === $enspire_api_enabled ? 'checked="checked"' : '',
				esc_attr( $enspire_api_user ),
				esc_attr( $enspire_api_password ),
				esc_attr($enspire_fma_id),
				'yotrack-corporate' === $yotrack_location_type ? 'checked="checked"' : '',
				'yotrack-local' === $yotrack_location_type ? 'checked="checked"' : '',
				esc_textarea( wp_unslash( $yotrack_phones ) ),
				'true' === $yotrack_enable_cors ? 'checked="checked"' : '',
				'false' === $yotrack_enable_cors ? 'checked="checked"' : '',
				$nonce,
				$button
			);

		}

		public function route_form_post() {

			if( ! isset( $_POST['efe-centermark-api-action'] ) ) {
				return;
			}

			if( ! $this->verify_nonce() ) {
				die( 'Something went wrong. Please try again.' );
			}

			$action = $_POST['efe-centermark-api-action'];

			switch( $action ) {

				case 'save-centermark-settings':

					$this->save_centermark_settings();
					break;

			}

		}

		public function save_centermark_settings() {

			// Save YoTrack Settings

            $yotrack_ver		   = trim( $_POST[ 'preferred-version' ] );
            $yotrack_cid 		   = trim( $_POST[ 'yotrack-cid' ] );
            $yotrack_shortname     = trim( $_POST[ 'yotrack-shortname' ] );
			$enspire_api_enabled = isset($_POST['enspire-api-enabled']) ? 'enabled' : 'disabled';
			$enspire_api_user     = sanitize_text_field($_POST['yotrack-enspire-api-user']);
			$enspire_api_password = sanitize_text_field($_POST['yotrack-enspire-api-password']);
			$enspire_fma_id = sanitize_text_field($_POST['enspire-fma-id']);

			$yotrack_status        = trim( $_POST[ 'yotrack-status' ] );
			$yotrack_location_type = trim( $_POST[ 'yotrack-location-type' ] );
			$yotrack_enable_cors   = trim( $_POST[ 'yotrack-enable-cors-header' ] );


			if ($enspire_api_enabled === 'enabled') {

				if (empty($enspire_api_user)) {
					$this->_form_errors[] = 'Enspire API User is required when Enspire Leads API is enabled.';
				}

				if (empty($enspire_api_password)) {
					$this->_form_errors[] = 'Enspire API Password is required when Enspire Leads API is enabled.';
				}

				if (empty($enspire_fma_id)) {
					$this->_form_errors[] = 'Enspire FMA ID is required when Enspire Leads API is enabled.';
				}
			}

			//  STOP if errors exist
			if (!empty($this->_form_errors)) {
				return;
			}


            update_option( 'efe_yotrack_version', $yotrack_ver );
			update_option( 'efe_yotrack_cid', $yotrack_cid );
            update_option( 'efe_yotrack_shortname', $yotrack_shortname );
			update_option('efe_enspire_api_enabled', $enspire_api_enabled);
			update_option('efe_enspire_api_user', $enspire_api_user);
			update_option('efe_enspire_api_password', $enspire_api_password);
			update_option('efe_enspire_fma_id', $enspire_fma_id);
			
			update_option( 'efe_yotrack_status', $yotrack_status );
			update_option( 'efe_yotrack_location_type', $yotrack_location_type );
			update_option( 'efe_yotrack_enable_cors', $yotrack_enable_cors );

			// Save Tracking Settings

			$phone_numbers = trim( $_POST[ 'yotrack-phones' ] );
			
            
			update_option( 'efe_yotrack_phones', $phone_numbers );

		}

		public function get_yotrack_version() {

			return get_option( 'efe_yotrack_version', '' );

		}

		public function get_yotrack_status() {

			return get_option( 'efe_yotrack_status', '' );

		}

		public function get_yotrack_cid() {

			return get_option( 'efe_yotrack_cid', '' );

        }
        
        public function get_yotrack_shortname() {

			return get_option( 'efe_yotrack_shortname', '' );

		}

		public function get_enspire_api_user() {
    	return get_option('efe_enspire_api_user', '');
		}

		public function get_enspire_api_password() {
			return get_option('efe_enspire_api_password', '');
		}

		public function get_enspire_fma_id() {
			return get_option('efe_enspire_fma_id', '');
		}

		public function get_enspire_api_enabled() {
			return get_option('efe_enspire_api_enabled', 'disabled');
		}	

		public function get_yotrack_location_type() {

			return get_option( 'efe_yotrack_location_type', '' );

		}

		public function get_yotrack_phones() {

			return apply_filters( 
				'efe_yotrack_phones', 
				wp_unslash( get_option( 'efe_yotrack_phones', '' ) )
			);

		}
		
		public function get_yotrack_cors_header_status() {

			return get_option( 'efe_yotrack_enable_cors', '' );

		}
		
		private function verify_nonce() {

			if( isset( $_POST[ self::NONCE_NAME ] ) ) {

				return wp_verify_nonce( $_POST[ self::NONCE_NAME ], self::NONCE_ACTION );

			}

			return false;

		}

    }

}
