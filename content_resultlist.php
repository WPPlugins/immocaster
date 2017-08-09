<?php

// Add metabox for resultlist
add_action('admin_init','immocaster_resultlist_meta_init');
function immocaster_resultlist_meta_init()
{
	foreach (array('page') as $type) 
	{
		add_meta_box(
			'immocaster_resultlist_all_meta',
			__('Immocaster Resultlist',IMMOCASTER_PO_TEXTDOMAIN),
			'immocaster_resultlist_meta_setup',
			$type,
			'normal',
			'high'
		);
	}
	if($_POST)
	{
		add_action('save_post','immocaster_resultlist_meta_save');
	}
}

// Build html for metabox (resultlist setup)
function immocaster_resultlist_meta_setup()
{
	global $post,$wpdb;
	$meta = get_post_meta($post->ID,'_immocaster_meta',TRUE);
	// Enable resultlist
	$sFieldName = 'immocaster_mb_resultlist_show_resultlist';
	if(!empty($meta[$sFieldName])){$sValue=' checked';}else{$sValue='';}
	echo '<label>'.__('Enable resultlist',IMMOCASTER_PO_TEXTDOMAIN).':</label>';
	echo '<p><input type="checkbox" name="_immocaster_meta['.$sFieldName.']" rows="3" '.$sValue.' /></p>';
	// Region
	$sFieldName = 'immocaster_mb_resultlist_region';
	if(!empty($meta['immocaster_mb_resultlist_all_regions'])){$sValue1=' disabled="disabled"';$sValue2=' checked';}else{$sValue1='';$sValue2='';}
	if(!empty($meta[$sFieldName])){$sValue=$meta[$sFieldName];}else{$sValue='';}
	echo '<label>'.__('Region',IMMOCASTER_PO_TEXTDOMAIN).':</label>';
	echo '<p><input name="_immocaster_meta['.$sFieldName.']" rows="3" value="'.$sValue.'" '.$sValue1.' class="immocaster_mb_resultlist_region">';
	echo '&nbsp;<input type="checkbox" name="_immocaster_meta['.'immocaster_mb_resultlist_all_regions'.']" '.
	$sValue2.' class="immocaster_mb_resultlist_all_regions" />&nbsp;'.__('Show all regions',IMMOCASTER_PO_TEXTDOMAIN).'</p>';
	// Objecttype/Searchtype
	$aSearchTypes = array(
		array('ApartmentRent',__('Apartment Rent',IMMOCASTER_PO_TEXTDOMAIN)),
		array('ApartmentBuy',__('Apartment Buy',IMMOCASTER_PO_TEXTDOMAIN)),
		array('HouseRent',__('House Rent',IMMOCASTER_PO_TEXTDOMAIN)),
		array('HouseBuy',__('House Buy',IMMOCASTER_PO_TEXTDOMAIN)),
		array('OfficeRentMonth',__('Office Rent (Price per month)',IMMOCASTER_PO_TEXTDOMAIN)),
		array('OfficeRentSqm',__('Office Rent (Price per sqm)',IMMOCASTER_PO_TEXTDOMAIN)),
		array('OfficeBuy',__('Office Buy',IMMOCASTER_PO_TEXTDOMAIN)),
		array('StoreRentMonth',__('Store Rent (Price per month)',IMMOCASTER_PO_TEXTDOMAIN)),
		array('StoreRentSqm',__('Store Rent (Price per sqm)',IMMOCASTER_PO_TEXTDOMAIN)),
		array('StoreBuy',__('Store Buy',IMMOCASTER_PO_TEXTDOMAIN))
	);
	$sFieldName = 'immocaster_mb_resultlist_searchtype';
	echo '<label>'.__('Object-Type',IMMOCASTER_PO_TEXTDOMAIN).':</label>';
	echo '<p><select name="_immocaster_meta['.$sFieldName.']">';
	foreach($aSearchTypes as $aSearchType)
	{
		$sValue='';
		if($meta[$sFieldName]==$aSearchType[0]){$sValue='selected';}
		echo '<option value="'.$aSearchType[0].'" '.$sValue.'>'.$aSearchType[1].'</option>';
	}
	echo '</select></p>';
	// Sorting
	$aSortTypes = array(
		array('price',__('Price (Low to high)',IMMOCASTER_PO_TEXTDOMAIN)),
		array('-price',__('Price (High to low)',IMMOCASTER_PO_TEXTDOMAIN))
	);
	$sFieldName = 'immocaster_mb_resultlist_sorttype';
	echo '<label>'.__('Sort by',IMMOCASTER_PO_TEXTDOMAIN).':</label>';
	echo '<p><select name="_immocaster_meta['.$sFieldName.']">';
	foreach($aSortTypes as $aSortType)
	{
		$sValue='';
		if($meta[$sFieldName]==$aSortType[0]){$sValue='selected';}
		echo '<option value="'.$aSortType[0].'" '.$sValue.'>'.$aSortType[1].'</option>';
	}
	echo '</select></p>';
	// Price
	$sFieldName = 'immocaster_mb_resultlist_price_from';
	if(!empty($meta[$sFieldName])){$sValue=$meta[$sFieldName];}else{$sValue='0';}
	echo '<label>'.__('Price (min.)',IMMOCASTER_PO_TEXTDOMAIN).':</label>';
	echo '<p><input name="_immocaster_meta['.$sFieldName.']" rows="3" maxlength="10" value="'.$sValue.'">&nbsp;'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN).'</p>';
	$sFieldName = 'immocaster_mb_resultlist_price_till';
	if(!empty($meta[$sFieldName])){$sValue=$meta[$sFieldName];}else{$sValue='10000';}
	echo '<label>'.__('Price (max.)',IMMOCASTER_PO_TEXTDOMAIN).':</label>';
	echo '<p><input name="_immocaster_meta['.$sFieldName.']" rows="3" maxlength="10" value="'.$sValue.'">&nbsp;'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN).'</p>';
	echo '<input type="hidden" name="immocaster_meta_submitcheck" value="'.wp_create_nonce(__FILE__).'" />';
	// Fulltext-Search
	echo '<label><strong>'.__('Basic-Feature:',IMMOCASTER_PO_TEXTDOMAIN).'</strong><br />'.__('Search for special words',IMMOCASTER_PO_TEXTDOMAIN).':&nbsp;</label>';
	$sFieldName = 'immocaster_mb_resultlist_fulltext';
	if(!empty($meta[$sFieldName])){$sValue=$meta[$sFieldName];}else{$sValue='';}
	echo '<input name="_immocaster_meta['.$sFieldName.']" maxlength="64" value="'.$sValue.'"> '.__('(e.g. garden, pets, ...)',IMMOCASTER_PO_TEXTDOMAIN).'<br />';
	// Only real estate agents
	echo '<label><strong>'.__('Pro-Feature:',IMMOCASTER_PO_TEXTDOMAIN).'</strong><br />'.__('Only objects from realtor',IMMOCASTER_PO_TEXTDOMAIN).':&nbsp;</label>';
	$sFieldName = 'immocaster_mb_resultlist_realtor_only';
	if($wpdb->query("SELECT * FROM Immocaster_Storage WHERE ic_desc='APPLICATION'")==0)
	{
		echo '<span class="immocaster_text_warning">'.__('Please first certify the application in settings!',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
	}else{
		echo '<input id="_immocaster_meta['.$sFieldName.']" name="_immocaster_meta['.$sFieldName.']" type="checkbox"';
		if(!empty($meta[$sFieldName])){$sValue=' checked';}else{$sValue='';}
		echo $sValue . '>';
	}
	// Found results (count)
	if(isset($meta['immocaster_mb_resultlist_region']))
	{
		if(substr_count($meta['immocaster_mb_resultlist_region'],' | ')>=1)
		{
			$aRegion = explode(' | ',$meta['immocaster_mb_resultlist_region']);
			$sRegionName = $aRegion[0];
			$iRegionId = $aRegion[1];
			$sRegionKey = 'geocodes';
			$sRegionValue = $iRegionId;
		}
	}
	if(!empty($meta['immocaster_mb_resultlist_all_regions']))
	{
		$sRegionKey = 'geocoordinates';
		$sRegionValue = '52.52546480183439;13.369545936584473;9999999999';
	}
	if(isset($sRegionKey) && isset($sRegionValue))
	{
		$aParameters = array(
			$sRegionKey => $sRegionValue,
			'realestatetype'=>strtolower($meta['immocaster_mb_resultlist_searchtype']),
			'sorting'=>$meta['immocaster_mb_resultlist_sorttype'],
			'price'=>$meta['immocaster_mb_resultlist_price_from']. '-' . $meta['immocaster_mb_resultlist_price_till']
		);
		// Fulltext
		if(isset($meta['immocaster_mb_resultlist_fulltext']))
		{
			$aParameters['fulltext'] = $meta['immocaster_mb_resultlist_fulltext'];
		}
		// Realtor only
		if(isset($meta['immocaster_mb_resultlist_realtor_only']))
		{
			$aParameters['username'] = get_option('is24_account_username');
			$aParameters['channel']  = 'hp';
		}
		$oImmocasterSDK = ImmocasterSDK::getInstance('is24');
		$res = $oImmocasterSDK->regionSearch($aParameters);
		echo '<div class="immocaster_resultlist_found_hits"><div class="immocaster_resultlist_found_hits_number">'.
		$res['paging']['numberOfHits'].'</div>&nbsp;'.__('results found for this settings.',IMMOCASTER_PO_TEXTDOMAIN).'</div>';
	}
}

// Save metabox data 
function immocaster_resultlist_meta_save($post_id) 
{
	if(isset($_POST['immocaster_meta_submitcheck']))
	{
		if(!wp_verify_nonce($_POST['immocaster_meta_submitcheck'],__FILE__)) return $post_id;
		$current_data = get_post_meta($post_id, '_immocaster_meta', TRUE);	
		$new_data = $_POST['_immocaster_meta'];
		immocaster_meta_clean($new_data);
		if($current_data) 
		{
			if(is_null($new_data)) delete_post_meta($post_id,'_immocaster_meta');
			else update_post_meta($post_id,'_immocaster_meta',$new_data);
		}
		elseif (!is_null($new_data))
		{
			add_post_meta($post_id,'_immocaster_meta',$new_data,TRUE);
		}
	}
	return $post_id;
}

// Clean array
function immocaster_meta_clean(&$arr)
{
	if (is_array($arr))
	{
		foreach($arr as $i=>$v)
		{
			if(is_array($arr[$i])) 
			{
				immocaster_meta_clean($arr[$i]);
				if(!count($arr[$i])) 
				{
					unset($arr[$i]);
				}
			}
			else 
			{
				if(trim($arr[$i])=='') 
				{
					unset($arr[$i]);
				}
			}
		}
		if (!count($arr)) 
		{
			$arr=NULL;
		}
	}
}

// Register frontend resultlist
if(!is_admin())
{
	add_filter('the_content','immocaster_content_show_resultlist');
}

// Show frontend resultlist
function immocaster_content_show_resultlist($text)
{
	global $post, $wp_query;
	if(is_page())
	{
		// Get metadata
		$immocaster_meta = get_post_meta($post->ID,'_immocaster_meta',TRUE);
		// Search by region
		if(isset($immocaster_meta['immocaster_mb_resultlist_region']))
		{
			if(substr_count($immocaster_meta['immocaster_mb_resultlist_region'],' | ')>=1)
			{
				$aRegion      = explode(' | ',$immocaster_meta['immocaster_mb_resultlist_region']);
				$sRegionName  = $aRegion[0];
				$iRegionId    = $aRegion[1];
				$sRegionKey   = 'geocodes';
				$sRegionValue = $iRegionId;
			}
		}
		// Search by radius
		if(!empty($immocaster_meta['immocaster_mb_resultlist_all_regions']))
		{
			$sRegionKey = 'geocoordinates';
			$sRegionValue = '52.52546480183439;13.369545936584473;9999999999';
		}
		// Code for resultlist
		if(isset($immocaster_meta['immocaster_mb_resultlist_show_resultlist']) && isset($sRegionKey) && isset($sRegionValue))
		{
			$aParameters = array(
				$sRegionKey      => $sRegionValue,
				'realestatetype' => strtolower($immocaster_meta['immocaster_mb_resultlist_searchtype']),
				'sorting'        => $immocaster_meta['immocaster_mb_resultlist_sorttype'],
				'price'          => $immocaster_meta['immocaster_mb_resultlist_price_from'].'-'.$immocaster_meta['immocaster_mb_resultlist_price_till']
			);
			if(isset($wp_query->query_vars[IMMOCASTER_PAGINATOR_PAGER]))
			{
				$aParameters['pagenumber'] = (int)$wp_query->query_vars[IMMOCASTER_PAGINATOR_PAGER];
			}
			// Fulltext
			if(isset($immocaster_meta['immocaster_mb_resultlist_fulltext']))
			{
				$aParameters['fulltext'] = $immocaster_meta['immocaster_mb_resultlist_fulltext'];
			}
			// Realtor only
			if(isset($immocaster_meta['immocaster_mb_resultlist_realtor_only']))
			{
				$aParameters['username'] = get_option('is24_account_username');
				$aParameters['channel']  = 'hp';
			}
			// Search with Immocaster-SDK
			$oImmocasterSDK = ImmocasterSDK::getInstance('is24');
			$res = $oImmocasterSDK->regionSearch($aParameters);
			// Output
			$sOutput = immocaster_theme('resultlist', array($res));
			$sPostContent = $post->post_content;
			if(isset($wp_query->query_vars[IMMOCASTER_PAGINATOR_PAGER]))
			{
				if((int)$wp_query->query_vars[IMMOCASTER_PAGINATOR_PAGER]>1)
				{
					$sPostContent = '';
				}
			}
			return $sPostContent.$sOutput;	
		}
	}
	return $text;
}