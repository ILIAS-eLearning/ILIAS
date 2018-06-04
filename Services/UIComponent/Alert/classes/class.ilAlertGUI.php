<?php
/**
 * Class ilAlertGUI
 */
class ilAlertGUI
{
	const PLACEHOLDER_STRING = '[[PLACEHOLDER]]';

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * ilAlertGUI constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->tpl = $DIC->ui()->mainTemplate();
	}

	/**
	 *
	 */
	public function renderToMainTemplate()
	{
		$this->tpl->addJavaScript('./Services/UIComponent/Alert/js/Alert.js');

		$templates = [];

		foreach ([
					 \ilTemplate::MESSAGE_TYPE_FAILURE,
					 \ilTemplate::MESSAGE_TYPE_INFO,
					 \ilTemplate::MESSAGE_TYPE_SUCCESS,
					 \ilTemplate::MESSAGE_TYPE_QUESTION
				 ] as $messageType) {
			$templates[$messageType] = $this->tpl->getMessageHTML(self::PLACEHOLDER_STRING, $messageType);
		}

		$this->tpl->addOnLoadCode('il.Alert.init(' . json_encode([
			'placeholder' => self::PLACEHOLDER_STRING,
			'templates'   => $templates
		]) . ');');
	}
}