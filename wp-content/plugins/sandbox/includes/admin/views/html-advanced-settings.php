<?php
/**
 * Admin View: Advance Settings
 *
 * @package Sandbox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="sandbox-settings-wrapper">

    <h2><?php _e('WordPress'); ?></h2>

    <table>
        <tr>
            <td class="table-label"><?php _e('One Click Login:'); ?></td>
            <td class="table-value"><?php echo $this->getOneClickUrl(); ?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Username:');?></td>
            <td class="table-value"><?php echo $this->data['options']['username'];?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Password:');?></td>
            <td class="table-value"><?php echo $this->data['options']['password'];?></td>
        </tr>
    </table>

</div>

<div class="sandbox-settings-wrapper">

    <h2><?php _e('SSH Credentials'); ?></h2>

    <table>
        <tr>
            <td class="table-label"><?php _e('Hostname:'); ?></td>
            <td class="table-value"><?php echo $this->getSetting('server_web_host');?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Username:');?></td>
            <td class="table-value"><?php echo $this->getSetting('server_name');?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Password:');?></td>
            <td class="table-value"><?php echo $this->getSetting('unix_pass');?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Login:');?></td>
            <td class="table-value"><?php echo 'ssh -p ' . $this->getSetting('port') . ' ' . $this->getSetting('server_name') . '@' . $this->getSetting('server_web_host');?></td>
        </tr>
    </table>

</div>

<div class="sandbox-settings-wrapper">

    <h2><?php _e('SFTP Credentials'); ?></h2>

    <table>
        <tr>
            <td class="table-label"><?php _e('Server:'); ?></td>
            <td class="table-value"><?php echo $this->getSetting('server_web_host');?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Username:');?></td>
            <td class="table-value"><?php echo $this->getSetting('server_name');?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Password:');?></td>
            <td class="table-value"><?php echo $this->getSetting('unix_pass');?></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Port:');?></td>
            <td class="table-value"><?php echo $this->getSetting('port');?></td>
        </tr>
    </table>

</div>

<div class="sandbox-settings-wrapper">

    <h2><?php _e('PHP Version'); ?></h2>

    <table>
        <tr>
            <td class="table-label"><?php _e('Current version:'); ?></td>
            <td><span class="sandbox-php-version"><strong><?php echo $this->getSetting('php_version');?></strong></span></td>
        </tr>
        <tr>
            <td class="table-label"><?php _e('Change To:');?></td>
            <td>
                <select name="php_version">
                    <?php foreach (Sandbox_API::$php_version as $version):?>
                    <?php if ($version === end(Sandbox_API::$php_version)) { ?>
					<option selected="selected" value="<?php echo $version;?>"><?php echo $version;?></option>
					<?php } else { ?>
					<option value="<?php echo $version;?>"><?php echo $version;?></option>
                    <?php } endforeach;?>
                </select>
            </td>
        </tr>
    </table>

    <input type="hidden" name="sandbox_advanced_settings" value="1"/>

    <p class="submit">
        <input name="save" class="button-primary sandbox-save-button save-advanced-settings" type="submit" value="<?php esc_attr_e( 'Save Changes', 'sandbox' ); ?>" />
        <?php wp_nonce_field( 'sandbox-settings' ); ?>
    </p>

</div>
