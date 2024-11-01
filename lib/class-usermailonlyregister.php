<?php
/**
 * User Mail Only Register
 *
 * @package    UserMailOnlyRegister
 * @subpackage User Mail Only Register Main function
/*  Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$usermailonlyregister = new UserMailOnlyRegister();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class UserMailOnlyRegister {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_filter( 'login_message', array( $this, 'login_message' ) );
		add_filter( 'login_headerurl', array( $this, 'custom_login_logo_link_url' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'custom_login_logo' ) );
		add_action( 'register_form', array( $this, 'only_email_register_form' ) );
		add_action( 'login_form', array( $this, 'all_login_form' ) );
		add_filter( 'registration_errors', array( $this, 'loginpresss_terms_of_use_auth' ), 10, 3 );
		add_action( 'user_register', array( $this, 'loginpress_terms_of_use_save' ) );
		add_filter( 'sanitize_user', array( $this, 'sanitize_user_email' ), 10, 3 );
		add_filter( 'validate_username', array( $this, 'validate_user_email' ), 10, 2 );
		add_filter( 'authenticate', array( $this, 'email_login' ), 20, 3 );

		if ( get_option( 'usermailonlyregister' ) ) {
			$umor_settings = get_option( 'usermailonlyregister' );
			if ( array_key_exists( 'notify_mail_use', $umor_settings ) && $umor_settings['notify_mail_use'] ) {
				add_filter( 'wp_new_user_notification_email', array( $this, 'regist_user_notify_mail' ), 10, 3 );
			}
		}
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_sitename' ) );
		add_filter( 'wp_mail_from', array( $this, 'mail_from_admin_email' ) );

		add_shortcode( 'umorregister', array( $this, 'umorregister_func' ) );
	}

	/** ==================================================
	 * Register short code
	 *
	 * @return html
	 * @since 2.00
	 */
	public function umorregister_func() {

		$html = null;

		$terms_of_use = false;
		$account = null;
		$registered = null;
		if ( isset( $_POST['umor-submit'] ) ) {
			if ( ! empty( $_POST['nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
				if ( wp_verify_nonce( $nonce, 'umor_register_request' ) ) {
					if ( ! empty( $_POST['umor_user_email_register'] ) ) {
						$account = sanitize_email( wp_unslash( $_POST['umor_user_email_register'] ) );
					}
					if ( ! empty( $_POST['lp_terms_of_use'] ) ) {
						$terms_of_use = true;
					}
					if ( $terms_of_use && ! is_null( $account ) ) {
						$registered = $this->register_account( $account );
					}
				}
			}
		}

		if ( $terms_of_use && ! is_null( $account ) && ! is_wp_error( $registered ) ) {
			$html .= '<p style="background-color: #e7f7d3;">' . esc_html( apply_filters( 'umor_register_success_msg', __( 'Please check your email. You will soon receive an email with successful registration.', 'user-mail-only-register' ) ) ) . '</p>';
		} else if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			/* translators: %1$s author_archive_link %2$s logout link  */
			$html .= '<p style="background-color: #e7f7d3;">' . wp_kses_post( apply_filters( 'umor_login_success_login_msg', sprintf( __( 'You are currently logged in as %1$s. %2$s', 'user-mail-only-register' ), '<a href="' . admin_url() . '" title="' . $current_user->display_name . '">' . esc_html( $current_user->display_name ) . '</a>', '<a href="' . esc_url( wp_logout_url( $this->curpageurl() ) ) . '" title="' . esc_attr__( 'Log out of this account', 'user-mail-only-register' ) . '">' . esc_attr__( 'Log out', 'user-mail-only-register' ) . ' &raquo;</a>' ) ) ) . '</p><!-- .alert-->';
		} else {
			if ( is_wp_error( $registered ) ) {
				$html .= '<p style="background-color: #ffebe8;">' . esc_html( apply_filters( 'umor_register_error', $registered->get_error_message() ) ) . '</p>';
			}
			if ( is_null( $account ) && ! empty( $_POST['umor-submit'] ) ) {
				$html .= '<p style="background-color: #ffebe8;">' . esc_html( apply_filters( 'umor_register_nomail', __( 'Please enter your email address.', 'user-mail-only-register' ) ) ) . '</p>';
			}
			if ( ! $terms_of_use && ! empty( $_POST['umor-submit'] ) ) {
				$html .= '<p style="background-color: #ffebe8;">' . esc_html( apply_filters( 'umor_register_noterm', __( 'Please accept the terms of use.', 'user-mail-only-register' ) ) ) . '</p>';
			}
			if ( get_option( 'users_can_register' ) ) {
				$umor_settings   = get_option( 'usermailonlyregister' );
				/* Register */
				$form_class_name = apply_filters( 'umor_register_form_class_name', null );
				$label = apply_filters( 'umor_register_form_label', __( 'Register with email', 'user-mail-only-register' ) );
				$label_class_name = apply_filters( 'umor_register_label_class_name', null );
				$input_class_name = apply_filters( 'umor_register_input_class_name', null );
				$check_form_class_name = apply_filters( 'umor_register_check_form_class_name', null );
				$check_class_name = apply_filters( 'umor_register_check_class_name', null );
				$input_size = apply_filters( 'umor_register_input_size', 17 );
				$submit_class_name = apply_filters( 'umor_register_submit_class_name', null );
				$html .= '<form action="' . get_the_permalink() . '" method="post" class="' . esc_attr( $form_class_name ) . '">';
				$html .= '<label for="umor_user_email_register" class="' . esc_attr( $label_class_name ) . '">' . esc_html( $label ) . '</label>';
				$html .= '<input type="text" inputmode="url" pattern="[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" name="umor_user_email_register" id="umor_user_email_register" class="' . esc_attr( $input_class_name ) . '" value="' . esc_attr( $account ) . '" size="' . esc_attr( $input_size ) . '" placeholder="' . esc_attr__( 'Email' ) . '" required="required" />';
				$html .= '<div class="' . esc_attr( $check_form_class_name ) . '">';
				$html .= '<label for="lp_terms_of_use">';
				$termsofuse_link = '<a href ="' . esc_url( apply_filters( 'umor_register_term_of_use_url', $umor_settings['termofuse'] ) ) . '">' . esc_html( apply_filters( 'umor_register_term_of_use', __( 'terms of use', 'user-mail-only-register' ) ) ) . '</a>';
				$html .= '</label>';
				$html .= '<input type="checkbox" name="lp_terms_of_use" id="lp_terms_of_use" aria-label="' . esc_attr__( 'terms of use', 'user-mail-only-register' ) . '" class="' . esc_attr( $check_class_name ) . '" required="required" />&nbsp;&nbsp;';
				/* translators: %1$s: link of terms of use */
				$html .= sprintf( __( 'Agree the %1$s.', 'user-mail-only-register' ), wp_kses_post( $termsofuse_link ) );
				$html .= '</div>';
				$html .= '<p>';
				$html .= '<input type="submit" name="umor-submit" id="umor-submit" class="' . esc_attr( $submit_class_name ) . '" value="' . esc_attr__( 'Register' ) . '" />';
				$html .= '</p>';
				$html .= wp_nonce_field( 'umor_register_request', 'nonce' );
				$html .= '</form>';
			} else {
				$html .= apply_filters( 'umor_not_register_message', esc_html__( 'User registration is currently not allowed.' ) );
			}
		}

		return $html;
	}

	/** ==================================================
	 * Register account from email.
	 *
	 * @param string $email_account  email_account.
	 * @return bool / WP_Error
	 * @since 2.00
	 */
	private function register_account( $email_account ) {

		$valid_email = $this->valid_account( $email_account );
		$errors = new WP_Error();
		if ( is_wp_error( $valid_email ) ) {
			$errors->add( 'invalid_account', $valid_email->get_error_message() );
		} else {
			$user_pswd = wp_generate_password( 12, false, false );
			$userdata = array(
				'user_login' => $email_account,
				'user_email' => $email_account,
				'user_pass'  => wp_hash_password( $user_pswd ),
				'role'       => get_option( 'default_role' ),
			);
			$user_id = wp_insert_user( $userdata );
			wp_new_user_notification( $user_id, null, 'both' );
			if ( is_wp_error( $user_id ) ) {
				$errors->add( 'email_not_register', __( 'There was a problem sending your email. Please try again or contact an admin.', 'user-mail-only-register' ) );
			}
		}
		$error_codes = $errors->get_error_codes();

		if ( empty( $error_codes ) ) {
			return false;
		} else {
			return $errors;
		}
	}

	/** ==================================================
	 * Checks to see if an account is valid.
	 *
	 * @param string $account  account.
	 * @return bool / WP_Error
	 * @since 2.00
	 */
	private function valid_account( $account ) {

		if ( is_email( $account ) ) {
			$account = sanitize_email( $account );
			if ( email_exists( $account ) ) {
				return new WP_Error( 'invalid_account', __( 'That email address is already in use. Please try a different email address.', 'user-mail-only-register' ) );
			} else {
				return $account;
			}
		} else {
			return new WP_Error( 'not_email', __( 'It is not an email address. You can register with only your email address.', 'user-mail-only-register' ) );
		}
	}

	/** ==================================================
	 * Returns the current page URL
	 *
	 * @return string
	 * @since 2.00
	 */
	private function curpageurl() {

		$url_path = wp_parse_url( home_url(), PHP_URL_PATH );
		$home_path = null;
		if ( $url_path ) {
			$home_path = trim( $url_path, '/' );
			$home_path_regex = sprintf( '|^%s|i', preg_quote( $home_path, '|' ) );
		}

		$req_uri = null;
		if ( isset( $_SERVER['REQUEST_URI'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$req_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

			/* Trim path info from the end and the leading home path from the front. */
			if ( ! is_null( $home_path ) ) {
				$req_uri = ltrim( $req_uri, '/' );
				$req_uri = preg_replace( $home_path_regex, '', $req_uri );
				$req_uri = trim( home_url(), '/' ) . '/' . ltrim( $req_uri, '/' );
			}
		}

		return $req_uri;
	}

	/** ==================================================
	 * Login Message
	 *
	 * @since 1.00
	 */
	public function login_message() {

		$umor_settings = get_option( 'usermailonlyregister' );
		$message       = '<p class="message">' . $umor_settings['login_message'] . '</p>';
		return $message;
	}

	/** ==================================================
	 * Login Screen
	 *
	 * @since 1.00
	 */
	public function custom_login_logo_link_url() {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		$umor_settings = get_option( 'usermailonlyregister' );
		if ( empty( $umor_settings['login_logo_link_url'] ) ) {
			return;
		}
		return $umor_settings['login_logo_link_url'];
	}

	/** ==================================================
	 * Login Screen Icon Link
	 *
	 * @since 1.00
	 */
	public function custom_login_logo() {
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		$umor_settings = get_option( 'usermailonlyregister' );
		if ( empty( $umor_settings['login_logo_url'] ) ) {
			return;
		}
		?>
		<style>
		.login #login h1 a {
			width: 150px;
			height: 150px;
			background: url(<?php echo esc_url( $umor_settings['login_logo_url'] ); ?>) no-repeat 0 0;
		}
		</style>
		<?php
	}

	/** ==================================================
	 * Only E-mail register form
	 *
	 * @since 1.00
	 */
	public function only_email_register_form() {
		?>
		<style>#registerform > p:first-child{display:none;}</style>
		<p>
		<input type="checkbox" name="lp_terms_of_use" id="lp_terms_of_use" class="checkbox" />
		<?php
		wp_nonce_field( 'terms_check', 'lp_terms_of_use_nonce' );
		$umor_settings   = get_option( 'usermailonlyregister' );
		$termsofuse_link = '<a href ="' . esc_url( $umor_settings['termofuse'] ) . '">' . esc_html__( 'terms of use', 'user-mail-only-register' ) . '</a>';
		/* translators: %1$s: link of terms of use */
		$message = sprintf( __( 'Agree the %1$s.', 'user-mail-only-register' ), $termsofuse_link );
		echo wp_kses_data( $message );
		?>
		</p>
		<br />
		<?php
		if ( $umor_settings['none_nav_link'] ) {
			?>
			<style>#nav { display:none; }</style>
			<?php
		}
	}

	/** ==================================================
	 * All login form
	 *
	 * @since 1.11
	 */
	public function all_login_form() {
		$umor_settings = get_option( 'usermailonlyregister' );
		if ( $umor_settings['none_nav_link'] ) {
			?>
			<style>#nav { display:none; }</style>
			<?php
		}
	}

	/** ==================================================
	 * Terms of use for register form
	 *
	 * @param object $errors  errors.
	 * @param string $sanitized_user_login  sanitized_user_login.
	 * @param string $user_email  user_email.
	 * @since 1.00
	 */
	public function loginpresss_terms_of_use_auth( $errors, $sanitized_user_login, $user_email ) {

		if ( ! isset( $_POST['lp_terms_of_use'] ) && empty( $_POST['lp_terms_of_use'] ) ) {
			if ( isset( $_POST['lp_terms_of_use_nonce'] ) && ! empty( $_POST['lp_terms_of_use_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['lp_terms_of_use_nonce'] ) );
				if ( wp_verify_nonce( $nonce, 'terms_check' ) ) {
					$errors->add( 'terms_of_use_error', __( 'Please accept the terms of use.', 'user-mail-only-register' ) );
					return $errors;
				}
			}
		}

		return $errors;
	}

	/** ==================================================
	 * Terms of use save user meta data
	 *
	 * @param int $user_id  user_id.
	 * @since 1.00
	 */
	public function loginpress_terms_of_use_save( $user_id ) {

		if ( isset( $_POST['lp_terms_of_use'] ) && ! empty( $_POST['lp_terms_of_use'] ) ) {
			if ( isset( $_POST['lp_terms_of_use_nonce'] ) && ! empty( $_POST['lp_terms_of_use_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['lp_terms_of_use_nonce'] ) );
				if ( wp_verify_nonce( $nonce, 'terms_check' ) ) {
					update_user_meta( $user_id, 'lp_terms_of_use', true );
				}
			}
		}
	}

	/** ==================================================
	 * Sanitize Email for only E-mail register
	 *
	 * @param string $sanitized_user  sanitized_user.
	 * @param string $raw_user  raw_user.
	 * @param bool   $strict  strict.
	 * @since 1.00
	 */
	public function sanitize_user_email( $sanitized_user, $raw_user, $strict ) {

		if ( '' !== $raw_user ) {
			return $sanitized_user;
		}

		if ( isset( $_POST['user_email'] ) && ! empty( $_POST['user_email'] ) ) {
			if ( isset( $_POST['lp_terms_of_use_nonce'] ) && ! empty( $_POST['lp_terms_of_use_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['lp_terms_of_use_nonce'] ) );
				if ( wp_verify_nonce( $nonce, 'terms_check' ) ) {
					$mail = sanitize_email( wp_unslash( $_POST['user_email'] ) );
					if ( ! empty( $_REQUEST['action'] ) && 'register' === $_REQUEST['action'] && is_email( $mail ) ) {
						return $mail;
					}
				}
			}
		}

		return $sanitized_user;
	}

	/** ==================================================
	 * Validate Email for only E-mail register
	 *
	 * @param bool   $valid  valid.
	 * @param string $username  username.
	 * @since 1.00
	 */
	public function validate_user_email( $valid, $username ) {

		if ( $valid ) {
			return $valid;
		}

		if ( isset( $_POST['user_email'] ) && ! empty( $_POST['user_email'] ) ) {
			if ( isset( $_POST['lp_terms_of_use_nonce'] ) && ! empty( $_POST['lp_terms_of_use_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['lp_terms_of_use_nonce'] ) );
				if ( wp_verify_nonce( $nonce, 'terms_check' ) ) {
					$mail = sanitize_email( wp_unslash( $_POST['user_email'] ) );
					if ( ! empty( $_REQUEST['action'] ) && 'register' === $_REQUEST['action'] && is_email( $mail ) ) {
						return true;
					}
				}
			}
		}

		return is_email( $username );
	}

	/** ==================================================
	 * Login Email auth
	 *
	 * @param object $user  user.
	 * @param string $username  username.
	 * @param string $password  password.
	 * @since 1.00
	 */
	public function email_login( $user, $username, $password ) {

		$user = get_user_by( 'email', $username );

		if ( ! empty( $user->user_login ) ) {
			$username = $user->user_login;
		}

		return wp_authenticate_username_password( null, $username, $password );
	}

	/** ==================================================
	 * Register email name
	 *
	 * @param string $from_name  from_name.
	 * @since 1.00
	 */
	public function mail_from_sitename( $from_name ) {
		return get_option( 'blogname' );
	}

	/** ==================================================
	 * Register email address
	 *
	 * @param string $from_email  from_email.
	 * @since 1.00
	 */
	public function mail_from_admin_email( $from_email ) {
		return get_option( 'admin_email' );
	}

	/** ==================================================
	 * Notice mail when newly registering users
	 *
	 * @param array  $wp_mail  wp_mail.
	 * @param object $user  user.
	 * @param string $blogname  blogname.
	 * @since 1.00
	 */
	public function regist_user_notify_mail( $wp_mail, $user, $blogname ) {

		$unm = $user->user_login;
		$key = get_password_reset_key( $user );

		/* translators: %s: blogname */
		$title    = sprintf( __( '[%s] login Username', 'user-mail-only-register' ), $blogname );
		$message  = __( 'Thank you for registering. The login information is as follows.', 'user-mail-only-register' ) . "\r\n\r\n";
		$message .= __( 'Login Address (URL)' ) . ':' . wp_login_url() . "\r\n\r\n";
		$message .= sprintf( __( 'Username' ) . '&' . __( 'Email' ) . ': %s', $unm ) . "\n";
		$message .= __( 'To reset your password, visit the following address:' ) . ': <' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $unm ), 'login' ) . ">\r\n\r\n";

		$wp_mail['subject'] = $title;
		$umor_settings      = get_option( 'usermailonlyregister' );
		$wp_mail['message'] = $umor_settings['before'] . "\r\n\r\n" . $message . $umor_settings['after'];

		return $wp_mail;
	}
}


