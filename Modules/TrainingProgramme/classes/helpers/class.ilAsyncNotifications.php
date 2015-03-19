<?php

/**
 * Class ilAsyncNotifications
 * @author Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilAsyncNotifications {
	protected $js_init;

	protected $content_container_id;

	protected $js_path;
	protected $js_config;

	public function __construct($content_container_id = null) {
		$this->js_init = false;
		$this->js_path = "./Modules/TrainingProgramme/templates/js/";
		$this->content_container_id = ($content_container_id != null)? $content_container_id : "ilContentContainer";
	}

	public function initJs() {
		global $tpl;

		if(!$this->js_init) {
			$tpl->addJavaScript($this->getJsPath().'ilTrainingProgramme.js');

			$templates['info'] = $tpl->getMessageHTML("[MESSAGE]");
			$templates['success'] = $tpl->getMessageHTML("[MESSAGE]", 'success');
			$templates['failure'] = $tpl->getMessageHTML("[MESSAGE]", 'failure');
			$templates['question'] = $tpl->getMessageHTML("[MESSAGE]", 'question');

			$this->addJsConfig('templates', $templates);

			$tpl->addOnLoadCode("$('#".$this->content_container_id."').training_programme_notifications(".json_encode($this->js_config).");");

			$this->js_init = true;
		}
	}

	public function getHTML() {
		global $tpl;

		$this->initJs();
	}

	/**
	 * @return mixed
	 */
	public function getTemplateId() {
		return $this->template_id;
	}


	/**
	 * @param mixed $template_id
	 */
	public function setTemplateId($template_id) {
		$this->template_id = $template_id;
	}


	/**
	 * @return string
	 */
	public function getJsPath() {
		return $this->js_path;
	}


	/**
	 * @param string $js_path
	 */
	public function setJsPath($js_path) {
		$this->js_path = $js_path;
	}


	/**
	 * @return mixed
	 */
	public function getJsConfig($key) {
		return $this->js_config[$key];
	}


	/**
	 * @param mixed $js_config
	 */
	public function addJsConfig($key, $value) {
		$this->js_config[$key] = $value;
	}
}