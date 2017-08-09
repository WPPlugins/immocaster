<?php

// Theme function for immocaster output
function immocaster_theme($sFile=false,$aVars=array())
{
	$sDefault = WP_PLUGIN_DIR.'/immocaster/default/theme/'.$sFile.'.php';
	$sCustom  = WP_PLUGIN_DIR.'/immocaster/custom/theme/'.$sFile.'.php';
	if(!is_file($sCustom))
	{
		require_once($sDefault);
	}else{
		require_once($sCustom);
	}
	$sFunction = 'immocaster_theme_'.$sFile;
	return $sFunction($aVars);
}

// Pagination
function icPaginator($iCurrentPage,$iPages,$iLinkShown,$sSpacer='')
{
	$sSpacerText = $sSpacer;
	$aPageArray = array();
	for($i=1;$i<=$iPages;$i++)
	{
		if ($i==1 || $i==$iPages || ($i>=$iCurrentPage-$iLinkShown && $i<=$iCurrentPage+$iLinkShown))
		{
			$sSpacer = $sSpacerText;
			if($i!=$iCurrentPage)
			{
				if(get_option('permalink_structure'))
				{
					// With permalinks
					global $post;
					$aPageArray[$i]['sUrl'] = get_bloginfo('wpurl').'/res/'.IMMOCASTER_PERMALINK_PAGINATOR_PAGER.'/'.$post->ID.'/'.$i;
				}
				else
				{
					// Without permalinks
					$sLink = get_bloginfo('wpurl').'?';
					foreach($_GET as $key=>$value)
					{
						if($key!=IMMOCASTER_PAGINATOR_PAGER)
						{
							$sLink .= $key.'='.$value.'&';
						}
					}
					$sLink .= IMMOCASTER_PAGINATOR_PAGER."=".$i;
					$aPageArray[$i]['sUrl'] = $sLink;
				}
				
			}
			$aPageArray[$i]['sText'] = strval($i);
		}
		elseif($sSpacer!='')
		{
			$aPageArray[$i]['sText'] = $sSpacerText;
			$sSpacer = '';
		}
		$sClass = '';
		if($i==1){ $aPageArray[$i]['sClass'] = 'immocaster_pager_first'; }
		if($i==$iPages){ $aPageArray[$i]['sClass'] = 'immocaster_pager_last'; }
		if($i>=$iCurrentPage-$iLinkShown && $i<=$iCurrentPage+$iLinkShown){ $aPageArray[$i]['sClass'] = 'immocaster_pager_link'; }
		if($i==$iCurrentPage){ $aPageArray[$i]['sClass'] = 'immocaster_pager_current'; }
    }
    return $aPageArray;
}


// Google Maps
function icGoogleMaps($aGeo=array(),$sCity='',$aParameter=array())
{
	if(!isset($aParameter['zoom'])){ $aParameter['zoom']=14; }
	if(!isset($aParameter['width'])){ $aParameter['width']='100%'; }
	if(!isset($aParameter['height'])){ $aParameter['height']='300px'; }
	if(!isset($aGeo['latitude'])||!isset($aGeo['longitude'])||$aGeo['latitude']==''||$aGeo['longitude']=='')
	{
		if($sCity==''){ return false; }else{$bCityOnly=true;}
		$sUrl = 'http://maps.googleapis.com/maps/api/geocode/xml?address='.$sCity.',deutschland&sensor=false';
		$ch = curl_init($sUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode >= 400) 
		{ 
			return '';
		} 
		$doc = new SimpleXmlElement($data, LIBXML_NOCDATA);
		$aGeo = array(
			'latitude'=>(float)$doc->result->geometry->location->lat,
			'longitude'=>(float)$doc->result->geometry->location->lng
		);
	}
	$sRand = md5(rand(99999,99999999));
	$sCode  = '<style type="text/css">#map_canvas_'.$sRand.'{width:'.$aParameter['width'].';height:'.$aParameter['height'].';}</style>';
	$sCode .= '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=true"></script>';
	$sCode .= '<script type="text/javascript">';
	$sCode .= 'var map;';
	$sCode .= 'function initialize() {';
	$sCode .= 'var myOptions = {';
	$sCode .= 'zoom: '.$aParameter['zoom'].',';
	$sCode .= 'center: new google.maps.LatLng('.$aGeo['latitude'].','.$aGeo['longitude'].'),';
	$sCode .= 'mapTypeId: google.maps.MapTypeId.ROADMAP';
	$sCode .= '};';
	$sCode .= 'map = new google.maps.Map(document.getElementById(\'map_canvas_'.$sRand.'\'),myOptions);';
	if(!isset($bCityOnly))
	{
		$sCode .= 'var myLatlng = new google.maps.LatLng('.$aGeo['latitude'].','.$aGeo['longitude'].');';
		$sCode .= 'var marker = new google.maps.Marker({position: myLatlng, map: map});';
	}
	$sCode .= '}';
	$sCode .= 'google.maps.event.addDomListener(window,\'load\',initialize);';
	$sCode .= '</script>';
	$sCode .= '<div id="map_canvas_'.$sRand.'"></div>';
	return $sCode;
}