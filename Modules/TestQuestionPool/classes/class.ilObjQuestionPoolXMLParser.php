<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilObjQuestionPoolXMLParser extends ilSaxParser
{
	/**
	 * @var ilObjQuestionPool
	 */
	private $poolOBJ;
	
	private $curTag;
	
	private $inSettingsTag;

	/**
	 * @param ilObjQuestionPool $poolOBJ
	 * @param $xmlFile
	 */
	public function __construct(ilObjQuestionPool $poolOBJ, $xmlFile)
	{
		$this->poolOBJ = $poolOBJ;
		
		$this->inSettingsTag = false;
		
		return parent::ilSaxParser($xmlFile);
	}
	
	public function setHandlers($xmlParser)
	{
		xml_set_object($xmlParser,$this);
		xml_set_element_handler($xmlParser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($xmlParser,'handlerCharacterData');
	}
	
	public function handlerBeginTag($xmlParser, $tagName, $tagAttributes)
	{
		switch($tagName)
		{
			case 'Settings':
				$this->inSettingsTag = true;
				break;
			
			case 'ShowTaxonomies':
			case 'NavTaxonomy':
			case 'SkillService':
				if($this->inSettingsTag)
				{
					$this->cdata = '';
				}
				break;
		}
	}

	public function handlerEndTag($xmlParser, $tagName)
	{
		if(!$this->inSettingsTag)
		{
			return;
		}
		
		switch($tagName)
		{
			case 'Settings':
				$this->inSettingsTag = false;
				break;

			case 'ShowTaxonomies':
				$this->poolOBJ->setShowTaxonomies((bool)$this->cdata);
				$this->cdata = '';
				break;
				
			case 'NavTaxonomy':
				$this->poolOBJ->setNavTaxonomyId((int)$this->cdata);
				$this->cdata = '';
				break;
			
			case 'SkillService':
				$this->poolOBJ->setSkillServiceEnabled((bool)$this->cdata);
				$this->cdata = '';
				break;
		}
	}

	public function handlerCharacterData($xmlParser, $charData)
	{
		if(!$this->inSettingsTag)
		{
			return;
		}

		if( $charData != "\n" )
		{
			// Replace multiple tabs with one space
			$charData = preg_replace("/\t+/"," ",$charData);

			$this->cdata .= $charData;
		}
	}
}