<?php
// cat-tms-patch start

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Handles course mail placeholders
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @package ModulesCourse
 */
class ilCourseMailTemplateAutomaticContext extends ilMailTemplateContext
{
	const ID = 'crs_context_automatic';

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
		global $lng;

		$lng->loadLanguageModule('crs');

		return $lng->txt('crs_mail_context_automatic_title');
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		global $lng;

		$lng->loadLanguageModule('crs');

		return $lng->txt('crs_mail_context_automatic_info');
	}

	/**
	 * Return an array of placeholders
	 * @return array
	 */
	public function getSpecificPlaceholders()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$lng->loadLanguageModule('crs');

		$placeholders = array();

		$placeholders['crs_title'] = array(
			'placeholder'	=> 'COURSE_TITLE',
			'label'			=> $lng->txt('crs_title')
		);

		$placeholders['crs_link'] = array(
			'placeholder'	=> 'COURSE_LINK',
			'label'			=> $lng->txt('crs_mail_permanent_link')
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
		global $ilObjDataCache;

		if('crs_title' == $placeholder_id)
		{
			return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));
		}
		else if('crs_link' == $placeholder_id)
		{
			require_once './Services/Link/classes/class.ilLink.php';
			return ilLink::_getLink($context_parameters['ref_id'], 'crs');
		}

		return '';
	}
}

// cat-tms-patch end