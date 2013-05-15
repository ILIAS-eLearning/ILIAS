<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateFilterPattern.php';

/**
 * Implementation of an include filter pattern for didactic template actions
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateIncludeFilterPattern extends ilDidacticTemplateFilterPattern
{

	/**
	 * Constructor
	 * @param int $a_pattern_id 
	 */
	public function __construct($a_pattern_id = 0)
	{
		parent::__construct($a_pattern_id);
		$this->setPatternType(self::PATTERN_INCLUDE);
	}

	/**
	 * Check if patttern matches
	 */
	public function valid($a_source)
	{
		$a_source = trim($a_source);
		
		switch($this->getPatternSubType())
		{
			case self::PATTERN_SUBTYPE_REGEX:
				$GLOBALS['ilLog']->write(__METHOD__.': Checking include pattern with '.$a_source.' against '.$this->getPattern());
				return preg_match('/'.$this->getPattern().'/',$a_source) === 1;
		}
		return false;
	}

	/**
	 * Write xml
	 * @param ilXmlWriter $writer
	 */
	public function toXml(ilXmlWriter $writer)
	{
		switch($this->getPatternSubType())
		{
			case ilDidacticTemplateFilterPattern::PATTERN_SUBTYPE_REGEX:
			default:

				$writer->xmlElement(
					'includePattern',
					array(
						'preg'	=> $this->getPattern()
					)
				);
		}
	}
}
?>