<?php

// Resultlist
function immocaster_theme_resultlist($aVars)
{
	$sCode = '';
	// Paginator (Top)
	$sPaginatorCode = '<center>'.immocaster_theme_resultlist_paginator($aVars[0]['paging']['numberOfPages']).'</center>';
	if($aVars[0]['paging']['numberOfPages']>1){$sCode .= $sPaginatorCode;}
	// List
	$sCode .= '<div class="immocaster_resultlist_wrapper">';
	foreach($aVars[0]['list'] as $aEntry)
	{
		if(isset($aEntry['themefile']))
		{
			$sFile = strtolower($aEntry['themefile']);
		}
		else
		{
			$sFile = 'standard';	
		}
		require_once('resultlist/'.$sFile.'.php');
		$sFunction = 'immocaster_theme_resultlist_'.$sFile;
		$sCode .= $sFunction($aEntry);
	}
	$sCode .= '</div>';
	// Paginator (Bottom)
	if($aVars[0]['paging']['numberOfPages']>1){$sCode .= $sPaginatorCode;}
	// Output
	return $sCode;
}

// Paginator
function immocaster_theme_resultlist_paginator($iPages=1,$sSpacer='...')
{
	global $wp_query;
	if(isset($wp_query->query_vars[IMMOCASTER_PAGINATOR_PAGER]))
	{
		$iCurrentPage = (int)$wp_query->query_vars[IMMOCASTER_PAGINATOR_PAGER];
	}
	else
	{
		$iCurrentPage=1;
	}
	$sReturn = '<div class="immocaster_resultlist_paginator">';
	$aPages = icPaginator($iCurrentPage,$iPages,3,$sSpacer);
	foreach ($aPages as $aPage)
	{
		$sClass='immocaster_pager';
		if(isset($aPage['sClass'])){$sClass=$aPage['sClass'];}
		$sReturn .= '<span class="'.$sClass.'">';
		if (isset ($aPage['sUrl']))
		{
			$sReturn .= '<a href="'.$aPage['sUrl'].'">'.$aPage['sText'].'</a>';
		}else{
			$sReturn .= $aPage['sText'];
		}
		$sReturn .= '</span>';
	}
	$sReturn .= '</div>';
	return $sReturn;
}