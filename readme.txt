=== Minimum Signup Period For WooCommerce Subscriptions ===
Contributors: RelyWP
Tags: woocommerce,subscriptions,signup,period,term,minimum,upfront,trial
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LQ59TEPA5PTHE&source=url
Requires at least: 4.7
Tested up to: 6.0.0
Stable tag: trunk
License: GPLv3 or later.

Allows you to create a minimum signup period for the official WooCommerce subscriptions plugin.

== Description ==

Allows you to create a minimum signup period for the official "WooCommerce Subscriptions" plugin.

The customer will pay the full total for the initial minimum period (for example first 3 months), then after that period ends, the subscription will renew as normal each month.

This plugin simply calculates the initial subtotal for the cart based on the minimum signup period, then once the user signs up, the next payment date for the subscription is automatically updated.

- Works well with product addons plugins etc.
- Does NOT use "trial" period functionality.

<strong>Suggestions or found a bug?</strong>

If you have any suggestions for additional functionality, or have found a bug, please get in touch.

== Installation ==

1. Upload 'woo-sub-minimum-signup-period' to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your settings on the plugins settings page.

== Frequently Asked Questions ==

Will there be more features added in the future?

If you have any suggestions, please let me know!

Need support? Feel free to get in touch and I'll be happy to help.

== Screenshots ==

1. Example Checkout (May look different based on your own site/theme settings etc)
2. Settings Page

== Changelog ==

Version 1.1.0<br>
- Redeveloped some of the code, and fixed a bug causing the plugin not working on some sites.
- Added option: Don't allow non-subscription products to be added to the same cart as a subscription.
- Added link to settings in plugins list, and moved the admin menu link to under the general "Settings" menu.
- Added localisation support.
- Tested with WordPress 6.0.0

Version 1.0.6<br>
- Update to not set the minimum signup period when user is paying for a renewal payment manually.
- Fixed array offset error on admin settings page.

Version 1.0.5<br>
- Fixed bug with plugin not working for certain products.

Version 1.0.4<br>
- Fixed bug with recurring total not always re-calcuating properly.

Version 1.0.3<br>
- Fixed a bug.

Version 1.0.2<br>
- Fixed a bug.

Version 1.0.1<br>
- Added redirect to settings page on activate.

Version 1.0.0<br>
- Initial Release.

== Upgrade Notice ==

None.