<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'msikomul_aura' );

/** Database password */
define( 'DB_PASSWORD', 'X&bZEvHh!qYaLF2x$uaeF@u4+' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'u40lVQon_mzP?%@-Y~(w[O,1e6N]AR@O0]pn0Ef9Eb!&onEAD |xj-=OiFo( rL;' );
define( 'SECURE_AUTH_KEY',  ')L9[J6>9gepJt~H21cvqLWy,!Gc5B=_eJ~~a|Gw<.0CyyXZ~`%AfAbXuUjXifZs' );
define( 'LOGGED_IN_KEY',    'GI+2/qgpe!4@-pD`D|}<-l[n!VsC2-e#y/b%EFySp1lEpad9D>a_..*ay#83S36u' );
define( 'NONCE_KEY',        ',s]ZiS+-kv|1)VyfjBsum})(S)_Wx>0LE:E0U/Ae+TLqL-$gU(bl[Qs`PfOmkT ' );
define( 'AUTH_SALT',        '1eCG71qY+zTe=bK53e:F917O+b-PA B54C;8A->Qcx&lBzT3+H%k]E*s<~I:@(Xa' );
define( 'SECURE_AUTH_SALT', 'QQe18l4g.TIsEKCvA!,fU@zWGUzX`_6F>Kpa(sX0U7_5amws[~)F5RN@@}1S8(xd' );
define( 'LOGGED_IN_SALT',   '&|lV).n4}Hm2U4KVR|uzaCgYc8E9C3dyfz{y%lRQ(~)3j0)I!xB#5!i^r5bG{pg5' );
define( 'NONCE_SALT',       '(tQ+TUwv~~+6.@&K!{:B,nUrD_yPT_(<LN~/xf_ZbU>5VY0j`VoQA1>Gt+~,s0;p' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG',false);

/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_MEMORY_LIMIT', '512M' );

define( 'WP_DEBUG_LOG',false);
define( 'WP_DEBUG_DISPLAY',false);
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
