<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
interface ilTermsOfServiceTableDataProvider
{
	/**
	 * @param array $params Table paramaters like limit or order
	 * @param array $filter Filter settings
	 * @return array
	 */
	public function getList(array $params, array $filter);
}