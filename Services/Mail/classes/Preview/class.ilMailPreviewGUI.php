<?php
require_once("Services/Mail/classes/Preview/ilPreviewFactory.php");

/**
 * Copyright (c) 2017 ILIAS open source, Extended GPL, see docs/LICENSE
 * CaT Concepts and Training GmbH
 */

/**
 * Preview form for mail templates
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilMailPreviewGUI {
	/**
	 * @var ilMailTemplate
	 */
	protected $template_id;

	public function __construct(ilMailTemplate $template, ilPreviewFactory $preview_factory) {
		global $DIC;
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->template = $template;
		$this->preview_factory = $preview_factory;
	}

	/**
	 * Render mail preview
	 *
	 * @return string
	 */
	public function getHTML() {
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setPreventDoubleSubmission(false);
		$form->setTableWidth('100%');
		$form->setTitle("Vorschau für: ".$this->template->getTitle());

		require_once("Services/Mail/classes/class.ilMail.php");
		$from = new ilCustomInputGUI($this->g_lng->txt('from'));
		$from->setHtml($this->g_user->getFullName());
		$form->addItem($from);

		$to = new ilCustomInputGUI($this->g_lng->txt('mail_to'));
		$to->setHtml(ilUtil::htmlencodePlainString($this->g_lng->txt('user'), false));
		$form->addItem($to);

		$subject = new ilCustomInputGUI($this->g_lng->txt('subject'));
		$subject->setHtml(ilUtil::htmlencodePlainString($this->template->getSubject(), true));
		$form->addItem($subject);

		$message = new ilCustomInputGUI($this->g_lng->txt('message'));
		$message->setHtml($this->populatePlaceholder($this->template->getMessage()));
		$form->addItem($message);

		return $form->getHtml();
	}

	/**
	 * Replace placeholders with default values
	 *
	 * @param string 	$message
	 *
	 * @return string
	 */
	protected function populatePlaceholder($message) {
		$context_preview = $this->preview_factory->getPreviewForContext($this->template->getContext());
		require_once 'Services/Mail/classes/class.ilMailTemplatePlaceholderResolver.php';
		$processor = new ilMailTemplatePlaceholderResolver($context_preview, $message);
		$message = $processor->resolve($this->g_user, array("ref_id"=>""), false);

		return $message;
	}
}