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
	const PREVIOUS = "previous";
	const NEXT = "next";
	const NO_TEXT = "**notext**";

	static protected $map = array(
		"up" => array("class" => "glyphicon glyphicon-chevron-up", "txt" => "up"),
		"down" => array("class" => "glyphicon glyphicon-chevron-down", "txt" => "down"),
		"add" => array("class" => "glyphicon glyphicon-plus", "txt" => "add"),
		"remove" => array("class" => "glyphicon glyphicon-minus", "txt" => "remove"),
		"previous" => array("class" => "glyphicon glyphicon-chevron-left", "txt" => "previous"),
		"next" => array("class" => "glyphicon glyphicon-chevron-right", "txt" => "next")
	);

	/**
	 * Get glyph html
	 *
	 * @param string $a_glyph glyph constant
	 * @param string $a_text text representation
	 * @return string html
	 */
	static function get($a_glyph, $a_text = "")
	{
		global $lng;

		$html = "";
		$text = ($a_text == "")
			? $lng->txt(self::$map[$a_glyph]["txt"])
			: ($a_text == self::NO_TEXT)
				? ""
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