<?php
/**
 * @wordpress-plugin
 * Plugin Name: 	Test PLUGIN UPDATE
 * Plugin URI: 		https://github.com/adtrak/sunshine
 * Description: 	Super Secret Sleuth
 * Version: 		1.0.0
 * Author: 			Jack Whiting
 * Author URI: 		https://jackwhiting.co.uk
 * License: 		GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     billy
 */

# if this file is called directly, abort
if (! defined( 'WPINC' )) die;

require_once('class-sunshine-updater.php');


    $updater = new SunshineUpdater(
        5,
        'Example Product',
        'example-product',
        'http://sunshine.dev/api/license-manager/v1',
        'plugin',
        __FILE__
    );
