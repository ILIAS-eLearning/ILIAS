<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceEntity
{
	/**
	 * @abstract
	 * @return mixed
	 */
	public function getId();

	/**
	 * @abstract
	 * @param $id
	 */
	public function setId($id);

	/**
	 * @abstract
	 */
	public function save();
}
