<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Blog news renderer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBlogNewsRendererGUI extends ilNewsDefaultRendererGUI
{
	/**
	 * Get object link
	 *
	 * @return string link href url
	 */
	function getObjectLink()
	{
		$n = $this->getNewsItem();
		$add = "";
		if ($n->getContextSubObjType() == "blp"
			&& $n->getContextSubObjId() > 0)
		{
			$add = "_".$n->getContextSubObjId();
		}

		return ilLink::_getLink($this->getNewsRefId(), "", array(), $add);
	}

}