<?php

// Teaser
function immocaster_theme_teaser($aVars)
{
	$sFile = 'standard';
	require_once('teaser/'.$sFile.'.php');
	$sFunction = 'immocaster_theme_teaser_'.$sFile;
	return $sFunction($aVars);;
}