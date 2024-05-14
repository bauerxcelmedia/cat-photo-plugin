<?php

namespace CatPhotoPlugin\Models;

class CatAPI {
    private $api_key;
    private $api_url;
    private $breed_url;

    public function __construct() {
        // Retrieve the API key from WordPress settings
        $this->api_key   = get_option( 'cat_photos_api_key', 'live_zj8Jfn8PLfVLqrhwRH9eN03tMWmZo8diE4PZb8UPZHbAduiQF5UQldzvUKji65da' ); 
        $this->api_url   = get_option( 'cat_photos_api_url', 'https://api.thecatapi.com/v1/images/search?breed_ids=' ); 
        $this->breed_url = "https://api.thecatapi.com/v1/breeds";
    }

    public function getCatPhoto($breed) {
        $response = wp_remote_get( $this->api_url . $breed, [
            'headers' => [
                'x-api-key' => $this->api_key
            ]
        ]);

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        if ( isset($data[0]->url )) {
            return $data[0]->url;
        } 
    }

    public function saveCatBreedsAsTax() {
        $response = wp_remote_get( $this->breed_url, [
            'headers' => [
                'x-api-key' => $this->api_key
            ]
        ]);

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );
        if ( is_array( $data ) ) {
            foreach ( $data as $breed ) {
                // Check if the breed already exists in the taxonomy
                $term = get_term_by( 'slug', $breed->id, 'cat_breed' );

                if (!$term) {
                    // Create a new term for the breed
                    $term_args = [
                        'description' => $breed->description,
                        'slug' => sanitize_title( $breed->id ),
                    ];

                    wp_insert_term( $breed->name, 'cat_breed', $term_args );
                }
            }
        }
    }
}
