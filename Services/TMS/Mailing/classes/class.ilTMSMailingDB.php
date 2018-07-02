<?php

use ILIAS\TMS\Mailing;

class ilTMSMailingDB implements Mailing\MailingDB {
	const TABLE_NAME = "mail_man_tpl";

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db) {
		$this->db = $db;
	}

	/**
	 * Get template id by template title
	 *
	 * @param string 	$title
	 *
	 * @return int
	 */
	public function getTemplateIdByTitle($title) {
		assert('is_string($title)');
		$query = "SELECT tpl_id".PHP_EOL
				." FROM ".self::TABLE_NAME.PHP_EOL
				." WHERE title LIKE ".$this->getDB()->quote($title."%", "text");

		$res = $this->getDB()->query($query);
		if($this->getDB()->numRows($res) == 0) {
			throw new Exception("There is no id for template title: ".$title);
		}

		$row = $this->getDB()->fetchAssoc($res);

		return (int)$row["tpl_id"];
	}

	/**
	 * @inheritdoc
	 */
	public function getTemplateDataByTitle($title) {
		assert('is_string($title)');

		$query = "SELECT tpl_id, title, context, lang, m_subject, m_message".PHP_EOL
				." FROM ".self::TABLE_NAME.PHP_EOL
				." WHERE title LIKE ".$this->getDB()->quote($title."%", "text");

		$res = $this->getDB()->query($query);
		if($this->getDB()->numRows($res) == 0) {
			return null;
		}

		$row = $this->getDB()->fetchAssoc($res);

		return array(
			'id' => $row["tpl_id"],
			'title' => $row["title"],
			'context' => $row["context"],
			'lang' => $row["lang"],
			'subject' => $row["m_subject"],
			'message' => $row["m_message"]
		);

	}


	/**
	 * Get the db handler
	 *
	 * @throws \Exception
	 *
	 * @return \ilDB
	 */
	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}