<?php
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * This is an information about a agenda item.
 */
interface AgendaItemInfo extends Component {
	/**
	 * Get a label for this step in the process.
	 *
	 * @return	string
	 */
	public function getId();

	/**
	 * Get the value of this field.
	 *
	 * @return	string|array<string,string>
	 */
	public function getTitle();

	/**
	 * Get a description for this step in the process.
	 *
	 * @return	string
	 */
	public function getTopics();

	/**
	 * Get the contents of the AgendaItem
	 *
	 * @return	string
	 */
	public function getContents();

	/**
	 * Get IDD-relevance of the AgendaItem
	 *
	 * @return	bool
	 */
	public function getIDDRelevant();

	/**
	 * Get the goals of the AgendaItem
	 *
	 * @return	string
	 */
	public function getGoals();

}
