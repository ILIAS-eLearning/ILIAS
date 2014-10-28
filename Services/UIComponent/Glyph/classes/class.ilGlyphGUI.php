<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilGlyphGUI
{
	const UP = "up";
	const DOWN = "down";
	const ADD = "add";
	const REMOVE = "remove";

	static protected $map = array(
		"up" => array("class" => "glyphicon glyphicon-chevron-up", "txt" => "up"),
		"down" => array("class" => "glyphicon glyphicon-chevron-down", "txt" => "down"),
		"add" => array("class" => "glyphicon glyphicon-plus", "txt" => "add"),
		"remove" => array("class" => "glyphicon glyphicon-minus", "txt" => "remove")
	);

	/**
	 * Get glyph html
	 *
	 * @param string glyph constant
	 * @param string $a_text text representation
	 * @return string html
	 */
	function get($a_glyph, $a_text = "")
	{
		global $lng;

		$html = "";
		$text = ($a_text == "")
			? $lng->txt(self::$map[$a_glyph]["txt"])
			: $a_text;

		switch ($a_glyph)
		{
			default:
				$html = '<span class="sr-only">'.$text.
					'</span><span class="'.self::$map[$a_glyph]["class"].'"></span>';
				break;

		}
		return $html;
	}

}

?>