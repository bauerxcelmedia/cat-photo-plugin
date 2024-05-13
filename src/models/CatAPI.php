<?php

namespace CatPhotoPlugin\Models;

class CatAPI {
    private $api_key;
    private $api_url;

    public function __construct() {
        // Retrieve the API key from WordPress settings
        $this->api_key = get_option('cat_photos_api_key', 'live_zj8Jfn8PLfVLqrhwRH9eN03tMWmZo8diE4PZb8UPZHbAduiQF5UQldzvUKji65da'); 
        $this->api_url = get_option('cat_photos_api_url', 'https://api.thecatapi.com/v1/images/search?breed_ids='); 
    }

    public function getCatPhoto($breed) {
        $response = wp_remote_get($this->api_url . $breed, [
            'headers' => ['x-api-key' => $this->api_key]
        ]);

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (isset($data[0]->url)) {
            return $data[0]->url;
        } 
    }
}
