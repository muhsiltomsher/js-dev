<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
/** Memory Limit */
define('WP_MEMORY_LIMIT', '128M');
define( 'WP_MAX_MEMORY_LIMIT', '128M' );
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'tomsher2022_junior-salon25' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'Ha/B;&77w:2_K28XPfs%3)5[c!c(br62(1@pa8|8O5J;0WxL(4zz;)u9qT#4Krp2');
define('SECURE_AUTH_KEY', 'y8Zfa1Rqbor50*p2s|;8/W8di63u4s0E)q26CA9]BcrU6i_0J8W4(3O8yV4n+a:V');
define('LOGGED_IN_KEY', 'Ku1(!&1%uz8[[&s2SxgFWWo/3(4jKDLGn3j%P4HL%D7HnqL4E-U~CtpZxaoW|#Wn');
define('NONCE_KEY', 'N_]yD#Z7-P5HD04h])BW]jDRGL:&Z@]5-jff(h+/(2/v|/#53fGf0HIayo9o10Ob');
define('AUTH_SALT', 'P*7SH/w_(@s[Q2e:zxA*(EU7~4v-G~6qE]/3s4b0g3-1Mg7f7lW@sFE]6uQw7#C-');
define('SECURE_AUTH_SALT', '10ee6AL#L937QYPgv#30X:G&957q~U1Ju7I4Yh[kI:+6n991a&@40#6*M/G(|F97');
define('LOGGED_IN_SALT', '-3IWi6(l0)o0sYi|p8y*P)z216xgFOWG14pnM:&29+JPo!!xf)c_4&%NA&D[w*23');
define('NONCE_SALT', 'ZQ0f995-%c0e063u7/r10r5G1(31@98:C816N1%9Z~W3844xx0]0!iVbreNK-Q31');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'QG6dosyx_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
