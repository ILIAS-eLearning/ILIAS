<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */
use ILIAS\TMS\Mailing;

/**
 * This builds content for mails in TMS, as e.g. used for
 * automatic notifications in courses.
 *
 */
class ilTMSMailContentBuilder implements Mailing\MailContentBuilder {
	const DEFAULT_WRAPPER = './Services/Mail/templates/default/tpl.html_mail_template.html';
	const DEFAULT_IMAGES = './Services/Mail/templates/default/img/';
	const CUSTOM_WRAPPER = './Customizing/global/skin/custom/Services/Mail/tpl.html_mail_template.html';
	const CUSTOM_IMAGES = './Customizing/global/skin/custom/Services/Mail/img/';

	//get all placeholder ids (w/o [])
	//read: lookahead for bracket, all chars, end with bracket
	const PLACEHOLDER = "/(?<=\[)[^]]+(?=\])/";

	/**
	 * @var MailingDB
	 */
	protected $mailing_db;

	/**
	 * @var string
	 */
	protected $ident;

	/**
	 * @var MailContext[]
	 */
	protected $contexts;

	/**
	 * @var array<string, string>
	 */
	protected $template_data;

	public function __construct(Mailing\MailingDB $mailing_db) {
		$this->mailing_db = $mailing_db;
	}

	/**
	 * @inheritdoc
	 */
	public function withData($ident, $contexts) {
		$clone = clone $this;
		$clone->ident = $ident;
		$clone->contexts = $contexts;
		$clone->initTemplateData();
		return $clone;
	}

	/**
	 * @return void
	 */
	private function initTemplateData() {
		$this->template_data = $this->mailing_db->getTemplateDataByTitle($this->ident);
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateId() {
		return $this->template_data['id'];
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateIdentifier() {
		return $this->ident;
	}

	/**
	 * Get the subject of Mail with placeholders applied
	 *
	 * @return string
	 */
	public function getSubject(){
		return $this->resolvePlaceholders($this->template_data['subject']);
	}

	/**
	 * @inheritdoc
	 */
	public function getMessage(){
		$msg = nl2br($this->getResolvedMessage());
		$wrapper = $this->getWrapper();
		$body = str_replace('{PLACEHOLDER}', $msg, $wrapper);
		return $body;
	}

	/**
	 * @inheritdoc
	 */
	public function getPlainMessage(){
		return strip_tags($this->getResolvedMessage());
	}

	/**
	 * @inheritdoc
	 */
	public function getEmbeddedImages(){
		if(! is_null($this->skin)) {
			$images_path = sprintf(self::CUSTOM_IMAGES, $this->skin);
			if(is_dir($images_path)) {
				return preg_grep ('/\.jpg$/i', $this->readDir($images_path));
			}
			return [];
		}
		return $this->readDir(self::DEFAULT_IMAGES);
	}

	/**
	 * @inheritdoc
	 */
	public function getAttachments()
	{

	}

	/**
	 * Replaces all placeholders.
	 *
	 * @return string
	 */
	private function getResolvedMessage(){
		return $this->resolvePlaceholders($this->template_data['message']);
	}

	/**
	 * Resolve all placeholder in txt
	 *
	 * @param string $txt
	 * @return string
	 */
	private function resolvePlaceholders($txt) {
		$placeholders = array();

		preg_match_all(self::PLACEHOLDER, $txt, $placeholders);
		foreach ($placeholders[0] as $placeholder) {
			$search = '[' .$placeholder .']';
			$value = '';
			foreach ($this->contexts as $context) {
				$v = $context->valueFor($placeholder, $this->contexts);
				if($v) {
					$value = $v;
				}
			}
			$txt = str_replace($search, $value, $txt);
		}
		return $txt;

	}


	private function getWrapper() {
		if(!file_exists(self::CUSTOM_WRAPPER)) {
			$bracket = file_get_contents(self::DEFAULT_WRAPPER);
		} else {
			$bracket = file_get_contents(self::CUSTOM_WRAPPER);
		}
		return $bracket;
	}

	private function readDir($dirpath) {
		$files = array_diff(scandir($dirpath), array('.', '..'));
		$ret = array();
		foreach ($files as $file) {
			$ret[] = array($dirpath.$file, 'img/'.$file);
		}
		return $ret;
	}

}
