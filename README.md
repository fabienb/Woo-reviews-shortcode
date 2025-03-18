# WooCommerce Reviews shortcodes

![Latest Version](https://img.shields.io/badge/release-v1.1.5-orange)
[![WordPress Version](https://img.shields.io/badge/wordpress-%3E%3D6.5-00749c)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.0-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0.html)

## Description

I had an issue with WooCommerce and the configuration of the theme I was using (Ohio by Colabrio, via Themeforest) that somehow prevented Customer Reviews from appearing in the Product pages.
I decided to retrieve the reviews from the database and then create a shortcode to display those reviews in a custom tab. 

This is the PHP that creates such shortcode. 

**I am happy to release this snippet for FREE. But if this is helpful to you in any way, please consider donating via [Paypal](https://paypal.me/fabienbutazzi) or use the Sponsor links in the sidebar to support this work and future enhancements.**


## Installation

Copy the raw PHP code you find in `snippet.php` and add it to your WordPress. This can be done in 2 different ways: 
- editing the `functions.php` file in your theme, which I would never recommend unless you are using a child theme; 
- adding this code into a snippets manager plugin.

My favourite snippets manager is **Fluent Snippets**. I do not have any affiliation with them, it's simply the plugin I use because it's free and does not bloat the database but keeps all snippets in separate external files (easier to manage, backup and transfer and also much better performance). 
Whatever your preference, this snippet works in whatever plugin you are using.

Of course, *always make a backup copy of your current WordPress before making any changes*.

## Usage

In a custom tab, you can now add 2 new shortcodes:
- `product_reviews`: displays all the existing reviews as retrieved from the database
- `product_review_form`: adds a form for more reviews to be added by your customers

## Requirements

There are no particular requirements, but this has been tested in the following conditions:
- WordPress 5.8 and higher
- PHP 7.4 and higher

## Dependencies

- WordPress and WooCommerce, for obvious reasons
- WordPress plugin to create custom tabs for products in WooCommerce (I use Booster, the free version allows 1 custom tab)
- WordPress plugin to manage snippets (I use FluentSnippets, see above) or using a child theme.

## Support

For support, please create an [issue](https://github.com/fabienb/Woo-reviews-shortcode/issues) on GitHub.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License

This plugin is licensed under the [GNU General Public License v3 or later](LICENSE).
