<?php
/**
 * Admin View: Expiration Settings
 *
 * @package Sandbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if (Sandbox_API::getInstance()->is_poopy_site()): ?>
<div class="sandbox-settings-wrapper">
    <?php if (empty($this->sandbox_migration)):?>
        <p><strong><?php _e('Already have subscription?', 'sandbox'); ?></strong></p>
        <p><?php _e('Migrate this install to your WP Sandbox account and prevent it from being deleted.', 'sandbox'); ?></p>
        <div class="input">
            <input id="sandbox_license" type="text" name="sandbox_license" value="" placeholder="<?php _e('WP Sandbox License', 'sandbox'); ?>"/>
            <input name="migrate" class="button-primary sandbox-save-button migrate-sandbox" type="submit" value="<?php esc_attr_e( 'Migrate', 'sandbox' ); ?>" />
        </div>
    <?php else: ?>
        <?php $pro_url = $this->sandbox_migration['url'] . '?upass=' . $this->sandbox_migration['wppass']; ?>
        <p><strong><?php _e('Migration Complete', 'sandbox'); ?></strong></p>
        <p><?php _e(' Log in to your Sandbox install:', 'sandbox'); ?> <a href="<?php echo $pro_url;?>" target="_blank"><?php echo $pro_url;?></a></p>
    <?php endif;?>
</div>
<?php endif;?>

<div class="sandbox-settings-wrapper">

	<?php if ( empty($this->data['expiration']) ): ?>
		<p><?php echo __('This Sandbox is not currently scheduled for deletion.', 'sandbox');?></p>
	<?php else: ?>
		<?php if (current_time( 'timestamp' ) < strtotime($this->data['expiration'])):?>
			<p><?php echo sprintf(__('This Sandbox will expire and be deleted in: <span class="sandbox-time-to-expire"><strong>%s</strong></span>', 'sandbox'), Sandbox_API::get_date_diff( current_time( 'timestamp' ), strtotime($this->data['expiration'])));?></p>
		<?php else: ?>
			<p><?php echo __('This Sandbox expire and be deleted soon.', 'sandbox');?></p>
		<?php endif;?>
	<?php endif;?>

	<div class="input" style="display: inline-block; position: relative; margin-bottom: 5px;">
		<label for="sandbox_expiration_date_yes">
			<input id="sandbox_expiration_date_yes" class="switcher" type="radio" name="is_set_sandbox_expiration_date" value="yes" <?php if (!empty($this->data['expiration'])): ?>checked="checked"<?php endif; ?>/>
			<?php _e('Delete this install 7 days after the last admin login', 'sandbox');?></label>
		<a href="#help" class="sandbox-help" title="<?php _e('Every time a logged in administrator visits the site, the deletion date will be set to 7 days in the future.', 'sandbox'); ?>">?</a>

		<!--span class="switcher-target-sandbox_expiration_date_yes">
			<div class="input" style="position: absolute; top: -2px; left: 150px;">
				<input type="text" name="sandbox_expiration_date" id="sandbox-datepicker" value="<?php if (!empty($this->data['expiration'])) echo date("m/d/Y", strtotime($this->data['expiration'])); ?>" />
			</div>
		</span-->
	</div>
	<div class="input">
		<label for="sandbox_expiration_date_no">
			<input id="sandbox_expiration_date_no" class="switcher" type="radio" name="is_set_sandbox_expiration_date" value="no" <?php if (empty($this->data['expiration'])): ?>checked="checked"<?php endif; ?>/>
			<?php _e('Do not expire this install', 'sandbox');?></label>
	</div>
	<input type="hidden" name="sandbox_expiration_settings" value="1"/>

	<p class="submit">
		<input name="save" class="button-primary sandbox-save-button save-expiration-settings" type="submit" value="<?php esc_attr_e( 'Save Changes', 'sandbox' ); ?>" />
		<?php wp_nonce_field( 'sandbox-settings' ); ?>
	</p>

</div>
