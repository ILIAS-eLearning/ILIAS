<?php

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Implementation\Component\Connector\TriggerAction;

/**
 * This action shows a modal async, meaning that the complete content of the modal is loaded
 * via ajax before the modal is displayed.
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ShowAsyncAction extends TriggerAction {

	/**
	 * The URL where the ajax request is sent, must render the complete modal
	 *
	 * @var string
	 */
	protected $async_url;


	/**
	 * @param \ILIAS\UI\Component\Component $component
	 * @param                               $async_url
	 */
	public function __construct(\ILIAS\UI\Component\Component $component, $async_url) {
		parent::__construct($component);
		$this->async_url = $async_url;
	}


	/**
	 * @inheritdoc
	 */
	public function renderJavascript($id) {
		return <<<EOT
$.get('{$this->async_url}', function(modal_html) {
	var \$modal = $('#{$id}');
	\$modal.html($(modal_html).find('.modal-dialog'));
	// Hacky: As the cancel button receives a new ID, we must bind the close event manually again
	\$cancel_button = $(modal_html).find('.modal-footer .btn:last-child');
	if (\$cancel_button.length) {
		\$modal.on('click', '#' + \$cancel_button.attr('id'), function() {
			\$modal.modal('hide'); return false;
		});
	}
	\$modal.modal('show');
});
return false;
EOT;
	}
}