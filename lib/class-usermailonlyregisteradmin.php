<?php
/**
 * User Mail Only Register
 *
 * @package    User Mail Only Register
 * @subpackage UserMailOnlyRegisterAdmin Management screen
	Copyright (c) 2019- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

$usermailonlyregisteradmin = new UserMailOnlyRegisterAdmin();

/** ==================================================
 * Management screen
 */
class UserMailOnlyRegisterAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'user-mail-only-register/usermailonlyregister.php';
		}
		if ( $file === $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=usermailonlyregister' ) . '">' . __( 'Settings' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {
		add_options_page( 'User Mail Only Register Options', 'User Mail Only Register', 'manage_options', 'usermailonlyregister', array( $this, 'plugin_options' ) );
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname                    = admin_url( 'options-general.php?page=usermailonlyregister' );
		$usermailonlyregister_settings = get_option( 'usermailonlyregister' );

		?>
		<div class="wrap">
		<h2>User Mail Only Register</h2>

			<details>
				<summary><strong><?php esc_html_e( 'Various links of this plugin', 'user-mail-only-register' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<div class="wrap">
				<form method="post" action="<?php echo esc_url( $scriptname ); ?>">
				<?php wp_nonce_field( 'umor_set', 'usermailonlyregister_set' ); ?>

				<details style="margin-bottom: 5px;" open>
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php echo 'WordPress ' . esc_html__( 'Settings' ); ?></strong></summary>
					<div style="margin: 5px; padding: 5px;">
					<div style="display: block;padding:5px 5px">
					<?php esc_html_e( 'Membership' ); ?> : 
					<input name="users_can_register" type="checkbox" value="1" <?php checked( '1', get_option( 'users_can_register' ) ); ?> />
					<?php esc_html_e( 'Anyone can register' ); ?>
					</div>
					<div style="display: block;padding:5px 5px">
					<?php esc_html_e( 'New User Default Role' ); ?> : 
					<select name="default_role">
					<?php wp_dropdown_roles( get_option( 'default_role' ) ); ?>
					</select>
					</div>
					</div>
					</details>

					<details style="margin-bottom: 5px;" open>
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Login screen', 'user-mail-only-register' ); ?></strong></summary>
					<div style="margin: 5px; padding: 5px;">
					<div style="display: block;padding:5px 5px"><?php esc_html_e( 'Login message', 'user-mail-only-register' ); ?> : <input type="text" name="login_message" style="width: 500px;" value="<?php echo esc_attr( $usermailonlyregister_settings['login_message'] ); ?>"></div>
					<div style="display: block;padding:5px 5px"><?php esc_html_e( 'login logo URL', 'user-mail-only-register' ); ?> : <input type="text" name="login_logo_url" style="width: 500px;" value="<?php echo esc_attr( $usermailonlyregister_settings['login_logo_url'] ); ?>"></div>
					<div style="display: block;padding:5px 5px"><?php esc_html_e( 'login logo link URL', 'user-mail-only-register' ); ?> : <input type="text" name="login_logo_link_url" style="width: 500px;" value="<?php echo esc_attr( $usermailonlyregister_settings['login_logo_link_url'] ); ?>"></div>
					<div style="display: block;padding:5px 5px"><?php esc_html_e( 'Term of use URL', 'user-mail-only-register' ); ?> : <input type="text" name="termofuse" style="width: 500px;" value="<?php echo esc_attr( $usermailonlyregister_settings['termofuse'] ); ?>"></div>
					<div style="display: block;padding:5px 5px">
					<input type="checkbox" name="none_nav_link" value="1" <?php checked( $usermailonlyregister_settings['none_nav_link'], true ); ?>>
					<?php esc_html_e( 'Hide the link to "Log in" and "Lost your password"', 'user-mail-only-register' ); ?>
					</div>
					</div>
				</details>

				<details style="margin-bottom: 5px;" open>
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'E-mail notification', 'user-mail-only-register' ); ?></strong></summary>
					<div style="margin: 5px; padding: 5px;">
					<div style="display: block;padding:5px 5px">
					<div><?php esc_html_e( 'Before login information (Text Only)', 'user-mail-only-register' ); ?></div>
					<textarea name="umor_before" style="resize: auto; max-width: 500px; max-height: 500px; min-width: 100px; min-height: 100px; width:500px; height:100px"><?php echo esc_textarea( $usermailonlyregister_settings['before'] ); ?></textarea>
					</div>
					<div style="display: block;padding:5px 5px">
					<div><?php esc_html_e( 'After login information (Text Only)', 'user-mail-only-register' ); ?></div>
					<textarea name="umor_after" style="resize: auto; max-width: 500px; max-height: 500px; min-width: 100px; min-height: 100px; width:500px; height:100px"><?php echo esc_textarea( $usermailonlyregister_settings['after'] ); ?></textarea>
					</div>
					<?php
					$filter_link = '<a style="text-decoration: none;" href="' . __( 'https://developer.wordpress.org/reference/hooks/wp_new_user_notification_email/', 'user-mail-only-register' ) . '" target="_blank" rel="noopener noreferrer">wp_new_user_notification_email</a>';
					?>
					<div style="display: block;padding:5px 5px">
					<div><input type="checkbox" name="notify_mail" value="1" <?php checked( '1', $usermailonlyregister_settings['notify_mail_use'] ); ?>>
					<?php esc_html_e( 'Use this plugin email notification', 'user-mail-only-register' ); ?>
					</div>
						<div style="display: block;padding:5px 15px">
						<?php /* translators: %1$s filter link */ ?>
						<?php echo wp_kses_post( sprintf( __( 'This plugin will notify the registrant of the mail using the filter hook %1$s. Please uncheck this if you want to your own notify mail using filter hook %1$s.', 'user-mail-only-register' ), $filter_link ) ); ?>
						</div>
					</div>
					</div>
				</details>

				<?php submit_button( __( 'Save Changes' ), 'large', 'Manageset', true ); ?>
				</form>

				<details style="margin-bottom: 5px;">
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php esc_html_e( 'Shortcode', 'user-mail-only-register' ); ?></strong></summary>
					<div style="margin: 5px; padding: 5px;">
						<div style="padding: 5px 35px;"><code>[umorregister]</code></div>
					</div>
				</details>

				<details style="margin-bottom: 5px;">
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><strong><?php echo esc_html_e( 'Filters', 'user-mail-only-register' ); ?></strong></summary>
					<div style="padding: 5px;">
					<?php
					$umor_url = __( 'https://wordpress.org/plugins/user-mail-only-register/', 'user-mail-only-register' );
					?>
					<a href="<?php echo esc_url( $umor_url ); ?>" target="_blank" rel="noopener noreferrer" class="page-title-action"><?php esc_html_e( 'How to use filters', 'user-mail-only-register' ); ?></a>
					</div>
				</details>

			</div>

		</div>
		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'user-mail-only-register' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'user-mail-only-register' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'user-mail-only-register' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['Manageset'] ) && ! empty( $_POST['Manageset'] ) ) {
			if ( check_admin_referer( 'umor_set', 'usermailonlyregister_set' ) ) {
				if ( isset( $_POST['users_can_register'] ) && ! empty( $_POST['users_can_register'] ) ) {
					update_option( 'users_can_register', true );
				} else {
					update_option( 'users_can_register', false );
				}
				if ( isset( $_POST['default_role'] ) && ! empty( $_POST['default_role'] ) ) {
					update_option( 'default_role', sanitize_text_field( wp_unslash( $_POST['default_role'] ) ) );
				}
				$usermailonlyregister_settings = get_option( 'usermailonlyregister' );
				if ( isset( $_POST['login_message'] ) && ! empty( $_POST['login_message'] ) ) {
					$usermailonlyregister_settings['login_message'] = sanitize_text_field( wp_unslash( $_POST['login_message'] ) );
				}
				if ( isset( $_POST['login_logo_url'] ) && ! empty( $_POST['login_logo_url'] ) ) {
					$usermailonlyregister_settings['login_logo_url'] = esc_url_raw( wp_unslash( $_POST['login_logo_url'] ) );
				} else {
					$usermailonlyregister_settings['login_logo_url'] = null;
				}
				if ( isset( $_POST['login_logo_link_url'] ) && ! empty( $_POST['login_logo_link_url'] ) ) {
					$usermailonlyregister_settings['login_logo_link_url'] = esc_url_raw( wp_unslash( $_POST['login_logo_link_url'] ) );
				}
				if ( isset( $_POST['termofuse'] ) && ! empty( $_POST['termofuse'] ) ) {
					$usermailonlyregister_settings['termofuse'] = esc_url_raw( wp_unslash( $_POST['termofuse'] ) );
				}
				if ( isset( $_POST['none_nav_link'] ) && ! empty( $_POST['none_nav_link'] ) ) {
					$usermailonlyregister_settings['none_nav_link'] = true;
				} else {
					$usermailonlyregister_settings['none_nav_link'] = false;
				}
				if ( isset( $_POST['umor_before'] ) && ! empty( $_POST['umor_before'] ) ) {
					$usermailonlyregister_settings['before'] = sanitize_text_field( wp_unslash( $_POST['umor_before'] ) );
				} else {
					$usermailonlyregister_settings['before'] = null;
				}
				if ( isset( $_POST['umor_after'] ) && ! empty( $_POST['umor_after'] ) ) {
					$usermailonlyregister_settings['after'] = sanitize_text_field( wp_unslash( $_POST['umor_after'] ) );
				} else {
					$usermailonlyregister_settings['after'] = null;
				}
				if ( isset( $_POST['notify_mail'] ) && ! empty( $_POST['notify_mail'] ) ) {
					$usermailonlyregister_settings['notify_mail_use'] = intval( $_POST['notify_mail'] );
				} else {
					$usermailonlyregister_settings['notify_mail_use'] = false;
				}
				update_option( 'usermailonlyregister', $usermailonlyregister_settings );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html__( 'Settings' ) . ' --> ' . esc_html__( 'Settings saved.' ) . '</li></ul></div>';
			}
		}
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		if ( get_option( 'usermailonlyregister' ) ) {
			$umor_settings = get_option( 'usermailonlyregister' );
			/* ver 1.01 later */
			if ( ! array_key_exists( 'before', $umor_settings ) ) {
				$umor_settings['before'] = null;
				update_option( 'usermailonlyregister', $umor_settings );
			}
			if ( ! array_key_exists( 'after', $umor_settings ) ) {
				$umor_settings['after'] = null;
				update_option( 'usermailonlyregister', $umor_settings );
			}
			if ( ! array_key_exists( 'notify_mail_use', $umor_settings ) ) {
				$umor_settings['notify_mail_use'] = 1;
				update_option( 'usermailonlyregister', $umor_settings );
			}
			/* ver 1.09 later */
			if ( ! array_key_exists( 'none_nav_link', $umor_settings ) ) {
				$umor_settings['none_nav_link'] = false;
				update_option( 'usermailonlyregister', $umor_settings );
			}
		} else {
			$umor_tbl = array(
				'login_message'       => get_option( 'blogname' ),
				'login_logo_url'      => null,
				'login_logo_link_url' => esc_url_raw( home_url() ),
				'termofuse'           => esc_url_raw( get_permalink( get_option( 'wp_page_for_privacy_policy' ) ) ),
				'before'              => null,
				'after'               => null,
				'notify_mail_use'     => 1,
				'none_nav_link'       => false,
			);
			update_option( 'usermailonlyregister', $umor_tbl );
		}
	}
}


