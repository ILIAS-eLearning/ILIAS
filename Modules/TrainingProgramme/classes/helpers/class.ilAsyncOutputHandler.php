<?php
require_once("./Services/UICore/classes/class.ilTemplate.php");

/**
 * Class ilAsyncOutputHandler
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncOutputHandler {

	protected $content;

	protected $heading;

	protected $window_properties;

	public function __construct($content = null, $heading = null, $windows_properties = array()) {
		$this->content = $content;
		$this->heading = $heading;

		$this->window_properties = $windows_properties;
	}

	public function terminate() {
		$tpl = new ilTemplate('tpl.modal_content.html', false, false, 'Modules/TrainingProgramme');
		$tpl->setVariable('HEADING', $this->getHeading());
		$tpl->setVariable('BODY', $this->getContent());


		/*foreach($this->window_properties as $key => $value) {
			if($value) {
				$tpl->activeBlock($key);
			} else {
				$tpl->removeBlockData($key);
			}
		}*/

		echo $tpl->get();
		exit();
	}



	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}


	/**
	 * @param mixed $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}


	/**
	 * @return mixed
	 */
	public function getHeading() {
		return $this->heading;
	}


	/**
	 * @param mixed $heading
	 */
	public function setHeading($heading) {
		$this->heading = $heading;
	}


	/**
	 * @return mixed
	 */
	public function getWindowProperties() {
		return $this->window_properties;
	}


	/**
	 * @param mixed $window_properties
	 */
	public function setWindowProperties($window_properties) {
		$this->window_properties = $window_properties;
	}

}