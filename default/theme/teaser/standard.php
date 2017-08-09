<?php

function immocaster_theme_teaser_standard($aVars)
{
	$sCode = '<div class="immocaster_teaser_wrapper">';
	foreach($aVars[0]['list'] as $aEntry)
	{
		$sCode .= '<div class="immocaster_teaser_entry">';
        $sCode .= '<h3 class="immocaster_teaser_entry_title"><a href="'.$aEntry['main']['link'].'">'.$aEntry['title'].'</a></h3>';
        
		$sCode .= '<div class="immocaster_teaser_entry_address">';
        $sCode .= '<address>';
        
		if($aEntry['address']['street']!='')
		{
			$sCode .= $aEntry['address']['street'] . ' ' . $aEntry['address']['houseNumber'] . '<br />';
		}
		if($aEntry['address']['postcode']>999)
		{
			$sCode .= $aEntry['address']['postcode'] . ' ';
		}
		$sCode .= $aEntry['address']['city']; 
               
		$sCode .= '</address>';
        $sCode .= '</div>';
        $sCode .= '<div class="immocaster_teaser_entry_image"><a href="'.$aEntry['main']['link'].'"><img src="'.$aEntry['media']['mainPicture'].'"></a></div>';  
        $sCode .= '<div class="immocaster_teaser_entry_infos">';
        $sCode .= '<div class="immocaster_teaser_entry_info_price">';
        $sCode .= '<span class="label">'.__('Price',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
        $sCode .= '<span class="value">'.$aEntry['price']['main']['value'].'&nbsp;'.__('EUR',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		$sCode .= '</div>';
		$sCode .= '<div class="immocaster_teaser_entry_info_rooms">';
		$sCode .= '<span class="label">'.__('Rooms',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.$aEntry['numberOfRooms'].'</span>';
		$sCode .= '</div>';
		$sCode .= '<div class="immocaster_teaser_entry_info_size">';
		$sCode .= '<span class="label">'.__('Size',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.$aEntry['livingSpace'].'&nbsp;'.__('m²',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		$sCode .= '</div>';
		$sCode .= '</div>';
		$sCode .= '</div>';
    
	}
	$sCode .= '</div>';
	return $sCode;
}