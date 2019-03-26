<?php
/**
 * Admin View: Template Settings
 *
 * @package Sandbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="sandbox-settings-wrapper">

	<input type="hidden" name="sandbox_template_settings" value="1"/>

	<?php wp_nonce_field( 'sandbox-settings' ); ?>

	<?php if ($this->data['is_wizard'] && ! Sandbox_API::getInstance()->is_poopy_site()): ?>

		<h2><?php _e('Sandbox Template', 'sandbox'); ?></h2>
		<p><?php _e("A template is a snapshot of your Sandbox install. After you create a template you'll be given a duplication URL. This URL will create a new install using the template you saved. Only people you share the URL with will be able to create an install with your template.", 'sandbox'); ?></p>
		<p><?php _e('The password for the demo user will be changed but all other user accounts will remain untouched.', 'sandbox'); ?></p>

		<p class="submit">
			<input type="hidden" name="create_sandbox_template" value="1"/>
			<input name="save" class="button-primary sandbox-save-button" type="submit" value="<?php esc_attr_e( 'Create Sandbox Template', 'sandbox' ); ?>" />
		</p>

	<?php else: ?>
		
		<h2><?php _e('Sandbox Template', 'sandbox'); ?></h2>
		<p><?php _e('Create a copy of this install with this URL:');?></p>

		<a id="template_url" href="<?php echo Sandbox_API::getInstance()->is_poopy_site() ? '' : $this->getTemplateUrl();?>" data-url="<?php echo $this->getTemplateUrl(false);?>" target="_blank"><?php echo $this->getTemplateUrl();?></a>
		
		<div class="input">
			<label for="is_add_redirect" class="add_redirect_wrapper">
				<input type="hidden" name="is_add_redirect" value="0"/>
				<input type="checkbox" id="is_add_redirect" name="is_add_redirect" value="1" class="switcher" <?php if (!empty($this->getRedirectUrl())) echo 'checked="checked"';?>/>
				<?php _e('Add a redirect URL'); ?>
			</label>
			<a href="#help" class="sandbox-help" title="<?php _e('Users will be redirected to this URL after their install is created and they are logged in. For best results, use a relative URL, like this: /wp-admin/admin.php?page=sandbox', 'sandbox'); ?>">?</a>
			<span class="switcher-target-is_add_redirect">
				<div class="input">
					<textarea type="text" name="sandbox_redirect_url" cols="50" rows="3"><?php echo $this->getRedirectUrl(); ?></textarea>
				</div>
			</span>
		</div>

		<div class="input">
			<input type="button" class="button-primary add-template-to-extension" rel="<?php echo Sandbox_API::getInstance()->sandbox_domain;?>" value="<?php _e('Add Template to Browser Extension', 'sandbox'); ?>"/>
		</div>

		<p><?php _e('New installs will be created using the latest Sandbox template.', 'sandbox'); ?></p>
		<?php
			$template_created = empty($this->data['settings']['template_date']) ? time() : $this->data['settings']['template_date'];
		?>
		<p><?php echo sprintf(__('This Sandbox template was last saved at <strong>%s</strong>.', 'sandbox'), date('g:iA', $template_created) . ' on ' . date('l, F jS, Y', $template_created)); ?></p>

		<p class="submit">
			<input type="hidden" name="update_sandbox_template" value="1"/>
			<input name="save" class="button-primary sandbox-save-button save-template-settings" type="submit" value="<?php esc_attr_e( 'Save Sandbox Template', 'sandbox' ); ?>" />
			<?php if (!Sandbox_API::getInstance()->is_poopy_site()): ?>
			<input type="submit" class="button" name="delete_template" value="<?php _e('Delete Sandbox Template', 'sandbox') ?>" />
			<?php endif; ?>
		</p>

	<?php endif; ?>

</div>

<?php if (!$this->data['is_wizard'] || Sandbox_API::getInstance()->is_poopy_site()): ?>

    <?php if (!empty(Sandbox::getInstance()->server_data['license']) && in_array(Sandbox::getInstance()->server_data['license']['plan_name'], array('10-installs', 'developer'))):?>
        <div class="sandbox-ad sandbox-permissions-permission">
            <a href="https://wpsandbox.io/pricing" class="try_now" target="_blank">
                <span><?php _e('Upgrade Now', 'sandbox'); ?></span>
                <span class="call_to_action_details"><?php _e('Starts at $9/mo'); ?></span>
            </a>
            <p><?php _e('Your plan does not include child install permissions or custom welcome notices.'); ?></p>
        </div>
    <?php endif; ?>


    <div class="sandbox-settings-wrapper sandbox-child-permissions">

        <h3><?php _e('Permissions for Child Installs', 'sandbox'); ?></h3>

        <p><?php echo sprintf(__('Choose which Sandbox options will be available in the admin panel for installs created from %s', 'sandbox'), site_url()); ?></p>

        <div class="input">
            <div class="input">
                <label for="sandbox_settings_tab">
                    <input id="sandbox_settings_tab" class="switcher" type="checkbox" name="sandbox_permissions[settings_page]" value="1" <?php if ($this->getPermission('settings_page')): ?>checked="checked"<?php endif;?>/>
                    <?php _e('Enable the WP Sandbox settings page', 'sandbox'); ?>
                </label>
            </div>

            <div class="switcher-target-sandbox_settings_tab">
                <?php if ($this->getParentPermission('expiration_tab')): ?>
                <div class="input">
                    <label for="sandbox_expiration_tab">
                        <input id="sandbox_expiration_tab" type="checkbox" name="sandbox_permissions[expiration_tab]" value="1" <?php if ($this->getPermission('expiration_tab')): ?>checked="checked"<?php endif;?>/>
                        <?php _e('Enable the Expiration tab', 'sandbox'); ?>
                    </label>
                </div>
                <?php endif; ?>
                <?php if ($this->getParentPermission('template_tab')): ?>
                <div class="input">
                    <label for="sandbox_template_tab">
                        <input id="sandbox_template_tab" type="checkbox" name="sandbox_permissions[template_tab]" value="1" <?php if ($this->getPermission('template_tab')): ?>checked="checked"<?php endif;?>/>
                        <?php _e('Enable the Template tab', 'sandbox'); ?>
                    </label>
                </div>
                <?php endif; ?>
                <?php if ($this->getParentPermission('advanced_tab')): ?>
                <div class="input">
                    <label for="sandbox_advanced_tab">
                        <input id="sandbox_advanced_tab" type="checkbox" name="sandbox_permissions[advanced_tab]" value="1" <?php if ($this->getPermission('advanced_tab')): ?>checked="checked"<?php endif;?>/>
                        <?php _e('Enable Advanced Options tab', 'sandbox'); ?>
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="submit">
            <input name="save_permissions" class="button-primary sandbox-save-button save-template-permissions" type="submit" value="<?php esc_attr_e( 'Save Sandbox Permissions', 'sandbox' ); ?>" />
        </p>
    </div>

<?php endif; ?>
