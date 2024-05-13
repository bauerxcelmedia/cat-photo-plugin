<?php

namespace CatPhotoPlugin\Tests;

use WP_Mock\Tools\TestCase;
use CatPhotoPlugin\Views\AdminSettings;
use WP_Mock;

class AdminSettingsTest extends TestCase {

    public function setUp(): void {
        WP_Mock::setUp();
        parent::setUp();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testRegisterSettings() {
        $adminSettings = new AdminSettings();
    
        // Mock register_setting for all settings being registered
        WP_Mock::userFunction('register_setting', [
            [
                'times' => 1,
                'args' => ['cat_photos_options_group', 'default_cat_breed']
            ],
            [
                'times' => 1,
                'args' => ['cat_photos_options_group', 'cat_photos_api_key']
            ],
            [
                'times' => 1,
                'args' => ['cat_photos_options_group', 'cat_photos_api_url']
            ]
        ]);
    
        // Mock add_settings_section
        WP_Mock::userFunction('add_settings_section', [
            'times' => 1,
            'args' => [
                'cat_photos_main_section',
                'Main Settings',
                \WP_Mock\Functions::type('callable'), 
                'cat_photos_options_group'
            ]
        ]);
    
        // Mock add_settings_field for all fields being added
        WP_Mock::userFunction('add_settings_field', [
            [
                'times' => 1,
                'args' => [
                    'default_cat_breed_field',
                    'Default Cat Breed',
                    \WP_Mock\Functions::type('callable'), 
                    'cat_photos_options_group',
                    'cat_photos_main_section'
                ]
            ],
            [
                'times' => 1,
                'args' => [
                    'cat_photos_api_key_field',
                    'API Key',
                    \WP_Mock\Functions::type('callable'), 
                    'cat_photos_options_group',
                    'cat_photos_main_section'
                ]
            ],
            [
                'times' => 1,
                'args' => [
                    'cat_photos_api_url_field',
                    'API URL',
                    \WP_Mock\Functions::type('callable'), 
                    'cat_photos_options_group',
                    'cat_photos_main_section'
                ]
            ]
        ]);
    
        $adminSettings->registerSettings();
    
        // Assert that all expected actions and filters have been added correctly
        WP_Mock::assertHooksAdded();
    }
    

    public function testDefaultCatBreedFieldCallback() {
        $expected_value = 'Beng';
        WP_Mock::userFunction('get_option', [
            'times' => 1,
            'args' => ['default_cat_breed', ''],
            'return' => $expected_value
        ]);

        ob_start();
        $adminSettings = new AdminSettings();
        $adminSettings->defaultCatBreedFieldCallback();
        $output = ob_get_clean();

        $expected_output = '<input type="text" id="default_cat_breed" name="default_cat_breed" value="' . esc_attr($expected_value) . '" />';
        $this->assertEquals($expected_output, $output);
    }

    public function testDisplaySettingsPage() {
        WP_Mock::userFunction('get_option', [
            'times' => 1,
            'args' => ['default_cat_breed', ''],
            'return' => 'Beng' 
        ]);
    
        // Mock other WordPress functions used in displaySettingsPage
        WP_Mock::userFunction('settings_fields', [
            'times' => 1,
            'args' => ['cat_photos_options_group']
        ]);
    
        WP_Mock::userFunction('do_settings_sections', [
            'times' => 1,
            'args' => ['cat_photos_options_group']
        ]);
    
        WP_Mock::userFunction('submit_button', [
            'times' => 1
        ]);
    
        ob_start();
        $adminSettings = new AdminSettings();
        $adminSettings->displaySettingsPage();
        $output = ob_get_clean();
    
        $this->assertStringContainsString('<h1>Cat Photo Plugin Settings</h1>', $output);
    }
    

}
