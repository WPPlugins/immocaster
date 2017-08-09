<?php

if(isset($_GET['term']))
{
	$arr = array();
	$oImmocasterSDK = ImmocasterSDK::getInstance('is24ajax');
	$oRes = $oImmocasterSDK->_oImmocaster->getRegions(array('q'=>$_GET['term']));
	$oResult = ImmocasterSDK::parseXML((string)$oRes);
	foreach($oResult as $aRegion)
	{
		if($aRegion->geoCodeId)
		{
			$sOption = array('label'=>(string)$aRegion->name,'value'=>(string)$aRegion->name.' | ' . $aRegion->geoCodeId);
			array_push($arr,$sOption);
		}
	}
	echo json_encode($arr);
}

?>