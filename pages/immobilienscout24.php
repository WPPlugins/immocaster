<?php

/**
 * Immocaster Wordpress Plugin
 * Settings to use immobilienscout24.
 */

/**
 * Certify application or delete a
 * certification (only in pro version).
 *
 * @return void
 */
function immocaster_certify_immobilienscout_app()
{
	if(isset($_GET['main_registration'])||isset($_GET['state']))
	{
		$sUrl = get_admin_url().'admin.php?page=immocaster-immobilienscout24&immocaster_register_app=1';
		$oImmocaster = ImmocasterSDK::getInstance('is24');
		$aParameter = array('callback_url'=>$sUrl,'verifyApplication'=>true);
		if($oImmocaster->_oImmocaster->getAccess($aParameter)){
			return true;
		}else{
			return false;
		}
	}
	if(isset($_GET['main_registration_delete']))
	{
		$oImmocaster = ImmocasterSDK::getInstance('is24');
		$oImmocaster->_oImmocaster->oDataStorage->deleteApplicationToken();
	}
}
add_action('init','immocaster_certify_immobilienscout_app');

/**
 * Update settings for immobilienscout.
 *
 * @return void
 */
function immocaster_pages_immobilienscout24_update()
{
	// Check POST
	if(!$_POST){ return false; }
	if(!isset($_POST['action'])){ return false ; }
	// Update immobilienscout options
	if($_POST['action']=='update_immobilienscout_options')
	{
		update_option('is24_affiliate_referrer',$_POST['is24_affiliate_referrer']);
		update_option('is24_show_contactbox',$_POST['is24_show_contactbox']);
		update_option('is24_rest_key',$_POST['is24_rest_key']);
		update_option('is24_rest_secret',$_POST['is24_rest_secret']);
		update_option('is24_account_username',$_POST['is24_account_username']);
		update_option('immocaster_force_is24_expose',$_POST['immocaster_force_is24_expose']);
		$oMsg = ImmocasterMSG::getInstance();
		$oImmocasterSDK = ImmocasterSDK::getInstance('is24check');
		if(substr_count((string)$oImmocasterSDK->getRegions('ber'),'ERROR_')>=1)
		{
			$oMsg->addMessage(
				__('Immocaster settings updated successful, but connection to ImmobilienScout24 failed.',
				IMMOCASTER_PO_TEXTDOMAIN),
				true
			);
		}
		else
		{
			$oMsg->addMessage(
				__('Immocaster settings updated successful and wordpress is connected to ImmobilienScout24.',
				IMMOCASTER_PO_TEXTDOMAIN)
			);
		}
	}
}
add_action('init','immocaster_pages_immobilienscout24_update');

/**
 * Form to connect immobilienscout.
 *
 * @return void
 */
function immocaster_pages_immobilienscout24()
{
	global $wpdb;
	// Verify application
	if(isset($_GET['main_registration']))
	{
		ImmocasterSDK::getInstance('is24')->certifyApplication();
	}
	// Form
?>
	<div class="wrap">
    	<form method="post" action="#" name="is24settings">
		<h2><?php echo __('ImmobilienScout24 Settings',IMMOCASTER_PO_TEXTDOMAIN); ?></h2>
		<p><?php echo __('If you want to use "Immocaster" with ImmobilienScout24 API you need a Key and a Secret by ImmobilienScout24. For this data you have to register <a href="http://rest.immobilienscout24.de/restapi/security/registration">here</a>.',IMMOCASTER_PO_TEXTDOMAIN); ?>&nbsp;<strong><?php echo __('Please activate JavaScript in your browser, if it is deactivated.',IMMOCASTER_PO_TEXTDOMAIN); ?></strong></p>
			<table class="form-table">
            
            	<!-- Key -->
            
				<tr valign="top">
					<th scope="row">
						<label for="is24_rest_key"><?php echo __('ImmobilienScout24-Key',IMMOCASTER_PO_TEXTDOMAIN); ?>:</label>
					</th>
					<td>
						<input name="is24_rest_key" value="<?php echo get_option('is24_rest_key'); ?>" type="text" maxlength="64" />
					</td>
				</tr>
                
                <!-- Secret -->
                
				<tr valign="top">
					<th scope="row">
						<label for="is24_rest_secret"><?php echo __('ImmobilienScout24-Secret',IMMOCASTER_PO_TEXTDOMAIN); ?>:</label>
					</th>
					<td>
						<input name="is24_rest_secret" value="<?php echo get_option('is24_rest_secret'); ?>" type="text" maxlength="64" />
					</td>
				</tr>
                
                <!-- Check connection -->
                
                <tr valign="top">
					<th scope="row">&nbsp;</th>
					<td>
						<?php
						$oImmocasterSDK = ImmocasterSDK::getInstance('is24check');
						if($oImmocasterSDK->checkConnection())
						{
							echo '<div class="immocaster_highlight_success">'.
							__('Connected to ImmobilienScout24',IMMOCASTER_PO_TEXTDOMAIN).
							'</div>';
							if($oImmocasterSDK->getExposeSupport()==false)
							{
								echo '<div class="immocaster_highlight_problem">'.
								__('Expose not supported by ImmobilienScout24',IMMOCASTER_PO_TEXTDOMAIN).
								'</div>';
							}
						}
						else
						{
							echo '<div class="immocaster_highlight_problem">'.__('Not connected to ImmobilienScout24',IMMOCASTER_PO_TEXTDOMAIN).'</div>';
						}
						?>
					</td>
				</tr>
                
                <!-- Username -->
                
                <tr valign="top">
                    <th scope="row">
                        <label for="is24_account_username"><?php echo __('ImmobilienScout24-User',IMMOCASTER_PO_TEXTDOMAIN); ?>:</label>
                    </th>
                    <td>
                        <input name="is24_account_username" value="<?php echo get_option('is24_account_username'); ?>" type="text" maxlength="32" />
                    </td>
            	</tr>
                
                <!-- Username verify -->
                
                <?php
				if(get_option('is24_rest_secret') && get_option('is24_rest_key'))
				{
					if($wpdb->query("SELECT * FROM Immocaster_Storage WHERE ic_desc='APPLICATION'")==0)
					{
						if(get_option('is24_account_username')!='')
						{
							echo '<tr valign="top"><th scope="row">&nbsp;</th><td>';
							echo sprintf(__('No token! <a href="%s" target="_self">Certify your website</a> with <strong>"%s"</strong> as Username.',IMMOCASTER_PO_TEXTDOMAIN),get_admin_url().'admin.php?page=immocaster-immobilienscout24&main_registration=1',get_option('is24_account_username'));
							echo '</td></tr>';
						}
					}else{
						echo '<tr valign="top"><th scope="row">&nbsp;</th><td>';
						echo sprintf(__('Token registered! Delete Certify-Token <a href="%s" target="_self">here</a>.',IMMOCASTER_PO_TEXTDOMAIN),get_admin_url().'admin.php?page=immocaster-immobilienscout24&main_registration_delete=1');
						echo '</td></tr>';
						if($oImmocasterSDK->getExposeSupport()==false)
						{
							echo '<tr valign="top"><th scope="row">&nbsp;</th><td>';
							echo __('<div class="immocaster_highlight_problem">Expose-Support is disabled by ImmobilienScout24. If you work only with your own real estate objects you can contact ImmobilienScout24 to get another key to get also expose data.</div>',IMMOCASTER_PO_TEXTDOMAIN);
							echo '</td></tr>';
						}
					}
				}
				?>
                
				<!-- Contactbox -->
                
                <tr valign="top">
					<th scope="row">
						<label for="is24_show_contactbox"><?php echo __('Show contactbox on pages with objects',IMMOCASTER_PO_TEXTDOMAIN); ?>:</label>
					</th>
					<td>
						<input name="is24_show_contactbox" value="no" type="radio" 
                        <?php 
						
							if(get_option('is24_show_contactbox','no')!='yes')
							{
								echo ' checked';
							}
						?>
                        />&nbsp;<?php echo __('Dont show box',IMMOCASTER_PO_TEXTDOMAIN); ?>
                        <input name="is24_show_contactbox" value="yes" type="radio" 
                        <?php 
							if(get_option('is24_show_contactbox','no')=='yes')
							{
								echo ' checked';
							}
						?>
                        />&nbsp;<?php echo __('Show box',IMMOCASTER_PO_TEXTDOMAIN); ?>
					</td>
				</tr>
                
                <!-- Force exposes -->
                
                <tr valign="top">
					<th scope="row">
						<label for="immocaster_force_is24_expose"><?php echo __('Show full data',IMMOCASTER_PO_TEXTDOMAIN); ?>:</label>
					</th>
					<td>
                    	<em><?php echo __('Immocaster does an automatic check if you are activated by ImmobilienScout24 to use the whole object data. Sometimes this check fail. So i you are shure you have these rights, please enable this. If you enable this and dont have the rights, it could be that this plugin crashes your wordpress.',IMMOCASTER_PO_TEXTDOMAIN); ?></em><br />
                        <input name="immocaster_force_is24_expose" value="no" type="radio" 
                        <?php 
							if(get_option('immocaster_force_is24_expose','no')!='yes')
							{
								echo ' checked';
							}
						?>
                        />&nbsp;<?php echo __("Use immocasters autocheck",IMMOCASTER_PO_TEXTDOMAIN); ?>&nbsp;
                        <input name="immocaster_force_is24_expose" value="yes" type="radio" 
                        <?php 
							if(get_option('immocaster_force_is24_expose','no')=='yes')
							{
								echo ' checked';
							}
						?>
                        />&nbsp;<?php echo __("Yes. I'm shure I have the rights to show exposes (objects).",IMMOCASTER_PO_TEXTDOMAIN); ?>
					</td>
				</tr>
                
                <!-- Referrer -->
                
                <tr valign="top">
                    <th scope="row">
                        <label for="is24_affiliate_referrer"><?php echo __('Affiliate Referrer',IMMOCASTER_PO_TEXTDOMAIN); ?>:</label>
                    </th>
                    <td>
                        <input name="is24_affiliate_referrer" value="<?php echo get_option('is24_affiliate_referrer'); ?>" type="text" maxlength="64" />&nbsp;<?php echo __('Only the referrer without full domain.',IMMOCASTER_PO_TEXTDOMAIN); ?>
                    </td>
            	</tr>
                
			</table>
        <input name="action" value="update_immobilienscout_options" type="hidden" />
		<p class="submit"><input type="submit" class="button-primary" name="Submit" value="<?php echo __('Save Settings',IMMOCASTER_PO_TEXTDOMAIN); ?>" /></p>
        </form>
	</div>  
<?php
}