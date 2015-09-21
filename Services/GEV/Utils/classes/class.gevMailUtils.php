<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Utilities for AdvancedMetadata of Generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/
require_once './Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php';

class gevMailUtils {
		protected $intance;

		protected function __construct() {
			global $ilDB;

			$this->gDB = $ilDB;
		}

		public function getInstance() {
			if(!$this->instance === null) {
				return $this->instance;
			}

			$this->instance = new gevMailUtils();

			return $this->instance;
		}

		public function getMailTemplateByIdAndLanguage($mail_tpl_id, $language) {
			$variant = new ilMailTemplateVariantEntity();
			$variant->setIlDB($this->gDB);
			$variant->loadByTypeAndLanguage($mail_tpl_id, $language);
			$mail_tpl = ($variant->getMessageHtml() !== null) ? $variant->getMessageHtml() : $variant->getMessagePlain();

			return $mail_tpl;
		}
}