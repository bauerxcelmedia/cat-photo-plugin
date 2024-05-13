# Cat Photo Plugin for WordPress
The Cat Photo Plugin plugin automates the process of adding cat photos to WordPress posts. Whenever an editor mentions a specific cat breed in their content, the plugin will automatically attach a relevant photo. This reduces the time spent manually searching for cat images online.

## Features
**Automatic Photo Attachment:**  Automatically attaches a photo of a specified cat breed to the post content.

**Configurable:** Includes a settings page to set and update the default cat breed.

**Easy Integration:** Uses CatAPI to fetch cat photos dynamically.

## Requirements
PHP 7.4 or higher
WordPress 5.0 or higher
Composer for managing PHP dependencies

## Installation
1. Clone the repository:
```
git clone https://github.com/sohanyakter9/cat-photo-plugin.git
```

2. Navigate to the plugin directory:
```
cd cat-photo-plugin
```
3. Install PHP dependencies with Composer:
```
composer install
```

## Configuration
**1. Set up your API key:**

  - Sign up at [TheCatAPI](https://thecatapi.com/) to get your API key.
  - Navigate to the plugin's settings page in the WordPress admin panel.
  - Enter your API key in the designated field.

**2. Set up your API Url:**

  - Navigate to the plugin's settings page in the WordPress admin panel.
  - Enter your API URL in the designated field. **Ex: https://api.thecatapi.com/v1/images/search?breed_ids=**
    
**3. Configure the default cat breed:**

  - On the plugin's settings page, specify the default cat breed you want to use.
  - This breed will be used to fetch images if the post content does not specify a breed.

## Usage
**Creating/Updating Posts:** When you create or update a post, include the name of a cat breed within the content. The plugin will automatically fetch a corresponding image from TheCatAPI and embed it into the post.

## Testing
**PHPUnit Tests:**
To run the PHPUnit tests included with the plugin, navigate to the plugin directory and run:
```
vendor/bin/phpunit
```

## Contributions

Contributions are welcome. Please fork the repository, make your changes, and submit a pull request.

## License
This plugin is open-sourced under the MIT License.