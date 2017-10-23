<?php
// cat-tms-patch start

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use CaT\Ente\ILIAS\ilHandlerObjectHelper;

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Handles course mail placeholders
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @package ModulesCourse
 */
class ilCourseMailTemplateAutomaticContext extends ilMailTemplateContext
{
	use ilHandlerObjectHelper;

	const ID = 'crs_context_automatic';
	const REPOSITORY_REF_ID = 1;

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

		foreach ($this->getComponentPlaceholders() as $comp_placeholder) {
			$placeholders[$comp_placeholder->getPlaceholder()] = array(
					'placeholder'	=> $comp_placeholder->getPlaceholder(),
					'label'			=> $comp_placeholder->getDescription()
				);
		}


		foreach ($this->getGloballyProvidedMailContexts() as $context) {
			foreach ($context->placeholderIds() as $placeholder_id) {
				$id = get_class($context) .$placeholder_id;
				$placeholders[$id] = array(
					'placeholder' => $placeholder_id,
					'label'	=> get_class($context)
				);
			}
		}

		foreach ($this->getTMSStandardPlaceholderIds() as $context => $ids) {
			foreach ($ids as $id) {
				$placeholders[$context .$id] = array(
						'placeholder' => $id,
						'label'	=> $context
					);
			}
		}

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

		$placeholder_values = $this->getComponentPlaceholdersValues();
		$placeholder = array_filter($placeholder_values, function($p) use ($placeholder_id) {
				if($p->getPlaceholder() == $placeholder_id) {
					return $p;
				}
			}
		);

		if(count($placeholder) == 1) {
			$p = array_shift($placeholder);
			return $p->getValue();
		}

		return '';
	}

	/**
	 * Get all mail placeholder via CaT\Ente
	 *
	 * @return \ILIAS\TMS\Mailing\MailPlaceholder[]
	 */
	protected function getComponentPlaceholders() {
		return $this->getComponentsOfType(\ILIAS\TMS\Mailing\Placeholder::class);
	}

	/**
	 * Get all mail placeholder values via CaT\Ente
	 *
	 * @return MailPlaceholderValue[]
	 */
	protected function getComponentPlaceholdersValues() {
		return $this->getComponentsOfType(\ILIAS\TMS\Mailing\PlaceholderValue::class);
	}


	/**
	 * Get all mailing contexts from Ente
	 *
	 * @return MailContext[]
	 */
	protected function getGloballyProvidedMailContexts() {
		return $this->getComponentsOfType(ILIAS\TMS\Mailing\MailContext::class);
	}

	/**
	 * Get placeholderids of TMS-Standard contexts
	 *
	 * @return array<string, string[]>
	 */
	protected function getTMSStandardPlaceholderIds() {
		require_once('./Services/TMS/Mailing/classes/ilTMSMailing.php');
		$tms_mailing = new \ilTMSMailing();
		return $tms_mailing->getPlaceholderIdsOfStandardContexts();
	}

	/**
	 * @inheritdoc
	 */
	protected function getEntityRefId() {
		return self::REPOSITORY_REF_ID;
	}

	/**
	 * @inheritdoc
	 */
	protected function getDIC() {
		global $DIC;
		return $DIC;
	}
}

// cat-tms-patch end