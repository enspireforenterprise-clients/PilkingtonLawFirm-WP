<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache


# Database Configuration
define( 'DB_NAME', 'wp_pilkingtonprd' );
define( 'DB_USER', 'pilkingtonprd' );
define( 'DB_PASSWORD', 'EC0nRRO2A2IP_7A-il3I' );
define( 'DB_HOST', '10.173.6.77:3306' );
#define( 'DB_HOST_SLAVE', '127.0.0.1:3306' );
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');
$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         '/U;VufrN?zSdKHYF@HyZ``@VY1qwpb(+#dIUVKLnG{+?Gb}93WwLbcW-NTmA<$H-');
define('SECURE_AUTH_KEY',  'eFE@--UOE,q&6j9NDR|H|z/pEQ.2L&dQSYVs8Q%yXt|?Li$,-4dn<aYt*Fb)?AQ[');
define('LOGGED_IN_KEY',    '_]x+yPLxqf:zqWtt*`#x)!|zD_`Wu-/-Q<sk|N8Qpnx-Vb]<r&(~f2E,.Uj[sBSF');
define('NONCE_KEY',        'z6vO4masB|Kj}*udjS[K`1H?|$OLYrG/21)Y^ZpDd)C3q|Q~-M|(NG`` DG[57hu');
define('AUTH_SALT',        'o@GdzPMjMi,!U(8N&z+2Hp;*|9>tqFj%?|`iAX+kj *b}9$ZeL+A :WRNmF#>40l');
define('SECURE_AUTH_SALT', '-$Jd-v-l-LM|[3:|r9(<G-HM`Wsq6}Fn^T>2e-5=[wQ8rpbE3![<`(S7}#;[@<st');
define('LOGGED_IN_SALT',   'sq((zCT8VN#[Rnug*GD~q/@-LVE-d%Fb^T]R`wHyIYI%lVfwq}BzGHEtBXJi4oj!');
define('NONCE_SALT',       'xU.d+l}c?{4dDq+UU3^(l?3?z1cn`,3+c8D>T-2nXN8lqH#]yoB6ZZ/|UjU@Haf;');



#SSL - 4/29/24 nw
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        $_SERVER['HTTPS']='on';

# WP Engine ID


# WP Engine Settings
// Enable WP_DEBUG mode
define( 'WP_DEBUG', false );

define( 'WP_ALLOW_MULTISITE', true );


define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
define( 'DOMAIN_CURRENT_SITE', 'pilkingtonimmigration.com' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );


/** Set up remote Redis Cache **/
// change the prefix and database for each site to avoid cache data collisions
define( 'WP_REDIS_PREFIX', 'wp-pilkington' );
define( 'WP_REDIS_DATABASE', 1 ); // 0-15

// reasonable connection and read+write timeouts
define( 'WP_REDIS_TIMEOUT', 1 );
define( 'WP_REDIS_READ_TIMEOUT', 1 );

// setup redis sentinel
define( 'WP_REDIS_CLIENT', 'predis' );
define( 'WP_REDIS_SENTINEL', 'cas-redis' );
define( 'WP_REDIS_SERVERS', [
        'tcp://prod-sso-redis01.prod.yodle.com:26379',
        'tcp://prod-sso-redis02.prod.yodle.com:26379',
        'tcp://prod-sso-redis03.prod.yodle.com:26379',
        ] );

# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', __DIR__ . '/');
require_once(ABSPATH . 'wp-settings.php');
