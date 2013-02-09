<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceEntity
{
	/**
	 * @return mixed
	 */
	public function getId();

	/**
	 * @param mixed $id
	 */
	public function setId($id);

	/**
	 * 
	 */
	public function save();
}
