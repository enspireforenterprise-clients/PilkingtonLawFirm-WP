<?php

/*
Plugin Name: Enspire for Enterprise - Centermark
Plugin URI:  https://enspireforenterprise.com/
Description: Adds in integration with Centermark by Enspire for Enterprise.
Version:     0.1.0
Author:      Enspire for Enterprise
Author URI:  https://enspireforenterprise.com/
Text Domain: efe-centermark
Domain Path: /lang
*/

define( 'EFE_CENTERMARK_VERSION', '0.1.0' );

define( 'EFE_CENTERMARK_BASE_URL', plugins_url( '', __FILE__ ) );
define( 'EFE_CENTERMARK_ADMIN_ASSETS_URL', path_join( plugins_url( '', __FILE__ ), 'assets/admin' ) );
define( 'EFE_CENTERMARK_FRONT_ASSETS_URL', path_join( plugins_url( '', __FILE__ ), 'assets/front' ) );

define( 'EFE_CENTERMARK_BASE_DIR', __DIR__ );
define( 'EFE_CENTERMARK_ADMIN_ASSETS_DIR', path_join( __DIR__, 'assets/admin' ) );
define( 'EFE_CENTERMARK_FRONT_ASSETS_DIR', path_join( __DIR__, 'assets/front' ) );

// classes

require_once( 'vendor/plugin-update-checker/plugin-update-checker.php');
require_once( 'classes/efe-centermark.php' );
require_once( 'classes/efe-centermark-settings.php' );
require_once( 'classes/efe-centermark-api.php' );


$efe_update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/enspireforenterprise-internal/efe-centermark',
	__FILE__,
	'efe-centermark'
);

$efe_update_checker->setAuthentication('5ba2dbb739143cd93ac594eeda5135dd61b9632f');
$efe_update_checker->getVcsApi()->enableReleaseAssets();


// shortcut
function Centermark() {

    return EFE_Centermark::instance();

}

// includes
require_once( 'includes/efe-centermark-helpers.php' );

// go go go
EFE_Centermark::instance();
