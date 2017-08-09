<?php

// Object
function immocaster_theme_object($aVars)
{
	$sFile = 'standard';
	if(isset($aVars[0]['main']['themefile']))
	{
		$sFile = strtolower($aVars[0]['main']['themefile']);
	}
	require_once('object/'.$sFile.'.php');
	$sFunction = 'immocaster_theme_object_'.$sFile;
	return $sFunction($aVars);
}