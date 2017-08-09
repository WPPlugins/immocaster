<?php

/**
 * Immocaster Wordpress Plugin
 * Infos and help to use immocaster.
 */

/**
 * Infos for using immocaster.
 *
 * @return void
 */
function immocaster_pages_main()
{
?>

	<div class="wrap">
		<h2><?php echo __('Immocaster Help',IMMOCASTER_PO_TEXTDOMAIN); ?></h2>
		<p>
		<?php 
			echo __('Please activate JavaScript in your browser, if it is deactivated.',IMMOCASTER_PO_TEXTDOMAIN);
			echo __('If you need help, watch our tutorials or visit our forum.',IMMOCASTER_PO_TEXTDOMAIN);
		?>
        </p>
	</div>
    
<?php
}