<?php

require_once(dirname(__FILE__).'/Immocaster/Sdk.php');

class ImmocasterSDK
{
	
	public  $_oImmocaster = null;
	private $_sServiceLinkURL = 'http://forward.immobilienscout24.de';
	private $_bExposeSupport = null;
	
	static private $_instances = array();
	static public function getInstance($sName)
	{
		if(!isset(self::$_instances[$sName]))
		{
			self::$_instances[$sName] = new self($sName);
		}
		return self::$_instances[$sName];
	}

	// Load immocaster
	public function __construct($sName)
	{
		$this->_oImmocaster = Immocaster_Sdk::getInstance($sName,get_option('is24_rest_key'),get_option('is24_rest_secret'));
		$aDatabase = array('mysql',DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
		$this->_oImmocaster->setDataStorage($aDatabase);
		$this->_oImmocaster->setRequestUrl('live');
		$this->_oImmocaster->setReadingType('curl');
	}
	
	// Check connection
	public function checkConnection()
	{
		$oRegion = $this->_oImmocaster->getRegions(array('q'=>'Ber'));
		if(substr_count((string)$oRegion,'ERROR_')>=1 || !$oRegion)
		{
			$this->_bExposeSupport = false;
			return false;
		}
		else
		{
			// Exposes activated by user (forced)
			if(get_option('immocaster_force_is24_expose','no')=='yes')
			{
				$this->_bExposeSupport = true;
				return true;
			}
			// Automatic check
			$req = $this->regionSearch(array(
				'geocoordinates'=>'52.52546480183439;13.369545936584473;999999999',
				'realestatetype'=>'apartmentrent'
			));
			if($req['paging']['numberOfHits']>=1)
			{
				$this->checkExposeSupport($req['list'][0]['id']);
			}
			else
			{
				$this->_bExposeSupport = false;
			}
			return true;
		}
	}
	
	// Get regions
	public function getRegions($sRegion)
	{
		return $this->_oImmocaster->getRegions(array('q'=>$sRegion));
	}
	
	// Get exposes by region-id or radius
	public function regionSearch($aParameter)
	{
		$aResult = array();
		// Recreate Parameters for Office
		if(strtolower($aParameter['realestatetype'])=='officebuy')
		{
			$aParameter['realestatetype'] = 'office';
			$aParameter['pricetype'] = 'buy';
		}
		if(strtolower($aParameter['realestatetype'])=='officerentmonth')
		{
			$aParameter['realestatetype'] = 'office';
			$aParameter['pricetype'] = 'rentpermonth';
		}
		if(strtolower($aParameter['realestatetype'])=='officerentsqm')
		{
			$aParameter['realestatetype'] = 'office';
			$aParameter['pricetype'] = 'rentpersqm';
		}
		// Recreate Parameters for Store
		if(strtolower($aParameter['realestatetype'])=='storebuy')
		{
			$aParameter['realestatetype'] = 'store';
			$aParameter['pricetype'] = 'buy';
		}
		if(strtolower($aParameter['realestatetype'])=='storerentmonth')
		{
			$aParameter['realestatetype'] = 'store';
			$aParameter['pricetype'] = 'rentpermonth';
		}
		if(strtolower($aParameter['realestatetype'])=='storerentsqm')
		{
			$aParameter['realestatetype'] = 'store';
			$aParameter['pricetype'] = 'rentpersqm';
		}
		// Get Exposes
		if(isset($aParameter['geocoordinates']))
		{
			// Radius
			/* SOF Radius FIX => Da IS24 die Radiussuche geändert hat */
			// $oResult = $this->parseXML($this->_oImmocaster->radiusSearch($aParameter));
			$aParameter['geocodes']='1';
			$oResult = $this->parseXML($this->_oImmocaster->regionSearch($aParameter));
			/* EOF Radius FIX */
		}
		else
		{
			// Region
			$oResult = $this->parseXML($this->_oImmocaster->regionSearch($aParameter));
		}
		// Paging
		$aResult['paging'] = array(
			'pageNumber' => (int)$oResult->paging->pageNumber,
			'numberOfHits' => (int)$oResult->paging->numberOfHits,
			'pageSize' => (int)$oResult->paging->pageSize,
			'numberOfPages' => (int)$oResult->paging->numberOfPages
		);
		// Get structured exposelist: Standard
		if(
			strtolower($aParameter['realestatetype']) == 'apartmentrent' ||
			strtolower($aParameter['realestatetype']) == 'apartmentbuy' ||
			strtolower($aParameter['realestatetype']) == 'houserent' ||
			strtolower($aParameter['realestatetype']) == 'housebuy'
		)
		{
			$aResult['list'] = $this->structureStandard($oResult,array());
		}
		// Get structured exposelist: Office
		if(strtolower($aParameter['realestatetype']) == 'office')
		{
			$aResult['list'] = $this->structureOffice($oResult,array('subtype'=>$aParameter['pricetype']));
			
		}
		// Get structured exposelist: Store
		if(strtolower($aParameter['realestatetype']) == 'store')
		{
			$aResult['list'] = $this->structureStore($oResult,array('subtype'=>$aParameter['pricetype']));
			
		}
		return $aResult;
	}
	
	// Structure standard object (house,apartment)
	private function structureStandard($oResult,$aVars=array())
	{
		$aResult = array();
		if(is_object($oResult->resultlistEntries->resultlistEntry))
		{
			foreach($oResult->resultlistEntries->resultlistEntry as $oEntry)
			{
				global $base_root,$base_path;
				$aEntry = array('address'=>array(),'media'=>array(),'price'=>array());
				$aEntry['id'] = (int)$oEntry->realEstateId;
				$aEntry['themefile'] = 'standard';
				$aEntry['title'] = (string)$oEntry->ic_estate->title;
				$aEntry['main']['link'] = $this->getLink('intern',(string)$oEntry->realEstateId);
				$aEntry['address']['street'] = (string)$oEntry->ic_estate->address->street;
				$aEntry['address']['houseNumber'] = (int)$oEntry->ic_estate->address->houseNumber;
				$aEntry['address']['postcode'] = (int)$oEntry->ic_estate->address->postcode;
				$aEntry['address']['city'] = (string)$oEntry->ic_estate->address->city;
				if(isset($oEntry->ic_estate->address->quarter))
				{
					$aEntry['address']['quarter'] = (string)$oEntry->ic_estate->address->quarter;
				}
				$aEntry['livingSpace'] = (float)$oEntry->ic_estate->livingSpace;
				$aEntry['numberOfRooms'] = (int)$oEntry->ic_estate->numberOfRooms;
				$aEntry['media']['mainPicture'] = plugins_url('immocaster/images/house_118x118.png');
				if($oEntry->ic_estate->titlePicture)
				{
					$aEntry['media']['mainPicture'] = (string)$oEntry->ic_estate->titlePicture->urls->url->attributes()->href;
				}
				$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->price->value;
				$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->price->currency;
				$aEntry['price']['main']['type'] = (string)$oEntry->ic_estate->price->marketingType;
				foreach($aVars as $key=>$value)
				{
					$aEntry[$key] = $value;
				}
				array_push($aResult,$aEntry);
			}
		}
		return $aResult;
	}
	
	// Structure office object
	private function structureOffice($oResult,$aVars=array())
	{
		$aResult = array();
		if(is_object($oResult->resultlistEntries->resultlistEntry))
		{
			foreach($oResult->resultlistEntries->resultlistEntry as $oEntry)
			{
				global $base_root,$base_path;
				$aEntry = array('address'=>array(),'media'=>array(),'price'=>array());
				$aEntry['id'] = (int)$oEntry->realEstateId;
				$aEntry['themefile'] = 'office';
				$aEntry['title'] = (string)$oEntry->ic_estate->title;
				$aEntry['main']['link'] = $this->getLink('intern',(string)$oEntry->realEstateId);
				$aEntry['address']['street'] = (string)$oEntry->ic_estate->address->street;
				$aEntry['address']['houseNumber'] = (int)$oEntry->ic_estate->address->houseNumber;
				$aEntry['address']['postcode'] = (int)$oEntry->ic_estate->address->postcode;
				$aEntry['address']['city'] = (string)$oEntry->ic_estate->address->city;
				if(isset($oEntry->ic_estate->address->quarter))
				{
					$aEntry['address']['quarter'] = (string)$oEntry->ic_estate->address->quarter;
				}
				$aEntry['officeSpace'] = (float)$oEntry->ic_estate->netFloorSpace;
				$aEntry['media']['mainPicture'] = plugins_url('immocaster/images/house_118x118.png');
				if($oEntry->ic_estate->titlePicture)
				{
					$aEntry['media']['mainPicture'] = (string)$oEntry->ic_estate->titlePicture->urls->url->attributes()->href;
				}
				$aEntry['price']['main']['commercializationType'] = (string)$oEntry->ic_estate->commercializationType;
				if(strtolower($aEntry['price']['main']['commercializationType'])=='buy')
				{
					$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->price->value;
					$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->price->currency;
				}
				else
				{
					if((float)$oEntry->ic_estate->calculatedPrice->value>(float)$oEntry->ic_estate->price->value)
					{
						$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->calculatedPrice->value;
						$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->calculatedPrice->currency;
						$aEntry['price']['sqm']['value'] = (float)$oEntry->ic_estate->price->value;
						$aEntry['price']['sqm']['currency'] = (string)$oEntry->ic_estate->price->currency;
					}
					else
					{
						$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->price->value;
						$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->calculatedPrice->currency;
						$aEntry['price']['sqm']['value'] = (float)$oEntry->ic_estate->calculatedPrice->value;
						$aEntry['price']['sqm']['currency'] = (string)$oEntry->ic_estate->price->currency;
					}
				}
				$aEntry['price']['courtage']['hasCourtage'] = (string)$oEntry->ic_estate->courtage->hasCourtage;
				foreach($aVars as $key=>$value)
				{
					$aEntry[$key] = $value;
				}
				array_push($aResult,$aEntry);
			}
		}
		return $aResult;
	}

	// Structure store object
	private function structureStore($oResult,$aVars=array())
	{
		$aResult = array();
		if(is_object($oResult->resultlistEntries->resultlistEntry))
		{
			foreach($oResult->resultlistEntries->resultlistEntry as $oEntry)
			{
				global $base_root,$base_path;
				$aEntry = array('address'=>array(),'media'=>array(),'price'=>array());
				$aEntry['id'] = (int)$oEntry->realEstateId;
				$aEntry['themefile'] = 'store';
				$aEntry['title'] = (string)$oEntry->ic_estate->title;
				$aEntry['main']['link'] = $this->getLink('intern',(string)$oEntry->realEstateId);
				$aEntry['address']['street'] = (string)$oEntry->ic_estate->address->street;
				$aEntry['address']['houseNumber'] = (int)$oEntry->ic_estate->address->houseNumber;
				$aEntry['address']['postcode'] = (int)$oEntry->ic_estate->address->postcode;
				$aEntry['address']['city'] = (string)$oEntry->ic_estate->address->city;
				if(isset($oEntry->ic_estate->address->quarter))
				{
					$aEntry['address']['quarter'] = (string)$oEntry->ic_estate->address->quarter;
				}
				$aEntry['storeSpace'] = (float)$oEntry->ic_estate->netFloorSpace;
				$aEntry['media']['mainPicture'] = plugins_url('immocaster/images/house_118x118.png');
				if($oEntry->ic_estate->titlePicture)
				{
					$aEntry['media']['mainPicture'] = (string)$oEntry->ic_estate->titlePicture->urls->url->attributes()->href;
				}
				$aEntry['price']['main']['commercializationType'] = (string)$oEntry->ic_estate->commercializationType;
				if(strtolower($aEntry['price']['main']['commercializationType'])=='buy')
				{
					$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->price->value;
					$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->price->currency;
				}
				else
				{
					if((float)$oEntry->ic_estate->calculatedPrice->value>(float)$oEntry->ic_estate->price->value)
					{
						$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->calculatedPrice->value;
						$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->calculatedPrice->currency;
						$aEntry['price']['sqm']['value'] = (float)$oEntry->ic_estate->price->value;
						$aEntry['price']['sqm']['currency'] = (string)$oEntry->ic_estate->price->currency;
					}
					else
					{
						$aEntry['price']['main']['value'] = (float)$oEntry->ic_estate->price->value;
						$aEntry['price']['main']['currency'] = (string)$oEntry->ic_estate->calculatedPrice->currency;
						$aEntry['price']['sqm']['value'] = (float)$oEntry->ic_estate->calculatedPrice->value;
						$aEntry['price']['sqm']['currency'] = (string)$oEntry->ic_estate->price->currency;
					}
					
				}
				$aEntry['price']['courtage']['hasCourtage'] = (string)$oEntry->ic_estate->courtage->hasCourtage;
				foreach($aVars as $key=>$value)
				{
					$aEntry[$key] = $value;
				}
				array_push($aResult,$aEntry);
			}
		}
		return $aResult;
	}

	// Get expose by id
	public function getExpose($aParameter)
	{
		$oResult = $this->_oImmocaster->getExpose($aParameter);
		$oXml = $this->parseXML($oResult);
		if(substr_count($oXml->message->messageCode,'ERROR')>=1)
		{
			return false;
		}
		if(isset($oXml->realEstate->officeType))
		{
			$sThemeFile = 'office';
			return $this->structureExposeOffice($oXml);
		}
		if(isset($oXml->realEstate->storeType))
		{
			$sThemeFile = 'store';
			return $this->structureExposeStore($oXml);
		}
		$sThemeFile = 'standard';
		return $this->structureExposeStandard($oXml);
	}
	
	// Expose Structure: standard
	private function structureExposeStandard($oXml,$aVars=array())
	{
		if(strtolower($oXml->realEstate->courtage->hasCourtage)=='yes'){$iCourtage=1;}else{$iCourtage=0;}
		if($iCourtage==1){$sCourtage=(string)$oXml->realEstate->courtage->courtage;}else{$sCourtage=false;}
		$aReturn = array(
			'main' => array(
				'id' => (string)$oXml->attributes()->id,
				'themefile' => 'standard',
				'title' => (string)$oXml->realEstate->title,
				'numberOfRooms' => (string)$oXml->realEstate->numberOfRooms,
				'floor' => (string)$oXml->realEstate->floor,
				'numberOfFloors' => (string)$oXml->realEstate->numberOfFloors,
				'livingSpace' => (string)$oXml->realEstate->livingSpace,
				'usableFloorSpace' => (string)$oXml->realEstate->usableFloorSpace,
				'apartmentType' => (string)$oXml->realEstate->apartmentType,
				'buildingType' => (string)$oXml->realEstate->buildingType,
				'condition' => (string)$oXml->realEstate->condition,
				'interiorQuality' => (string)$oXml->realEstate->interiorQuality,
				'heatingType' => (string)$oXml->realEstate->heatingType,
				'freeFrom' => (string)$oXml->realEstate->freeFrom,
				'link' => $this->getLink('extern',(string)$oXml->attributes()->id)
				),
			'address' => array(
				'street' => (string)$oXml->realEstate->address->street,
				'houseNumber' => (string)$oXml->realEstate->address->houseNumber,
				'postcode' => (string)$oXml->realEstate->address->postcode,
				'city' => (string)$oXml->realEstate->address->city,
				'quarter' => (string)$oXml->realEstate->address->quarter
				),
			'geo' => array(
				'latitude' => (string)$oXml->realEstate->address->wgs84Coordinate->latitude,
				'longitude' => (string)$oXml->realEstate->address->wgs84Coordinate->longitude
				),
			'costs' => array(
				'buyPrice' => (string)$oXml->realEstate->price->value,
				'baseRent' => (string)$oXml->realEstate->baseRent,
				'totalRent' => (string)$oXml->realEstate->totalRent,
				'deposit' => (string)$oXml->realEstate->deposit,
				'courtage' => $sCourtage,
				),
			'notes' => array(
				'desc' => (string)$oXml->realEstate->descriptionNote,
				'furnishing' => (string)$oXml->realEstate->furnishingNote,
				'location' => (string)$oXml->realEstate->locationNote,
				'other' => (string)$oXml->realEstate->otherNote
				)
			);
			// Contact details
			if(isset($oXml->contactDetails))
			{
				$aReturn['contact']['company'] = (string)$oXml->contactDetails->company;
				$aReturn['contact']['homepage'] = (string)$oXml->contactDetails->homepageUrl;
				$aReturn['contact']['logo'] = (string)$oXml->contactDetails->realtorLogo;
				$aReturn['contact']['gender'] = strtolower((string)$oXml->contactDetails->salutation);
				$aReturn['contact']['firstname'] = (string)$oXml->contactDetails->firstname;
				$aReturn['contact']['lastname'] = (string)$oXml->contactDetails->lastname;
				$aReturn['contact']['phone'] = (string)$oXml->contactDetails->phoneNumber;
				$aReturn['contact']['mobile'] = (string)$oXml->contactDetails->cellPhoneNumber;
				$aReturn['contact']['fax'] = (string)$oXml->contactDetails->faxNumber;
				$aReturn['contact']['address']['street'] = (string)$oXml->contactDetails->address->street;
				$aReturn['contact']['address']['houseNumber'] = (string)$oXml->contactDetails->address->houseNumber;
				$aReturn['contact']['address']['zip'] = (string)$oXml->contactDetails->address->postcode;
				$aReturn['contact']['address']['city'] = (string)$oXml->contactDetails->address->city;
				$aReturn['contact']['address']['countryCode'] = (string)$oXml->contactDetails->countryCode;
			}
			// Extras
			if(strtolower($oXml->realEstate->cellar)=='yes'){$iCellar=1;}else{$iCellar=0;}
			if(strtolower($oXml->realEstate->balcony)=='true'){$iBalcony=1;}else{$iBalcony=0;}
			if(strtolower($oXml->realEstate->lift)=='true'){$iLift=1;}else{$iLift=0;}
			if(strtolower($oXml->realEstate->garden)=='true'){$iGarden=1;}else{$iGarden=0;}
			if(strtolower($oXml->realEstate->petsAllowed)==true){$sPetsAllowed=$oXml->realEstate->petsAllowed;}else{$sPetsAllowed=0;}
			if(strtolower($oXml->realEstate->builtInKitchen)=='true'){$iBuildInKitchen=1;}else{$iBuildInKitchen=0;}
			if(strtolower($oXml->realEstate->handicappedAccessible)=='yes'){$iHandicappedAccessible=1;}else{$iHandicappedAccessible=0;}
			if(strtolower($oXml->realEstate->guestToilet)=='yes'){$iGuestToilet=1;}else{$iGuestToilet=0;}
			$aReturn['extras'] = array(
				'cellar' => $iCellar,
				'balcony' => $iBalcony,
				'lift' => $iLift,
				'garden' => $iGarden,
				'petsAllowed' => $sPetsAllowed,
				'builtInKitchen' => $iBuildInKitchen,
				'handicappedAccessible' => $iHandicappedAccessible,
				'guestToilet' => $iGuestToilet,
			);
			$aReturn['media']['pictures'] = $this->getImages($oXml->realEstate->attachments);
			return $aReturn;
	}
	
	// Expose Structure: office
	private function structureExposeOffice($oXml,$aVars=array())
	{
		if(strtolower($oXml->realEstate->courtage->hasCourtage)=='yes'){$iCourtage=1;}else{$iCourtage=0;}
		if($iCourtage==1){$sCourtage=(string)$oXml->realEstate->courtage->courtage;}else{$sCourtage=false;}
		$aReturn = array(
			'main' => array(
				'id' => (string)$oXml->attributes()->id,
				'themefile' => 'office',
				'title' => (string)$oXml->realEstate->title,
				'link' => $this->getLink('extern',(string)$oXml->attributes()->id),
				'flooringType' => $oXml->realEstate->flooringType,
				'commercializationType' => strtolower((string)$oXml->realEstate->commercializationType),
				'heatingType' => (string)$oXml->realEstate->heatingType,
				'numberOfFloors' => (string)$oXml->realEstate->numberOfFloors,
				'netFloorSpace' => (string)$oXml->realEstate->netFloorSpace
				),
			'address' => array(
				'street' => (string)$oXml->realEstate->address->street,
				'houseNumber' => (string)$oXml->realEstate->address->houseNumber,
				'postcode' => (string)$oXml->realEstate->address->postcode,
				'city' => (string)$oXml->realEstate->address->city,
				'quarter' => (string)$oXml->realEstate->address->quarter
				),
			'geo' => array(
				'latitude' => (string)$oXml->realEstate->address->wgs84Coordinate->latitude,
				'longitude' => (string)$oXml->realEstate->address->wgs84Coordinate->longitude
				),
			'notes' => array(
				'free' => (string)$oXml->realEstate->freeForm,
				'desc' => (string)$oXml->realEstate->descriptionNote,
				'location' => (string)$oXml->realEstate->locationNote,
				'other' => (string)$oXml->realEstate->otherNote
				)
			);
			if($aReturn['main']['commercializationType']=='buy')
			{
				$aReturn['price']['main'] = array(
				'value' => (string)$oXml->realEstate->price->value,
				'currency' => $oXml->realEstate->price->currency
				);
			}
			if($aReturn['main']['commercializationType']=='rent')
			{
				$aReturn['price']['main'] = array(
				'value' => (string)$oXml->realEstate->calculatedPrice->value,
				'currency' => $oXml->realEstate->calculatedPrice->currency
				);
				$aReturn['price']['sqm'] = array(
				'value' => (string)$oXml->realEstate->price->value,
				'currency' => $oXml->realEstate->price->currency
				);
				if(isset($oXml->realEstate->additionalCosts))
				{
					$aReturn['price']['sqm']['additional'] = $oXml->realEstate->additionalCosts->value;
				}
			}
			if(isset($oEntry->ic_estate->courtage->hasCourtage))
			{
				$aEntry['price']['courtage']['hasCourtage'] = (string)$oEntry->ic_estate->courtage->hasCourtage;
			}
			// Contact details
			if(isset($oXml->contactDetails))
			{
				$aReturn['contact']['company'] = (string)$oXml->contactDetails->company;
				$aReturn['contact']['homepage'] = (string)$oXml->contactDetails->homepageUrl;
				$aReturn['contact']['logo'] = (string)$oXml->contactDetails->realtorLogo;
				$aReturn['contact']['gender'] = strtolower((string)$oXml->contactDetails->salutation);
				$aReturn['contact']['firstname'] = (string)$oXml->contactDetails->firstname;
				$aReturn['contact']['lastname'] = (string)$oXml->contactDetails->lastname;
				$aReturn['contact']['phone'] = (string)$oXml->contactDetails->phoneNumber;
				$aReturn['contact']['mobile'] = (string)$oXml->contactDetails->cellPhoneNumber;
				$aReturn['contact']['fax'] = (string)$oXml->contactDetails->faxNumber;
				$aReturn['contact']['address']['street'] = (string)$oXml->contactDetails->address->street;
				$aReturn['contact']['address']['houseNumber'] = (string)$oXml->contactDetails->address->houseNumber;
				$aReturn['contact']['address']['zip'] = (string)$oXml->contactDetails->address->postcode;
				$aReturn['contact']['address']['city'] = (string)$oXml->contactDetails->address->city;
				$aReturn['contact']['address']['countryCode'] = (string)$oXml->contactDetails->countryCode;
			}
			// Extras
			$aReturn['extras'] = array();
			if(strtolower($oXml->realEstate->kitchenComplete)=='yes'){$iKitchenComplete=1;}else{$iKitchenComplete=0;}
			$aReturn['extras']['kitchenComplete'] = $iKitchenComplete;
			if(strtolower($oXml->realEstate->lift)=='yes'){$iLift=1;}else{$iLift=0;}
			$aReturn['extras']['lift'] = $iLift;
			if(strtolower($oXml->realEstate->cellar)=='yes'){$iCellar=1;}else{$iCellar=0;}
			$aReturn['extras']['cellar'] = $iCellar;
			if(strtolower($oXml->realEstate->handicappedAccessible)=='yes'){$iHandicappedAccessible=1;}else{$iHandicappedAccessible=0;}
			$aReturn['extras']['handicappedAccessible'] = $iHandicappedAccessible;
			if(strtolower($oXml->realEstate->hasCanteen)=='yes'){$iHasCanteen=1;}else{$iHasCanteen=0;}
			$aReturn['extras']['hasCanteen'] = $iHasCanteen;
			if(strtolower($oXml->realEstate->lanCables)=='yes'){$iLanCables=1;}else{$iLanCables=0;}
			$aReturn['extras']['lanCables'] = $iLanCables;
			if(strtolower($oXml->realEstate->highVoltage)=='yes'){$iHighVoltage=1;}else{$iHighVoltage=0;}
			$aReturn['extras']['highVoltage'] = $iHighVoltage;
			$aReturn['extras']['airConditioning'] = (string)$oXml->realEstate->airConditioning;
			$aReturn['extras']['condition'] = (string)$oXml->realEstate->condition;
			// Images
			$aReturn['media']['pictures'] = $this->getImages($oXml->realEstate->attachments);
			return $aReturn;
	}
	
	// Expose Structure: store
	private function structureExposeStore($oXml,$aVars=array())
	{
		if(strtolower($oXml->realEstate->courtage->hasCourtage)=='yes'){$iCourtage=1;}else{$iCourtage=0;}
		if($iCourtage==1){$sCourtage=(string)$oXml->realEstate->courtage->courtage;}else{$sCourtage=false;}
		$aReturn = array(
			'main' => array(
				'id' => (string)$oXml->attributes()->id,
				'themefile' => 'store',
				'title' => (string)$oXml->realEstate->title,
				'link' => $this->getLink('extern',(string)$oXml->attributes()->id),
				'flooringType' => $oXml->realEstate->flooringType,
				'commercializationType' => strtolower((string)$oXml->realEstate->commercializationType),
				'heatingType' => (string)$oXml->realEstate->heatingType,
				'numberOfFloors' => (string)$oXml->realEstate->numberOfFloors,
				'netFloorSpace' => (string)$oXml->realEstate->netFloorSpace
				),
			'address' => array(
				'street' => (string)$oXml->realEstate->address->street,
				'houseNumber' => (string)$oXml->realEstate->address->houseNumber,
				'postcode' => (string)$oXml->realEstate->address->postcode,
				'city' => (string)$oXml->realEstate->address->city,
				'quarter' => (string)$oXml->realEstate->address->quarter
				),
			'geo' => array(
				'latitude' => (string)$oXml->realEstate->address->wgs84Coordinate->latitude,
				'longitude' => (string)$oXml->realEstate->address->wgs84Coordinate->longitude
				),
			'notes' => array(
				'free' => (string)$oXml->realEstate->freeForm,
				'desc' => (string)$oXml->realEstate->descriptionNote,
				'location' => (string)$oXml->realEstate->locationNote,
				'other' => (string)$oXml->realEstate->otherNote
				)
			);
			if($aReturn['main']['commercializationType']=='buy')
			{
				$aReturn['price']['main'] = array(
				'value' => (string)$oXml->realEstate->price->value,
				'currency' => $oXml->realEstate->price->currency
				);
			}
			if($aReturn['main']['commercializationType']=='rent')
			{
				$aReturn['price']['main'] = array(
				'value' => (string)$oXml->realEstate->calculatedPrice->value,
				'currency' => $oXml->realEstate->calculatedPrice->currency
				);
				$aReturn['price']['sqm'] = array(
				'value' => (string)$oXml->realEstate->price->value,
				'currency' => $oXml->realEstate->price->currency
				);
				if(isset($oXml->realEstate->additionalCosts))
				{
					$aReturn['price']['sqm']['additional'] = $oXml->realEstate->additionalCosts->value;
				}
			}
			if(isset($oEntry->ic_estate->courtage->hasCourtage))
			{
				$aEntry['price']['courtage']['hasCourtage'] = (string)$oEntry->ic_estate->courtage->hasCourtage;
			}
			// Contact details
			if(isset($oXml->contactDetails))
			{
				$aReturn['contact']['company'] = (string)$oXml->contactDetails->company;
				$aReturn['contact']['homepage'] = (string)$oXml->contactDetails->homepageUrl;
				$aReturn['contact']['logo'] = (string)$oXml->contactDetails->realtorLogo;
				$aReturn['contact']['gender'] = strtolower((string)$oXml->contactDetails->salutation);
				$aReturn['contact']['firstname'] = (string)$oXml->contactDetails->firstname;
				$aReturn['contact']['lastname'] = (string)$oXml->contactDetails->lastname;
				$aReturn['contact']['phone'] = (string)$oXml->contactDetails->phoneNumber;
				$aReturn['contact']['mobile'] = (string)$oXml->contactDetails->cellPhoneNumber;
				$aReturn['contact']['fax'] = (string)$oXml->contactDetails->faxNumber;
				$aReturn['contact']['address']['street'] = (string)$oXml->contactDetails->address->street;
				$aReturn['contact']['address']['houseNumber'] = (string)$oXml->contactDetails->address->houseNumber;
				$aReturn['contact']['address']['zip'] = (string)$oXml->contactDetails->address->postcode;
				$aReturn['contact']['address']['city'] = (string)$oXml->contactDetails->address->city;
				$aReturn['contact']['address']['countryCode'] = (string)$oXml->contactDetails->countryCode;
			}
			// Extras
			$aReturn['extras'] = array();
			if(strtolower($oXml->realEstate->kitchenComplete)=='yes'){$iKitchenComplete=1;}else{$iKitchenComplete=0;}
			$aReturn['extras']['kitchenComplete'] = $iKitchenComplete;
			if(strtolower($oXml->realEstate->lift)=='yes'){$iLift=1;}else{$iLift=0;}
			$aReturn['extras']['lift'] = $iLift;
			if(strtolower($oXml->realEstate->cellar)=='yes'){$iCellar=1;}else{$iCellar=0;}
			$aReturn['extras']['cellar'] = $iCellar;
			if(strtolower($oXml->realEstate->handicappedAccessible)=='yes'){$iHandicappedAccessible=1;}else{$iHandicappedAccessible=0;}
			$aReturn['extras']['handicappedAccessible'] = $iHandicappedAccessible;
			if(strtolower($oXml->realEstate->hasCanteen)=='yes'){$iHasCanteen=1;}else{$iHasCanteen=0;}
			$aReturn['extras']['hasCanteen'] = $iHasCanteen;
			if(strtolower($oXml->realEstate->lanCables)=='yes'){$iLanCables=1;}else{$iLanCables=0;}
			$aReturn['extras']['lanCables'] = $iLanCables;
			if(strtolower($oXml->realEstate->highVoltage)=='yes'){$iHighVoltage=1;}else{$iHighVoltage=0;}
			$aReturn['extras']['highVoltage'] = $iHighVoltage;
			$aReturn['extras']['airConditioning'] = (string)$oXml->realEstate->airConditioning;
			$aReturn['extras']['condition'] = (string)$oXml->realEstate->condition;
			// Images
			$aReturn['media']['pictures'] = $this->getImages($oXml->realEstate->attachments);
			return $aReturn;
	}
	
	// Parse xml
	static public function parseXML($string='')
	{
		try
		{
			$string = str_replace('resultlist:resultlist','ic_resultlist',$string);
			$string = str_replace('resultlist:realEstate','ic_estate',$string);
			if(substr($string,0,5)=='<?xml')
			{
				$xml=simplexml_load_string($string);
				return $xml;
			}
			throw new Exception($string);
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}
	
	// Shorten resultlist
	public function shortenList($aList,$iObjects=10)
	{
		$iCount = 0;
		$aResults = array();
		foreach($aList as $entry)
		{
			if($iCount>=$iObjects)
			{
				continue;
			}
			else
			{
				array_push($aResults,$entry);
				$iCount++;
			}
		}
		$aList = $aResults;
		return $aList;
	}
	
	// Check expose support
	private function checkExposeSupport($iId)
	{
		if($this->_bExposeSupport===null)
		{
			$req = $this->getExpose(array('exposeid'=>$iId));
			if($req)
			{
				$this->_bExposeSupport = true;
			}
			else
			{
				$this->_bExposeSupport = false;
			}
			
		}
		return $this->_bExposeSupport;
	}
	
	// Get expose support
	public function getExposeSupport()
	{
		return $this->_bExposeSupport;
	}
	
	// Get link to object
	private function getLink($sType='extern',$iId)
	{
		if($sType=='extern' || $this->_bExposeSupport===false)
		{
			$sAffiliateRef = get_option('is24_affiliate_referrer','5504WDP');
			if($sAffiliateRef==''){ $sAffiliateRef = '5504WDP'; }
			$sLink = base64_encode($this->_sServiceLinkURL.'/'.$sAffiliateRef.'/'.$iId);
			return get_bloginfo('wpurl').'?'.IMMOCASTER_GET_PARAM_REDIRECT.'='.$sLink;
		}
		if($sType=='intern')
		{
			// Check expose support
			if($this->checkExposeSupport($iId)===false)
			{
				return $this->getLink('extern',$iId);
			}
			// With permalinks
			if(get_option('permalink_structure'))
			{
				$iPage = 0;
				global $post;
				if(isset($post->ID)){ $iPage = $post->ID; }
				return get_bloginfo('wpurl').'/'.$iPage.'/'.IMMOCASTER_PERMALINK_OBJECT.'/'.$iId;
			}
			// Without permalinks
			$sLink = get_bloginfo('wpurl').'?';
			foreach($_GET as $key=>$value)
			{
				if($key!=IMMOCASTER_POST_TYPE_NAME && $key!=IMMOCASTER_GET_PARAM_EXPOSE)
				{
					$sLink .= $key.'='.$value.'&';
				}
			}
			$sLink .= IMMOCASTER_POST_TYPE_NAME.'=1&'.IMMOCASTER_GET_PARAM_EXPOSE.'='.$iId;
			return $sLink;
		}
	}
	
	// Get Images
	private function getImages($oAttachments,$aReturn=array())
	{
		if(!is_object($oAttachments->attachment))
		{
			return array(array('title'=>'','floorplan'=>false,'pictures'=>array(
				array('href'=>plugins_url('immocaster/images/house_118x118.png')),
				array('href'=>plugins_url('immocaster/images/house_118x118.png')),
				array('href'=>plugins_url('immocaster/images/house_240x140.png')),
				array('href'=>plugins_url('immocaster/images/house_240x140.png'))
			)));
		}
		foreach($oAttachments->attachment as $oAttachment)
		{
			$aPictures=array();
			if(isset($oAttachment->urls))
			{
				foreach($oAttachment->urls->url as $aMedia)
				{
					array_push($aPictures,array('href'=>(string)$aMedia->attributes()->href));
				}
				array_push($aReturn,array(
					'title' => (string)$oAttachment->title,
					'floorplan' => (string)$oAttachment->floorplan,
					'pictures' => $aPictures
				));
			}
		}
		return $aReturn;
	}

}