<?php

namespace CatPhotoPlugin\Controllers;

use CatPhotoPlugin\Models\CatAPI;
use CatPhotoPlugin\Views\AdminSettings;

class ContentController {
    private $catApi;
    private $adminSettings;
    private $is_updating_post = false;

    public function __construct() {
        $this->catApi        = new CatAPI();
        $this->adminSettings = new AdminSettings();
    }

    public function run() {
        add_action( 'admin_menu', array( $this->adminSettings, 'addSettingsPage' ) );
        add_action( 'init', array( $this, 'registerCatBreedTaxonomy' ) );
        add_action( 'save_post', array( $this, 'addOrUpdatePostId' ), 20, 1 );  // Priority 20, later than save
    }

    public function install() {
        $this->registerCatBreedTaxonomy();
        // Flush the rewrite rules to make your taxonomy available immediately
        flush_rewrite_rules();
        $this->catApi->saveCatBreedsAsTax();
    }

    public function registerCatBreedTaxonomy() {
        $labels = array(
            'name'              => _x( 'Cat Breeds', 'taxonomy general name' ),
            'singular_name'     => _x( 'Cat Breed', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Cat Breeds' ),
            'all_items'         => __( 'All Cat Breeds' ),
            'parent_item'       => __( 'Parent Cat Breed' ),
            'parent_item_colon' => __( 'Parent Cat Breed:' ),
            'edit_item'         => __( 'Edit Cat Breed' ),
            'update_item'       => __( 'Update Cat Breed' ),
            'add_new_item'      => __( 'Add New Cat Breed' ),
            'new_item_name'     => __( 'New Cat Breed Name' ),
            'menu_name'         => __( 'Cat Breeds' ),
        );

        $args = array(
            'hierarchical'      => false,
            'labels'            => $labels,
            'rewrite'           => array( 'slug' => 'cat-breed' ),
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
        );

        register_taxonomy( 'cat_breed', 'post', $args );
    }

    public function addOrUpdatePostId( $post_id ) {
        // Check if this function is already running to avoid recursion
        if ( $this->is_updating_post ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $post    = get_post( $post_id );
        $content = $post->post_content;

        $this->removeDynamicCatImages( $post );
        // Retrieve the cat breed either from the meta box or the default settings
        $terms = get_the_terms( $post_id, 'cat_breed' );
        $breedStr = '';
        if ( $terms && !is_wp_error( $terms ) ) {
            $breedSlugs = wp_list_pluck( $terms, 'slug' );
            $breedStr   = implode( ',', $breedSlugs );
        }
        if ( empty( $breedStr ) ) {
            $breedStr = get_option( 'default_cat_breed', '' );
        }

        $breeds = array_map( 'trim', explode( ',', $breedStr ) );

        foreach ( $breeds as $breed ) {
            if ( $this->isBreedMentioned( $content, $breed ) ) {
                $photoUrl = $this->catApi->getCatPhoto( $breed );
                if ( $photoUrl && !$this->isBreedImagePresent( $content, $breed ) ) {
                    $content = $this->appendImageToContent( $content, $breed, $photoUrl );
                }
            }
        }

        if ( !empty( $breeds ) ) {
            $this->is_updating_post = true;
            wp_update_post( ['ID' => $post_id, 'post_content' => $content] );
            $this->is_updating_post = false;
        }
    }

    protected function removeDynamicCatImages( $content ) {
        $pattern = '/<!--\s*wp:image\s*({.*?"className":"dynamic-cat-image".*?})\s*-->(.*?)<!--\s*\/wp:image\s*-->/s';
        $content = preg_replace( $pattern, '', $content );
        return $content;
    }

    protected function isBreedMentioned( $content, $breed ) {
        return stripos( $content, $breed ) !== false;
    }

    protected function isBreedImagePresent( $content, $breed ) {
        return stripos( $content, 'breed-type-image-for-' . esc_attr( $breed ) ) !== false;
    }

    protected function appendImageToContent( $content, $breed, $photoUrl ) {
        $imageMarkup = "<!-- wp:image {\"className\":\"dynamic-cat-image breed-image breed-type-image-for-" . esc_attr( $breed ) . "\"} -->";
        $imageMarkup .= "<figure class=\"wp-block-image breed-type-image-for-" . esc_attr( $breed ) . "\">";
        $imageMarkup .= "<img src=\"" . esc_url( $photoUrl ) . "\" alt=\"" . esc_attr( $breed ) . "\">";
        $imageMarkup .= "</figure>";
        $imageMarkup .= "<!-- /wp:image -->";
        return $content . $imageMarkup;
    }

    public function saveCatBreed( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( !isset( $_POST['cat_photo_breed_nonce'] ) || !wp_verify_nonce( $_POST['cat_photo_breed_nonce'], 'cat_photo_save_breed' ) ) {
            return;
        }

        // Save or delete the meta field based on input presence
        $cat_breed = isset( $_POST['cat_breed'] ) ? sanitize_text_field( $_POST['cat_breed'] ) : '';
        update_post_meta( $post_id, '_cat_breed', $cat_breed );
    }

    public function addCatMetaBox() {
        add_meta_box(
            'cat_photo_breed',           // ID of the meta box
            'Cat Breed',                 // Title of the meta box
            array( $this, 'renderCatMetaBox' ), // Callback function to display the meta box
            'post',                      // Post type where the meta box will appear
            'side',                      // Context where the box will appear ('normal', 'side', 'advanced')
            'high'                       // Priority of where the box should appear
        );
    }

    public function renderCatMetaBox( $post ) {
        // Retrieve the current breed from the post meta or fall back to the default setting
        $breed = get_post_meta( $post->ID, '_cat_breed', true );
        if ( empty( $breed ) ) {
            $breed = get_option( 'default_cat_breed', '' );  // Default value from plugin settings
        }

        // Nonce field for security
        wp_nonce_field( 'cat_photo_save_breed', 'cat_photo_breed_nonce' );

        // HTML for the input field
        echo '<label for="cat_breed">Cat Breed:</label>';
        echo '<input type="text" id="cat_breed" name="cat_breed" value="' . esc_attr( $breed ) . '" style="width:100%;" />';
    }
}