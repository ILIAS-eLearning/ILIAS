<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * DOM 2 util
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services/Utilites
 */
class ilDOM2Util
{
	/**
	 * Change name of a node
	 *
	 * @param object $node
	 * @param string new name
	 * @return
	 */
	static function changeName($node, $name, $keep_attributes = true)
	{
		$newnode = $node->ownerDocument->createElement($name);
		foreach ($node->childNodes as $child)
		{
			$child = $node->ownerDocument->importNode($child, true);
			$newnode->appendChild($child);
		}
		if ($keep_attributes)
		{
			foreach ($node->attributes as $attrName => $attrNode)
			{
				$newnode->setAttribute($attrName, $attrNode);
			}
		}
		$newnode = $node->parentNode->replaceChild($newnode, $node);

		return $newnode;
	}
}
?>
