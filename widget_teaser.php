<?php

add_action('widgets_init',array('Immocaster_Widget_Teaser','register'));

class Immocaster_Widget_Teaser extends WP_Widget
{
	
	// Activate widget teaser
	function Immocaster_Widget_Teaser()
	{
		parent::WP_Widget(
			false,
			__('Immocaster Teaser',IMMOCASTER_PO_TEXTDOMAIN),
			array('description'=>__('A very simple and short real estate list.',IMMOCASTER_PO_TEXTDOMAIN))
		);
	}
	
	// Widget front
	function widget($args,$instance)
	{
		$aRegion = explode(' | ',$instance['region']);
		$sRegionName = $aRegion[0];
		$iRegionId = $aRegion[1];
		if((int)$iRegionId<=1)
		{
			return false;
		}else{
			global $post;
			extract($args,EXTR_SKIP);
			$sOutput = $before_widget;
			// Title
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
			if ( !empty( $title ) ) { $sOutput .= $before_title . $title . $after_title; };
			// Create code for widget
			$aParameters = array('geocodes'=>$iRegionId,'realestatetype'=>strtolower($instance['searchtype']),
			'sorting'=>$instance['sorting'],'price'=>$instance['price_from'].'-'.$instance['price_till']);
			// Realtor filter
			if(isset($instance['realtor_only']))
			{
				$aParameters['username'] = get_option('is24_account_username');
                $aParameters['channel']  = 'hp';
			}
			$oImmocasterSDK = ImmocasterSDK::getInstance('is24');
			$res = $oImmocasterSDK->regionSearch($aParameters);
			if($res['list'] = $oImmocasterSDK->shortenList($res['list'],(int)$instance['objectcount']))
			{
				$sOutput .= immocaster_theme('teaser',array($res));
			}
			$sOutput .= $after_widget;
			echo $sOutput;
		}
	}
	
	// Form (backend)
	function form($instance)
	{
		// Parameter.
		$instance = wp_parse_args((array)$instance,array('title'=>'','region'=>'','searchtype'=>'AppartmentRent',
		'objectcount'=>3,'sorting'=>'price','price_from'=>'50','price_till'=>'2000','realtor_only'=>0));
		// Title
		echo '<p><label for="'.$this->get_field_id('title').'">'.__('Title',IMMOCASTER_PO_TEXTDOMAIN).
		':</label><br /><input class="widefat" id="'.$this->get_field_id('title').'" name="'.
		$this->get_field_name('title').'" type="text" value="'.esc_attr($instance['title']).'"></p>';
		// Region
		echo '<p><label for="'.$this->get_field_id('region').'">'.__('Region',IMMOCASTER_PO_TEXTDOMAIN).
		':</label><br /><input class="widefat immocaster_ajax_region_autocomplete" id="'.$this->get_field_id('region').'" name="'.
		$this->get_field_name('region').'" type="text" value="'.esc_attr($instance['region']).'"><span class="widget_javascript_note">'.__('<strong>Important:</strong> You have to enable JavaScript in your Browser to use autocompletion. This will build the correct region with the geo-code (e.g. Berlin | 1276003001). ',IMMOCASTER_PO_TEXTDOMAIN).'</span></p><script language="JavaScript">jQuery(".widget_javascript_note").css("display","none");</script>';
		// Object-Type.
		echo '<p><label for="'.$this->get_field_id('searchtype').'">'.__('Object-Type',IMMOCASTER_PO_TEXTDOMAIN).
		':&nbsp;</label><select id="'.$this->get_field_id('searchtype').'" name="'.
		$this->get_field_name('searchtype').'" value="'.esc_attr($instance['searchtype']).'">';
		echo '<option value="ApartmentRent" ';
		if($instance['searchtype']=='ApartmentRent'){ echo ' selected';}
		echo '>'.__('Apartment Rent',IMMOCASTER_PO_TEXTDOMAIN).'</option>';
		echo '<option value="ApartmentBuy" ';
		if($instance['searchtype']=='ApartmentBuy'){ echo ' selected';}
		echo '>'.__('Apartment Buy',IMMOCASTER_PO_TEXTDOMAIN).'</option>';
		echo '<option value="HouseRent" ';
		if($instance['searchtype']=='HouseRent'){ echo ' selected';}
		echo '>'.__('House Rent',IMMOCASTER_PO_TEXTDOMAIN).'</option>';
		echo '<option value="HouseBuy" ';
		if($instance['searchtype']=='HouseBuy'){ echo ' selected';}
		echo '>'.__('House Buy',IMMOCASTER_PO_TEXTDOMAIN).'</option>';
		echo '</select></p>';		
		// Objectcount
		echo '<p><label for="'.$this->get_field_id('objectcount').'">'.__('Objectcount',IMMOCASTER_PO_TEXTDOMAIN).
		': </label><select id="'.$this->get_field_id('objectcount').'" name="'.
		$this->get_field_name('objectcount').'" value="'.esc_attr($instance['objectcount']).'">';
		for($i=1;$i<=10;$i++)
		{
			echo '<option value="'.$i.'" ';
				if($instance['objectcount']==$i){ echo ' selected';}
			echo '>'.$i.'</option>';
		}
		echo '</select></p>';
		// Sort by
		echo '<p><label for="'.$this->get_field_id('sorting').'">'.__('Sort by',IMMOCASTER_PO_TEXTDOMAIN).
		': </label><select id="'.$this->get_field_id('sorting').'" name="'.$this->get_field_name('sorting').'"
		value="'.esc_attr($instance['sorting']).'">';
		echo '<option value="price" ';
		if($instance['sorting']=='price'){ echo ' selected';}
		echo '>'.__('Price (Low to high)',IMMOCASTER_PO_TEXTDOMAIN).'</option>';
		echo '<option value="-price" ';
		if($instance['sorting']=='-price'){ echo ' selected';}
		echo '>'.__('Price (High to low)',IMMOCASTER_PO_TEXTDOMAIN).'</option>';
		echo '</select></p>';
		// Price.
		echo '<p>'.__('Price (min.)',IMMOCASTER_PO_TEXTDOMAIN).
		'<input size="4" maxlength="7" id="'.$this->get_field_id('price_from').'" name="'.
		$this->get_field_name('price_from').'" type="text" value="'.esc_attr($instance['price_from']).'">'.
		__('EUR',IMMOCASTER_PO_TEXTDOMAIN).'<br />'.__('Price (max.)',IMMOCASTER_PO_TEXTDOMAIN).'&nbsp;<input size="4" maxlength="7"
		id="'.$this->get_field_id('price_till').'" name="'.$this->get_field_name('price_till').
		'" type="text" value="'.esc_attr($instance['price_till']).'">'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN).'</p>';
		// Only user estates
		echo '<p>'.__('Only objects from realtor',IMMOCASTER_PO_TEXTDOMAIN).':&nbsp;';	
		global $wpdb;
		if($wpdb->query("SELECT * FROM Immocaster_Storage WHERE ic_desc='APPLICATION'")==0)
		{
			echo '<span class="immocaster_text_warning">'.__('Please first certify the application in settings!',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		}else{
			echo '<input id="'.$this->get_field_id('realtor_only').'" name="'.$this->get_field_name('realtor_only').'" type="checkbox"';
			if($instance['realtor_only']){ echo ' checked';}
			echo '>';
		}
		echo '</p>';
		// Found results (count)
		if(substr_count($instance['region'],' | ')>=1)
		{
			$aRegion = explode(' | ',$instance['region']);
			$sRegionName = $aRegion[0];
			$iRegionId = $aRegion[1];
			$aParameters = array('geocodes'=>(int)$iRegionId,'realestatetype'=>strtolower($instance['searchtype']),
			'sorting'=>$instance['sorting'],'price'=>$instance['price_from'].'-'.$instance['price_till']);
			// Realtor filter
			if($instance['realtor_only']==true)
			{
				$aParameters['username'] = get_option('is24_account_username');
                $aParameters['channel']  = 'hp';
			}
			// 
			$oImmocasterSDK = ImmocasterSDK::getInstance('is24');
			$res = $oImmocasterSDK->regionSearch($aParameters);
			echo '<div class="immocaster_teaser_found_hits"><div class="immocaster_teaser_found_hits_number">'.$res['paging']['numberOfHits'].'</div>&nbsp;'.__('results found for this settings.',IMMOCASTER_PO_TEXTDOMAIN).'</div>';
		}
	}
	
	// Upadate vars
	function update($new_instance,$old_instance)
	{
		return $new_instance;
	}
	
	// Register teaser
	public static function register()
	{
		register_widget('Immocaster_Widget_Teaser');
	}
	
}