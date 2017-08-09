<?php

add_action('admin_notices', 'immocaster_show_massages');

function immocaster_show_massages()
{
    $oMsg = ImmocasterMSG::getInstance();
	echo $oMsg->getMessages();
}

class ImmocasterMSG
{
	
	private $_aMessages = array();
	
	static private $instance = null; 
	static public function getInstance() 
	{ 
		if (!isset(self::$instance)) 
		{ 
			self::$instance = new self(); 
		} 
		return self::$instance; 
	}
	
	public function addMessage($sMsg,$bError=false)
	{
		array_push($this->_aMessages,array('msg'=>$sMsg,'error'=>$bError));
	}
	
	public function getMessages($sReturn='')
	{
		foreach($this->_aMessages as $aMessage)
		{
			if($aMessage['error'])
			{
				$sReturn .= '<div id="message" class="error">';
			}
			else
			{
				$sReturn .= '<div id="message" class="updated fade">';
			}
			$sReturn .= '<p><strong>'.$aMessage['msg'].'</strong></p></div>';
		}
		return $sReturn;
	}
	
	public function renderMessage($sMsg,$bError=false)
	{
		if($bError)
		{
			echo '<div id="message" class="error">';
		}
		else
		{
			echo '<div id="message" class="updated fade">';
		}
		echo '<p><strong>'.$sMsg.'</strong></p></div>';
	}
	
}