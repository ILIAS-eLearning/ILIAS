<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilFormPropertyGUI.php';

/**
 * Class ilTermsOfServiceSignedDocumentFormElementGUI
 */
class ilTermsOfServiceSignedDocumentFormElementGUI extends ilFormPropertyGUI
{
	/**
	 * @var ilTermsOfServiceAcceptanceEntity
	 */
	protected $entity;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @param string                           $a_title
	 * @param string                           $a_postvar
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 */
	public function __construct($a_title = '', $a_postvar = '', ilTermsOfServiceAcceptanceEntity $entity)
	{
		global $DIC;

		parent::__construct($a_title, $a_postvar);

		$this->entity = $entity;
		$this->lng    = $DIC['lng'];
	}

	/**
	 * @return bool
	 */
	public function checkInput()
	{
		return true;
	}

	/**
	 * @param ilTemplate $tpl
	 */
	public function insert(ilTemplate $tpl)
	{
		$local_tpl = new ilTemplate('tpl.prop_tos_signed_document.html', true, true, 'Services/TermsOfService');

		require_once 'Services/UIComponent/Modal/classes/class.ilModalGUI.php';
		$modal = ilModalGUI::getInstance();
		$modal->setType(ilModalGUI::TYPE_LARGE);
		$modal->setHeading($this->lng->txt('tos_agreement_document'));
		$modal->setId('accepted_tos_' . $this->entity->getUserId());
		$modal->setBody($this->entity->getText());

		require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';
		$local_tpl->setVariable('MODAL_TRIGGER_HTML', ilGlyphGUI::get(ilGlyphGUI::SEARCH));
		$local_tpl->setVariable('MODAL', $modal->getHTML());
		$local_tpl->setVariable('MODAL_ID', 'accepted_tos_' . $this->entity->getUserId());

		$tpl->setCurrentBlock('prop_generic');
		$tpl->setVariable('PROP_GENERIC', $local_tpl->get());
		$tpl->parseCurrentBlock();
	}
} 