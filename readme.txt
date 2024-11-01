=== User Mail Only Register ===
Contributors: Katsushi Kawamori
Donate link: https://shop.riverforest-wp.info/donate/
Tags: email, register, users
Requires at least: 4.7
Requires PHP: 8.0
Tested up to: 6.6
Stable tag: 2.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Register users with mail only.

== Description ==

= Register users with mail only =
* Register only email address.
* Can check the terms of use agreement.
* WordPress : `wp-login.php?action=register`
* shortcode : `[umorregister]`

= Filter for shortcode form =
~~~
/** ==================================================
 * Filter for message.
 *
 */
add_filter( 'umor_register_success_msg', function(){ return 'Message for register success.'; }, 10, 1 );
add_filter( 'umor_login_success_login_msg', function(){ return 'Message for login success.'; }, 10, 1 );
add_filter( 'umor_register_error', function(){ return 'Message for register error.'; }, 10, 1 );
add_filter( 'umor_register_nomail', function(){ return 'Message for unentered mail.'; }, 10, 1 );
add_filter( 'umor_register_noterm', function(){ return 'Message for unentered term of use.'; }, 10, 1 );
add_filter( 'umor_register_form_label', function(){ return 'Message for form label.'; }, 10, 1 );
add_filter( 'umor_register_term_of_use', function(){ return 'Message for term of use.'; }, 10, 1 );
add_filter( 'umor_not_register_message', function(){ return 'Message for not register.'; }, 10, 1 );
~~~
~~~
/** ==================================================
 * Filter for Term of use URL.
 *
 */
add_filter(
	'umor_register_term_of_use_url',
	function( $term_of_use_url ) {
		if ( 'ja' === get_locale() ) {
			$term_of_use_url = 'https://test.com/ja/';
		}
		return $term_of_use_url;
	},
	10,
	1
);
~~~
~~~
/** ==================================================
 * Filter for input text size.
 *
 */
add_filter( 'umor_register_input_size', function(){ return 17; }, 10, 1 );
~~~
~~~
/** ==================================================
 * Filter for class name.
 *
 */
add_filter( 'umor_register_form_class_name', function(){ return 'myform'; }, 10, 1 );
add_filter( 'umor_register_label_class_name', function(){ return 'mylabel'; }, 10, 1 );
add_filter( 'umor_register_input_class_name', function(){ return 'myinput'; }, 10, 1 );
add_filter( 'umor_register_check_form_class_name', function(){ return 'mycheckform'; }, 10, 1 );
add_filter( 'umor_register_check_class_name', function(){ return 'mycheck'; }, 10, 1 );
add_filter( 'umor_register_submit_class_name', function(){ return 'mysubmit'; }, 10, 1 );
~~~

== Installation ==

1. Upload `user-mail-only-register` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

none

== Screenshots ==

1. Register form by WordPress
2. Register form by shortcode
3. Settings

== Changelog ==

= [2.12] 2024/03/04 =
* Fix - Elimination of short code attribute values.

= 2.11 =
Changed parse_url to wp_parse_url.

= 2.10 =
Supported WordPress 6.4.
PHP 8.0 is now required.

= 2.09 =
Added escaping process.

= 2.08 =
Added escaping process.

= 2.07 =
Added url filter('umor_register_term_of_use_url') for term of use.

= 2.06 =
Added "aria-label" attributes to the checkbox form.

= 2.05 =
Added class name filter('umor_register_form_class_name') for register form.
Added class name filter('umor_register_check_form_class_name') for register form.
Added "placeholder" and "required" attributes to the email input form.
Added "required" attributes to the checkbox input form.

= 2.04 =
Changed input size.

= 2.03 =
Fixed escape for form.

= 2.02 =
Added validation to the email address input field of the shortcode form.

= 2.01 =
Added some filters.
Change readme.txt.

= 2.00 =
Added a original login form with shortcode.
The block has been removed.

= 1.12 =
Fixed a problem with the password reset link.

= 1.11 =
Fixed a problem with the password reset link.

= 1.10 =
Supported WordPress 5.6.

= 1.09 =
Added function by hide the link to "Log in" and "Lost your password".
Changed management screen.

= 1.08 =
Fixed shortcode.

= 1.07 =
Fixed sample code.

= 1.06 =
The block now supports ESNext.

= 1.05 =
Conformed to the WordPress coding standard.

= 1.04 =
Add WordPress user settings.

= 1.03 =
Described the hook description on the management screen.

= 1.02 =
Fixed error on activation.
Added a filter "umor_not_register_message" for messages when user registration is not allowed.

= 1.01 =
Can add text at email notification.
Can stop mail notification of this plugin.

= 1.00 =
Initial release.

== Upgrade Notice ==

= 2.00 =
Added a original login form with shortcode.
The block has been removed.

= 1.00 =
Initial release.

