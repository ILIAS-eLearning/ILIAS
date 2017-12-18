<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;

/**
 * Some Objects offer an export of data as file (PDF, XLS...).
 * They should announce this fact via the File-Component
 * and offer an inferface accordingly.
 */
interface File extends Component {

	/**
	 * Get the component's id. The id should be unique throughout the system.
	 * This is can be achieved, e.g., by combining object_id with ident.
	 *
	 * @return 	int
	 */
	public function getId();

	/**
	 * In order to identify the essence/intention of the file
	 * within the same provider, it gets an ident that identifies
	 * this _kind_ of file.
	 * The ident is not to be confused with the id, which
	 * must be truly unique for the exact file with specific contents.
	 *
	 * @return 	string
	 */
	public function getIdent();

	/**
	 * Get the ownwer of this component.
	 *
	 * @return 	\ilObject
	 */
	public function getOwner();

	/**
	 * Get the (mime-) type of this file.
	 *
	 * @return 	string
	 */
	public function getType();

	/**
	 * Get the filesystem-path to the file.
	 * It should exist, so probably it has to be created first...
	 *
	 * @return 	string
	 */
	public function getFilePath();

}