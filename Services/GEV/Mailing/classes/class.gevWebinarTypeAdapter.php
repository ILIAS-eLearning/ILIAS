<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ("Services/GEV/Mailing/classes/class.gevCrsMailTypeAdapter.php");

/**
 * GEV mail placeholders for Webinar
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version $Id$
 */
class gevWebinarTypeAdapter extends gevCrsMailTypeAdapter {
	private $placeholders = null;
	
	public function getCategoryNameLocalized($category_name, $lng) {
		return 'webinar';
	}
}