<?php

function immocaster_theme_object_standard($aVars)
{
	$aExpose = $aVars[0];
	$sCode = '<div class="immocaster_object_wrapper">';

	// Address
	$sCode .= '<address>';
	if($aExpose['address']['street'])
	{
		$sCode .= $aExpose['address']['street'] . '&nbsp;' . $aExpose['address']['houseNumber'] . ',&nbsp;';
	}
	$sCode .= $aExpose['address']['postcode'] . '&nbsp;' . $aExpose['address']['city'];
	$sCode .= '</address>';
	
	// Gallery (no javascript)
	$sCode .= '<div class="entry-image">';
	$sCode .= '<img src="'.$aExpose['media']['pictures'][0]['pictures'][3]['href'].'">';
	$sCode .= '</div>';
	
	// Gallery (javascript)
	$sCode .= '<div id="exposegallery_wrapper_galleria" style="display:none;"><div id="exposegallery">';
	foreach($aExpose['media']['pictures'] as $aPicture)
	{
		$sCode .= '<img src="'.$aPicture['pictures'][3]['href'].'">&nbsp;';
	}
	$sCode .= '</div></div>';
	$sCode .= '<script language="javascript">
	jQuery("#exposegallery_wrapper_galleria").css("display","inline-block");
	Galleria.loadTheme("'.plugins_url('immocaster/lib/galleria/themes/classic/galleria.classic.min.js').'");
	jQuery("#exposegallery").galleria({width:480,height:400});
	jQuery(".entry-image").css("display","none");
	</script>';

	// Infotable
	$aObjectInfos = array
	(
		array('baseRent',$aExpose['costs']['baseRent'],
			$aExpose['costs']['baseRent'].'&nbsp;'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN),__('Rental price',IMMOCASTER_PO_TEXTDOMAIN)),
		array('buyPrice',$aExpose['costs']['buyPrice'],
			$aExpose['costs']['buyPrice'].'&nbsp;'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN),__('Purchase price',IMMOCASTER_PO_TEXTDOMAIN)),
		array('numberOfRooms',$aExpose['main']['numberOfRooms'],
			$aExpose['main']['numberOfRooms'],__('Number of rooms',IMMOCASTER_PO_TEXTDOMAIN)),
		array('livingSpace',$aExpose['main']['livingSpace'],
			$aExpose['main']['livingSpace'].'&nbsp;'.__('m²',IMMOCASTER_PO_TEXTDOMAIN),__('Livingspace',IMMOCASTER_PO_TEXTDOMAIN)),
		array('floor',$aExpose['main']['floor'],
			$aExpose['main']['floor'],__('Floor',IMMOCASTER_PO_TEXTDOMAIN)),
		array('numberOfFloors',$aExpose['main']['numberOfFloors'],
			$aExpose['main']['numberOfFloors'],__('Number of floors',IMMOCASTER_PO_TEXTDOMAIN)),
		array('apartmentType',$aExpose['main']['apartmentType'],
			constant('IMMOCASTER_LANG_APARTMENTTYPE_'.$aExpose['main']['apartmentType']),__('Apartmenttype',IMMOCASTER_PO_TEXTDOMAIN)),
		array('usableFloorSpace',$aExpose['main']['usableFloorSpace'],
			$aExpose['main']['usableFloorSpace'].'&nbsp;'.__('m²',IMMOCASTER_PO_TEXTDOMAIN),__('Usable floorspace',IMMOCASTER_PO_TEXTDOMAIN)),
		array('totalRent',$aExpose['costs']['totalRent'],
			$aExpose['costs']['totalRent'].'&nbsp;'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN),__('Total rent',IMMOCASTER_PO_TEXTDOMAIN)),
		array('cellar',$aExpose['extras']['cellar'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Cellar',IMMOCASTER_PO_TEXTDOMAIN)),
		array('balcony',$aExpose['extras']['balcony'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Balcony',IMMOCASTER_PO_TEXTDOMAIN)),
		array('lift',$aExpose['extras']['lift'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Lift',IMMOCASTER_PO_TEXTDOMAIN)),
		array('garden',$aExpose['extras']['garden'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Garden',IMMOCASTER_PO_TEXTDOMAIN)),
		array('petsAllowed',$aExpose['extras']['petsAllowed'],
			constant('IMMOCASTER_LANG_PETSALLOWED_'.$aExpose['extras']['petsAllowed']),__('Pets allowed',IMMOCASTER_PO_TEXTDOMAIN)),
		array('builtInKitchen',$aExpose['extras']['builtInKitchen'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Kitchen',IMMOCASTER_PO_TEXTDOMAIN)),
		array('handicappedAccessible',$aExpose['extras']['handicappedAccessible'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Handicapped accessible',IMMOCASTER_PO_TEXTDOMAIN)),
		array('guestToilet',$aExpose['extras']['guestToilet'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Guesttoilet',IMMOCASTER_PO_TEXTDOMAIN)),
		array('courtage',$aExpose['costs']['courtage'],
			__('Yes',IMMOCASTER_PO_TEXTDOMAIN),__('Courtage',IMMOCASTER_PO_TEXTDOMAIN)),
	);
	$sCode .= '<table cellpadding="0" cellspacing="0" border="0" id="ImmocasterContentObjectInfoTable">';
	foreach($aObjectInfos as $aObjectInfo)
	{
		if($aObjectInfo[1] != '' && $aObjectInfo[1] != 'NO_INFORMATION')
		{
			$sCode .= '<tr id="ImmocasterContentObjectInfoTable-'.ucfirst($aObjectInfo[0]).'">'.
			'<td class="ImmocasterContentObjectInfoTableLft">'.$aObjectInfo[3].':</td>'.
			'<td class="ImmocasterContentObjectInfoTableRgt">'.$aObjectInfo[2].'</td>'.
			'</tr>';
		}
	}
	$sCode .= '</table>';
	
	// Maps
	$aMapParameter = array('zoom'=>14,'width'=>'100%');
	$sCode .= icGoogleMaps($aExpose['geo'],$aExpose['address']['city'],$aMapParameter);
	
	// Infos
	$aInfos = array('desc','furnishing','location','other');
	foreach($aInfos as $sInfo)
	{
		if($aExpose['notes'][$sInfo])
		{
			$sCode .= '<div style="immocaster_infoblock_'.$sInfo.'">';
			$sCode .= '<h2>' . constant('IMMOCASTER_LANG_OBJECT_INFOHEADLINE_'.strtoupper($sInfo)) . ':</h2>';
			$sCode .= '<p>' . nl2br($aExpose['notes'][$sInfo]) . '</p>';
			$sCode .= '</div>';
		}
	}
	
	// Contact
	if(get_option('is24_show_contactbox','')=='yes')
	{
		$sCode .= '<div class="immocaster_object_contactbox">';
		if($aExpose['contact']['company']!='')
		{
			$sCode .= '<h2>' . $aExpose['contact']['company'] . '</h2>';
		}
		else
		{
			$sCode .= '<h2>' . constant('IMMOCASTER_LANG_OBJECT_INFOHEADLINE_CONTACT'). '</h2>';
		}
		if($aExpose['contact']['logo']!='')
		{
			$sCode .= '<div class="immocaster_object_contactlogo">';
			$sCode .= '<img src="'.$aExpose['contact']['logo'].'">';
			$sCode .= '</div>';
		}
		$sCode .= '<div class="contactbox_infos">';
		if($aExpose['contact']['firstname'] !='' && $aExpose['contact']['lastname']!='')
		{
			$sCode .= '<div class="contactbox_contactperson"><span class="contactbox_label">' . __('Contact person',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value">' . $aExpose['contact']['firstname'] . ' ' . $aExpose['contact']['lastname'] . '</span></div>';
		}
		if($aExpose['contact']['homepage'] != '')
		{
			$sCode .= '<div class="contactbox_homepage"><span class="contactbox_label">' . __('Homepage',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value"><a href="' . $aExpose['contact']['homepage'] . '">' . $aExpose['contact']['homepage'] . '</a></span></div>';
		}
		if($aExpose['contact']['mobile'] != '')
		{
			$sCode .= '<div class="contactbox_mobile"><span class="contactbox_label">' . __('Mobile phone',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value">' . $aExpose['contact']['mobile'] . '</span></div>';
		}
		if($aExpose['contact']['phone'] != '')
		{
			$sCode .= '<div class="contactbox_phone"><span class="contactbox_label">' . __('Phone',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value">' . $aExpose['contact']['phone'] . '</span></div>';
		}
		if($aExpose['contact']['fax'] != '')
		{
			$sCode .= '<div class="contactbox_fax"><span class="contactbox_label">' . __('Fax',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value">' . $aExpose['contact']['fax'] . '</span></div>';
		}
		if($aExpose['contact']['address']['street'] != '' && $aExpose['contact']['address']['houseNumber']!='')
		{
			$sCode .= '<div class="contactbox_street_housenumber"><span class="contactbox_label">' . __('Street',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value">' . $aExpose['contact']['address']['street'] . ' ' . $aExpose['contact']['address']['houseNumber'] . '</span></div>';
		}
		if($aExpose['contact']['address']['zip'] != '' && $aExpose['contact']['address']['city']!='')
		{
			$sCode .= '<div class="contactbox_zip_city"><span class="contactbox_label">' . __('City',IMMOCASTER_PO_TEXTDOMAIN) . ':</span> <span class="contactbox_value">' . $aExpose['contact']['address']['zip'] .  ' ' . $aExpose['contact']['address']['city'] . '</span></div>';
		}
		$sCode .= '</div>';
		$sCode .= '</div>';
	}
	
	// CSS
	$sCode .= '<style type="text/css">
		.entry-meta,#comments,#nav-single{display:none;visibility:hidden;}
		.immocaster_servicelink{margin:10px 0;}
	</style>';
	
	// Link (Redirect)
	$sCode .= '<center><div class="immocaster_servicelink">';
	$sCode .= '<a href="'.$aExpose['main']['link'].'" target="_blank">'.__('More infos & Contact',IMMOCASTER_PO_TEXTDOMAIN).'</a>';
	$sCode .= '</div></center>';
	
	$sCode .= '</div>';
	
	return $sCode;
}