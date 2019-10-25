<?php
/*
Plugin Name: سامانه سنجش روان    pas-os.com
Plugin URI:  https://pas-os.com/
Description: افزونه اتصال به سامانه سنجش روان
Version:     1
Author:      پردانش
Author URI:  https://pardanesh.com/
License:     PDHS

*/
if (!defined('ABSPATH')) exit;
define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );
require("libs/class-pas-os-api.php");
require("libs/class-pas-os-form.php");
require("libs/class-pas-os-pages.php");
require("libs/class-pas-os-questions.php");
require("libs/class-pas-os-answers.php");
require("libs/class-pas-os-clients.php");
require("pas-os-funs.php");
register_activation_hook( __FILE__, 'pardanesh_pasos_install' );
function pas_os_load(){
     new Pas_Os_Pages();
}
pas_os_load();
function pardanesh_pasos_install() {
    update_option( 'pasos_api_url', 'https://pas-os.com/api_v2/' );
}