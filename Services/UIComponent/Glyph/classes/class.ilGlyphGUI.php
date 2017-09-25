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
	const CALENDAR = "calendar";
	const CLOSE = "close";
	const ATTACHMENT = "attachment";
	const CARET = "caret";
	const DRAG = "drag";
	const SEARCH = "search";
	const FILTER = "filter";
	const NO_TEXT = "**notext**";
	const INFO = "info";
	const EXCLAMATION = "exclamation";

	static protected $map = array(
		"up" => array("class" => "glyphicon glyphicon-chevron-up", "txt" => "up"),
		"down" => array("class" => "glyphicon glyphicon-chevron-down", "txt" => "down"),
		"add" => array("class" => "glyphicon glyphicon-plus", "txt" => "add"),
		"remove" => array("class" => "glyphicon glyphicon-minus", "txt" => "remove"),
		"previous" => array("class" => "glyphicon glyphicon-chevron-left", "txt" => "previous"),
		"next" => array("class" => "glyphicon glyphicon-chevron-right", "txt" => "next"),
		"calendar" => array("class" => "glyphicon glyphicon-calendar", "txt" => "calendar"),
		"close" => array("class" => "glyphicon glyphicon-remove", "txt" => "close"),
		"attachment" => array("class" => "glyphicon glyphicon-paperclip", "txt" => "attachment"),
		"caret" => array("class" => "", "txt" => ""),
		"drag" => array("class" => "glyphicon glyphicon-share-alt", "txt" => "drag"),
		"search" => array("class" => "glyphicon glyphicon-search", "txt" => "search"),
		"filter" => array("class" => "glyphicon glyphicon-filter", "txt" => "filter"),
		"exclamation" => array("class" => "glyphicon glyphicon-exclamation-sign ilAlert", "txt" => "exclamation"),
		"info" => array("class" => "glyphicon glyphicon-info-sign", "txt" => "info")
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
		global $DIC;

		$lng = $DIC->language();

		$html = "";
		$text = ($a_text == "")
			? $lng->txt(self::$map[$a_glyph]["txt"])
			: ($a_text == self::NO_TEXT)
				? ""
				: $a_text;
		switch ($a_glyph)
		{
			case self::CARET:
				$html = '<span class="caret"></span>';
				break;

			default:
				$html = '<span class="sr-only">'.$text.
					'</span><span class="'.self::$map[$a_glyph]["class"].'"></span>';
				break;

		}
		return $html;
	}

}

?>