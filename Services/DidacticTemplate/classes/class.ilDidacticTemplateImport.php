<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/exceptions/class.ilDidacticTemplateImportException.php';

/**
 * Description of ilDidacticTemplateImport
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateImport
{
    const IMPORT_FILE = 1;

	private $type = 0;
	private $xmlfile = '';


	/**
	 * Constructor
	 * @param <type> $a_type
	 */
	public function __construct($a_type)
	{
		$this->type = $a_type;
	}

	/**
	 * Set input file
	 * @param string $a_file
	 */
	public function setInputFile($a_file)
	{
		$this->xmlfile = $a_file;
	}

	/**
	 * Get inputfile
	 * @return <type>
	 */
	public function getInputFile()
	{
		return $this->xmlfile;
	}

	/**
	 * Get input type
	 * @return string
	 */
	public function getInputType()
	{
		return $this->type;
	}

	/**
	 * Do import
	 */
	public function import()
	{
		libxml_use_internal_errors(true);

		switch($this->getInputType())
		{
			case self::IMPORT_FILE:

				$root = simplexml_load_file($this->getInputFile());
				if($root == FALSE)
				{
					throw new ilDidacticTemplateImportException(
						$this->parseXmlErrors()
					);
				}
				break;
		}

		$settings = $this->parseSettings($root);
		$this->parseActions($settings,$root->didacticTemplate->actions);

	}

	/**
	 * Parse settings
	 * @param SimpleXMLElement $el
	 * @return ilDidacticTemplateSetting
	 */
	protected function parseSettings(SimpleXMLElement $root)
	{

		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';
		$setting = new ilDidacticTemplateSetting();

		foreach($root->didacticTemplate as $tpl)
		{
			switch((string) $tpl->attributes()->type)
			{
				case 'creation':
				default:
					$setting->setType(ilDidacticTemplateSetting::TYPE_CREATION);
					break;
			}
			$setting->setTitle(trim((string) $tpl->title));
			$setting->setDescription(trim((string) $tpl->description));

			foreach($tpl->assignments->assignment as $element)
			{
				$setting->addAssignment(trim((string) $element));
			}
		}
		$setting->save();
		return $setting;
	}

	/**
	 * Parse template action from xml
	 * @param ilDidacticTemplateSetting $set
	 * @param SimpleXMLElement $root
	 * @return void
	 */
	protected function parseActions(ilDidacticTemplateSetting $set, SimpleXMLElement $actions = NULL)
	{
		include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateActionFactory.php';

		if($actions === NULL)
		{
			return void;
		}


		foreach($actions->action as $ele)
		{
			$act = ilDidacticTemplateActionFactory::factoryByTypeString((string) $ele->attributes()->type);
			$act->setTemplateId($set->getId());

			if($act instanceof ilDidacticTemplateLocalPolicyAction)
			{
				// Filter type
				foreach($ele->filter as $fis)
				{
					switch((string) $fis->attributes()->type)
					{
						case ilDidacticTemplateLocalPolicyAction::FILTER_POSITIVE:
							$act->setFilterType(ilDidacticTemplateLocalPolicyAction::FILTER_POSITIVE);
							break;

						case ilDidacticTemplateLocalPolicyAction::FILTER_NEGATIVE:
							$act->setFilterType(ilDidacticTemplateLocalPolicyAction::FILTER_NEGATIVE);
							break;
					}
				}
				// local policy template
				foreach($ele->localPolicyTemplate as $lpt)
				{
					switch((string) $lpt->attributes()->type)
					{
						case ilDidacticTemplateLocalPolicyAction::TPL_ACTION_OVERWRITE:
							$act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_OVERWRITE);
							break;

						case ilDidacticTemplateLocalPolicyAction::TPL_ACTION_INTERSECT:
							$act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_INTERSECT);
							break;

						case ilDidacticTemplateLocalPolicyAction::TPL_ACTION_ADD:
							$act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_ADD);
							break;

						case ilDidacticTemplateLocalPolicyAction::TPL_ACTION_SUBTRACT:
							$act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_SUBTRACT);
							break;

						case ilDidacticTemplateLocalPolicyAction::TPL_ACTION_UNION:
							$act->setRoleTemplateType(ilDidacticTemplateLocalPolicyAction::TPL_ACTION_UNION);
							break;
					}
					$act->setRoleTemplateId((string) $lpt->attributes()->id);
				}

			}

			// Other action types

			$act->save();
		}
	}

	/**
	 * Parse xml errors from libxml_get_errors
	 *
	 * @return string
	 */
	protected function parseXmlErrors()
	{
		$errors = '';
		foreach(libxml_get_errors() as $err)
		{
			$errors .= $err->code.'<br/>';
		}
		return $errors;
	}
}
?>