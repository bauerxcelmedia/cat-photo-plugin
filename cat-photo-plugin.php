<?php
/*
Plugin Name: Cat Photo Plugin
Description: Automatically adds cat photos to posts containing cat breeds.
Version: 1.0
Author: Sohany Akter
*/

if (!defined('WPINC')) {
    die;
}
require_once __DIR__ . '/vendor/autoload.php';

use CatPhotoPlugin\Controllers\ContentController;

function run_cat_photo_plugin() {
    $plugin = new ContentController();
    $plugin->run();
}

run_cat_photo_plugin();


register_activation_hook(__FILE__, function() {
    // Adding available cat breed as taxonomy so that it can be selected by editor.
    $plugin = new ContentController();
    $plugin->install();
});


