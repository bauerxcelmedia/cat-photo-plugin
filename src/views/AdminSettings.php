<?php

namespace CatPhotoPlugin\Views;

use CatPhotoPlugin\Models\CatAPI;

class AdminSettings {
    public function __construct() {
        add_action( 'admin_init', array( $this, 'registerSettings' ) );
    }
    
    public function registerSettings() {
        // Register a new setting for "cat_photos" page
        register_setting( 'cat_photos_options_group', 'default_cat_breed' );
        register_setting( 'cat_photos_options_group', 'cat_photos_api_key' ); 
        register_setting( 'cat_photos_options_group', 'cat_photos_api_url' ); 
    
        // Register a new section in the "cat_photos" page
        add_settings_section(
            'cat_photos_main_section', 
            'Main Settings', 
            array( $this, 'catPhotosMainSectionCallback' ), 
            'cat_photos_options_group'
        );
    
        // Register a new field in the "main_section"
        add_settings_field(
            'default_cat_breed_field', 
            'Default Cat Breed', 
            array( $this, 'defaultCatBreedFieldCallback' ), 
            'cat_photos_options_group', 
            'cat_photos_main_section'
        );

        // Register API key field in the "main_section"
        add_settings_field(
            'cat_photos_api_key_field', 
            'API Key', 
            array( $this, 'apiKeyFieldCallback' ), 
            'cat_photos_options_group', 
            'cat_photos_main_section'
        );

        // Register API url field in the "main_section"
        add_settings_field(
            'cat_photos_api_url_field', 
            'API URL', 
            array( $this, 'apiUrlFieldCallback' ), 
            'cat_photos_options_group', 
            'cat_photos_main_section'
        );        
    }
    
    public function catPhotosMainSectionCallback() {
        echo '<p>Enter the default cat breed to be used by the plugin.</p>';
    }
    
    public function defaultCatBreedFieldCallback() {
        $default_breed = get_option( 'default_cat_breed', '' );
        echo '<input type="text" id="default_cat_breed" name="default_cat_breed" value="' . esc_attr( $default_breed ) . '" />';
    }

    public function apiKeyFieldCallback() {
        $api_key = get_option( 'cat_photos_api_key', '' );
        echo '<input type="text" id="cat_photos_api_key" name="cat_photos_api_key" value="' . esc_attr( $api_key ) . '" />';
    }

    public function apiUrlFieldCallback() {
        $api_url = get_option( 'cat_photos_api_url', '' );
        echo '<input type="text" id="cat_photos_api_url" name="cat_photos_api_url" value="' . esc_attr( $api_url ) . '" />';
    }
    
    public function addSettingsPage() {
        add_menu_page(
            'Cat Photo Settings',      // Page title
            'Cat Photos',              // Menu title
            'manage_options',          // Capability
            'cat-photo-settings',      // Menu slug
            array( $this, 'displaySettingsPage' ), // Callback function
            'dashicons-camera',        // Icon
            6                          // Position
        );
    }

    public function displaySettingsPage() {
        echo '<div class="wrap">';
        echo '<h1>Cat Photo Plugin Settings</h1>';
        echo '<form method="post" action="options.php">';
        
        // Settings API needs to be used here to handle settings data securely
        settings_fields( 'cat_photos_options_group' );
        do_settings_sections( 'cat_photos_options_group' );
    
        // Retrieve existing option value from the database
        $default_breed = get_option( 'default_cat_breed', '' );
        
        // Submit button
        submit_button();
    
        echo '</form>';
        echo '</div>';
    }
    
}