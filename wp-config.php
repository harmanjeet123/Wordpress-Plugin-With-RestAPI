<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dbzdiglgg1xvss' );

/** MySQL database username */
define( 'DB_USER', 'ud0kdd5qf7tf2' );

/** MySQL database password */
define( 'DB_PASSWORD', '9q3kzbxdvzdj' );

/** MySQL hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'Ca!YJ@Q #87F<1Qx;.%LldK5^;)9E}Mf$%q[s;IEHPQK1a2^+mxg^c7/,xe/a-Ks' );
define( 'SECURE_AUTH_KEY',   'wJhu/frV<5U1~fO6$PADpkFs@1IT1^|;7c4geqZ+<.Y={dlDTW-)Qx0}dp`I<YyW' );
define( 'LOGGED_IN_KEY',     'HNa+#cmI=Ru|kB6_,! A~yqggs)>r`;]*tHM&mF:J,~(~z f8!xbWCuy 14FVou!' );
define( 'NONCE_KEY',         'KBi45ugvJA3b{%&]fv_1Ovw>M QReG3Bog$c7Q7{uqC,^QPTYl,^sSo->(YZ[* I' );
define( 'AUTH_SALT',         'VO*aG[%4GfQJEh*/J=~Lv>gFmI>9*Io7va)~^%)`vEp8|V%FmA#>JXCA3e60vH9c' );
define( 'SECURE_AUTH_SALT',  '& hv6xHsxOYKISTc4JF<Q_1DJQ3[pyT+^<b-^h`leWgeKMr6zTNVHvj_-f_w&~p$' );
define( 'LOGGED_IN_SALT',    ')D;)%qHG,A4E,AiAEfgMvh(@BG%r.lE*L/2!R-n{4![d+M:Ilh73eU/~~9K9G!T ' );
define( 'NONCE_SALT',        'Sp%@O5*gD-aZfJFi$ZY{tP84!UL_+]^s%/d2Y/ee.F#<wW$Lj&]$D][z BkfNok5' );
define( 'WP_CACHE_KEY_SALT', 'qKq,kp82dL:]VLJZ,[%?BO,UA5l;EPe|[d[!N fIDAM>15(wMM9`qib<JBlV`-y?' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'jde_';
define('WP_DEBUG', false);

// Enable Debug logging to the /wp-content/debug.log file
define('WP_DEBUG_LOG', true);

// Disable display of errors and warnings 
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors',0);



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system
