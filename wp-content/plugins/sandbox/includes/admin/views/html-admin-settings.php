
<?php
/**
 * Admin View: Settings
 *
 * @package Sandbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ $current_tab ] ) || has_action( 'sandbox_sections_' . $current_tab ) || has_action( 'sandbox_settings_' . $current_tab ) || has_action( 'sandbox_settings_tabs_' . $current_tab );
$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : '';

if ( ! $tab_exists ) {
	wp_safe_redirect( admin_url( 'admin.php?page=sandbox' ) );
	exit;
}
?>
<div class="wrap sandbox-plugin">
	<?php self::show_messages(); ?>
	<h1><a href="https://wpsandbox.io" target="_blank" class="sandbox-logo"></a></h1>
	<form method="<?php echo esc_attr( apply_filters( 'sandbox_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" class="sandbox-settings-form" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php

			foreach ( $tabs as $slug => $label ) {
				echo '<a href="' . esc_html( admin_url( 'admin.php?page=sandbox&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
			}

			do_action( 'sandbox_settings_tabs' );

			?>
		</nav>
		<?php if (Sandbox_API::getInstance()->is_poopy_site() || !empty(Sandbox::getInstance()->server_data['license']) && Sandbox::getInstance()->server_data['license']['plan_name'] == Sandbox::FREE_TRIAL_PLAN):?>
		<div class="sandbox-ad">
            <?php if (!empty(Sandbox::getInstance()->server_data['license']) && Sandbox::getInstance()->server_data['license']['plan_name'] == Sandbox::FREE_TRIAL_PLAN):?>
                <a href="https://wpsandbox.io/checkout?edd_action=add_to_cart&download_id=686" class="try_now" target="_blank">
                    <span><?php _e('Sign up for Sandbox Now', 'sandbox'); ?></span>
                    <span class="call_to_action_details"><?php _e('Starts at $9/mo'); ?></span>
                </a>
            <?php else: ?>
    			<a href="https://wpsandbox.io/checkout?edd_action=add_to_cart&download_id=686" class="try_now" target="_blank"><span><?php _e('Try WP Sandbox for $1', 'sandbox'); ?></span></a>
            <?php endif; ?>
			<p><?php do_action( 'sandbox_sections_upgrade_notice_' . $current_tab ); ?></p>
		</div>
		<?php endif; ?>

		<?php
		do_action( 'sandbox_sections_' . $current_tab );
		do_action( 'sandbox_settings_' . $current_tab );
		?>

	</form>
</div>
