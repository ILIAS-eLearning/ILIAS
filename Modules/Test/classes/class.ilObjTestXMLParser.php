<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Xml/classes/class.ilSaxParser.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilObjTestXMLParser extends ilSaxParser
{
	protected $randomQuestionSelectionDefinitionMapping = array();

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
			case 'RandomQuestionSetConfig':
				$this->inRandomQuestionSetConfig = true;
				break;
			
			case 'RandomQuestionStage':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionStage = true;
				}
				break;

			case 'RandomQuestionStagingPool':
				if($this->inRandomQuestionSetConfig && $this->inRandomQuestionStage)
				{
					$this->cdata = '';
				}
				break;
			
			case 'RandomQuestionSelectionDefinitions':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionSelectionDefinitions = true;
				}
				break;

			case 'RandomQuestionSelectionDefinition':
				if($this->inRandomQuestionSetConfig && $this->inRandomQuestionSelectionDefinitions)
				{
					$this->cdata = '';
					$this->attr = $tagAttributes;
				}
				break;
		}
	}

	public function handlerEndTag($xmlParser, $tagName)
	{
		switch($tagName)
		{
			case 'RandomQuestionSetConfig':
				$this->inRandomQuestionSetConfig = false;
				break;

			case 'RandomQuestionStage':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionStage = false;
				}
				break;

			case 'RandomQuestionStagingPool':
				if($this->inRandomQuestionSetConfig && $this->inRandomQuestionStage)
				{
					// persist stage for pool
					$this->cdata = '';
				}
				break;

			case 'RandomQuestionSelectionDefinitions':
				if($this->inRandomQuestionSetConfig)
				{
					$this->inRandomQuestionSelectionDefinitions = false;
				}
				break;

			case 'RandomQuestionSelectionDefinition':
				if($this->inRandomQuestionSetConfig && $this->inRandomQuestionSelectionDefinitions)
				{
					$sourcePoolDefinition = new ilTestRandomQuestionSetSourcePoolDefinition();
					$sourcePoolDefinition->setPoolId((int)$this->attr['pool']);
					
					if( isset($this->attr['tax']) && isset($this->attr['taxNode']) )
					{
						$sourcePoolDefinition->setOriginalFilterTaxId((int)$this->attr['tax']);
						$sourcePoolDefinition->setOriginalFilterTaxNodeId((int)$this->attr['taxNode']);
					}

					$sourcePoolDefinition->saveToDb();
					
					$this->addRandomQuestionSelectionDefinitionMapping(
						(int)$this->attr['id'], $sourcePoolDefinition->getId()
					);
					
					$this->cdata = '';
				}
				break;
		}
	}

	public function handlerCharacterData($xmlParser, $charData)
	{
		if( $charData != "\n" )
		{
			// Replace multiple tabs with one space
			$charData = preg_replace("/\t+/"," ",$charData);

			$this->cdata .= $charData;
		}
	}

	protected function addRandomQuestionSelectionDefinitionMapping($oldId, $newId)
	{
		$this->randomQuestionSelectionDefinitionMapping[$oldId] = $newId;
	}

	protected function getRandomQuestionSelectionDefinitionMapping()
	{
		return $this->randomQuestionSelectionDefinitionMapping;
	}
}
