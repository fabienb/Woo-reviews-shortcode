# woo-reviews-shortcode

I had a an issue with Woocommerce and the configuration of the theme I was using (Ohio by Colabrio via Themeforest) that prevented Customer Reviews from appearing in Products.
I tried the Booster plugin to create additional custom tabs and I thought I could retrieve the reviews from the database and then create a shortcode to display those reviews in a custom tab. This is the PHP that creates such shortcode. 

If this is helpful to you, please consider donating via [Paypal](https://paypal.me/fabienbutazzi) to support this work and future enhancements. Thanks!

## Installation

Copy the raw PHP code you found in snippet.php and add it to your WordPress. This can be done in 2 different ways: 
- editing the functions.php file in your theme, which I would never recommend unless you are using a child theme and do so there; 
- adding this code to a snippet manager plugin.

My favourite snippets manager is Fluent Snippets. I do not have any affiliation with them, it's simply the plugin I use because it's free and  does not bloat the database but keeps all snippets in separate external files (easier to manage, backup and transfer and also much better performance). But this snippet works in whatever plugin you are using.

Of course always make a backup copy of your current WordPress before making any change.

## Requirements

There are no particular requirements, but this has been tested in the following conditions:
- WordPress 5.8 and higher
- PHP 7.4 and higher

## Support

For support, please create an issue on GitHub.

## License

GPL v3 or later