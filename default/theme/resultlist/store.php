<?php

function immocaster_theme_resultlist_store($aEntry)
{
	$sCode = '';
	$sCode .= '<div class="immocaster_resultlist_entry">';
	$sCode .= '<h2 class="immocaster_resultlist_entry_title"><a href="'.$aEntry['main']['link'].'">'.$aEntry['title'].'</a></h2>';
	$sCode .= '<div class="immocaster_resultlist_entry_address">';
	$sCode .= '<address>';
	if($aEntry['address']['street']!='')
	{
		$sCode .= $aEntry['address']['street'] . ' ' . $aEntry['address']['houseNumber'] . ', ';
	}
	if($aEntry['address']['postcode']>999)
	{
		$sCode .= $aEntry['address']['postcode'] . ' ';
	}
	if(isset($aEntry['address']['quarter']))
	{
		$sCode .= $aEntry['address']['quarter'] . ' / ';
	}
	$sCode .= $aEntry['address']['city']; 
	$sCode .= '</address>';
	$sCode .= '</div>';
	$sCode .= '<div class="immocaster_resultlist_entry_image"><a href="'.$aEntry['main']['link'].'"><img src="'.$aEntry['media']['mainPicture'].'"></a></div>';
	$sCode .= '<div class="immocaster_resultlist_entry_infos">';
	$sCode .= '<div class="immocaster_resultlist_entry_info_price">';
	if(strtolower($aEntry['price']['main']['commercializationType'])=='buy')
	{
		$sCode .= '<span class="label">'.__('Purchase price',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.$aEntry['price']['main']['value'].'&nbsp;'.$aEntry['price']['main']['currency'].'</span>';
	}
	if(strtolower($aEntry['price']['main']['commercializationType'])=='rent')
	{
		$sCode .= '<span class="label">'.__('Rental price',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.$aEntry['price']['main']['value'].'&nbsp;'.$aEntry['price']['main']['currency'].'</span>';
		$sCode .= '</div><div class="immocaster_resultlist_entry_info_subprice">';
		$sCode .= '<span class="label">'.__('Rental price per sqm',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.$aEntry['price']['sqm']['value'].'&nbsp;'.$aEntry['price']['sqm']['currency'].'</span>';
	}
	$sCode .= '</div>';
	$sCode .= '<div class="immocaster_resultlist_entry_info_size">';
	$sCode .= '<span class="label">'.__('Store space',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
	$sCode .= '<span class="value">'.$aEntry['storeSpace'].'&nbsp;'.__('m²',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
	$sCode .= '</div>';
	if(strtolower($aEntry['price']['courtage']['hasCourtage'])=='yes')
	{
		$sCode .= '<div class="immocaster_resultlist_entry_info_size">';
		$sCode .= '<span class="label">'.__('Has courtage',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.__('Yes',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		$sCode .= '</div>';
	}
	if(strtolower($aEntry['price']['courtage']['hasCourtage'])=='no')
	{
		$sCode .= '<div class="immocaster_resultlist_entry_info_size">';
		$sCode .= '<span class="label">'.__('Has Courtage',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.__('No',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		$sCode .= '</div>';
	}
	if(strtolower($aEntry['price']['main']['commercializationType'])=='buy')
	{
		$sCode .= '<div class="immocaster_resultlist_entry_info_size">';
		$sCode .= '<span class="label">'.__('Buy',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.__('Yes',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		$sCode .= '</div>';
	}
	if(strtolower($aEntry['price']['main']['commercializationType'])=='rent')
	{
		$sCode .= '<div class="immocaster_resultlist_entry_info_size">';
		$sCode .= '<span class="label">'.__('Rent',IMMOCASTER_PO_TEXTDOMAIN).':</span>&nbsp;';
		$sCode .= '<span class="value">'.__('Yes',IMMOCASTER_PO_TEXTDOMAIN).'</span>';
		$sCode .= '</div>';
	}
	$sCode .= '</div>';
	$sCode .= '<div class="immocaster_Resultlist_clear">&nbsp;</div>';
	$sCode .= '</div>';
	return $sCode;
}