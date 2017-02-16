<?php
/*
Plugin Name: BilingualWordPress
Plugin URI: https://github.com/kojipon/wp-plugin-bilingual
Description: This plugin translates the new Wordpress page into another language and creates a new page.
Author: KOJIPON <kojipon@gmail.com>
Version: 1.0.0
Author URI: https://github.com/kojipon
*/

define('BILINGUAL_VERSION', '1.0.0');

define('BILINGUAL_FILE', __FILE__);
define('BILINGUAL_BASENAME', plugin_basename(BILINGUAL_FILE));
define('BILINGUAL_DIR', dirname(BILINGUAL_FILE));
define('BILINGUAL_INC', BILINGUAL_DIR . '/include');

require_once(BILINGUAL_INC . '/processor.php');

$bilingual = new Bilingual_Processor();

add_action('admin_menu', array($bilingual, 'set_menu_pages'));
add_action('publish_post', array($bilingual, 'run'));
add_action('private_post', array($bilingual, 'run'));
add_action('draft_post', array($bilingual, 'run'));
