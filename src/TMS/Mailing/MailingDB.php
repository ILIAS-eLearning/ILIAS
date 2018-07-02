<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

interface MailingDB {
	/**
	 * Get template id by template title
	 *
	 * @param string 	$title
	 *
	 * @return int
	 */
	public function getTemplateIdByTitle($title);

	/**
	 * Get template data by template title.
	 * TemplateData holds title, context, language, subject and message of a mail-template.
	 *
	 * @param string 	$title
	 *
	 * @return array<string, string> | null
	 */
	public function getTemplateDataByTitle($title);
}