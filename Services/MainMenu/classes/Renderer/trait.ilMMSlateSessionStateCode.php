<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Slate;

/**
 * Class
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilMMSlateSessionStateCode {

	use ilMMHasher;


	/**
	 * @param Slate $slate
	 *
	 * @return Slate
	 */
	public function addOnloadCode(Slate $slate, isItem $item): Slate {
		$show_signal = $slate->getToggleSignal();
		$identification = $this->hash($item->getProviderIdentification()->serialize());

		if(isset($_COOKIE[$identification])) {
			$slate = $slate->withEngaged(true);
		}


		return $slate->withAdditionalOnLoadCode(
			function ($id) use ($show_signal, $identification) {
				return "
				$(document).on('{$show_signal}', function(event, signalData) {
					console.log('{$identification} opened/closed (js-id {$id})');
					if(document.cookie.indexOf('{$identification}') >= 0) {
						document.cookie = '{$identification}' + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;'
					}else {
						document.cookie = '{$identification}' + '=' + true
					}
					
					
					
					
					return false;
				});
			";
			}
		);
	}
}
