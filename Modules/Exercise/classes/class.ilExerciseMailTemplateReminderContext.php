<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * TODO Test this links if they work with assignments or we should call exercises by ref.
 * Handles exercise reminder mail placeholders
 * 
 * @author Jesús López <lopez@leifos.com>
 * @package ModulesExercise
 */
class ilExerciseMailTemplateReminderContext extends ilMailTemplateContext
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjectDataCache
	 */
	protected $obj_data_cache;


	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		if (isset($DIC["ilObjDataCache"]))
		{
			$this->obj_data_cache = $DIC["ilObjDataCache"];
		}
	}

	const ID = 'exc_context_rmd';
	
	/**
	 * @return string
	 */
	public function getId()
	{
		return self::ID;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		$lng = $this->lng;
		
		$lng->loadLanguageModule('exc');
		
		return $lng->txt('exc_mail_context_reminder_title');
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		$lng = $this->lng;

		$lng->loadLanguageModule('exc');

		return $lng->txt('exc_mail_context_reminder_info');
	}

	/**
	 * Return an array of placeholders
	 * @return array
	 */
	public function getSpecificPlaceholders()
	{
		$lng = $this->lng;
		$lng->loadLanguageModule('exc');

		$placeholders = array();
		
		$placeholders['ass_title'] = array(
			'placeholder'	=> 'ASSIGNMENT_TITLE',
			'label'			=> $lng->txt('exc_mail_context_reminder_assignment_title')
		);
								
		$placeholders['ass_link'] = array(
			'placeholder'	=> 'ASSIGNMENT_LINK',
			'label'			=> $lng->txt('perma_link')
		);

		return $placeholders;
	}

	/**
	 * {@inheritdoc}
	 */
	public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient = null, $html_markup = false)
	{
		/**
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		$ilObjDataCache = $this->obj_data_cache;

		if('ass_title' == $placeholder_id)
		{
			return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));
		}
		else if('ass_link' == $placeholder_id)
		{
			require_once './Services/Link/classes/class.ilLink.php';
			return ilLink::_getLink($context_parameters['ref_id'], 'ass');
		}

		return '';
	}
}