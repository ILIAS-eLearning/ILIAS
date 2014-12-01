<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id\$
 * @ingroup 
 */
class ilRSSButtonGUI
{
	const ICON_RSS = "rss";
	const ICON_RSS_AUDIO = "rss audio";
	const ICON_RSS_VIDEO = "rss video";
	const ICON_ICAL = "ical";
	const ICON_ITUNES = "itunes";
	const ICON_ITUNES_AUDIO = "itunes audio";
	const ICON_ITUNES_VIDEO = "itunes video";

	/**
	 * Get icon html
	 *
	 * @param string $a_type icons type ICON_RSS | ICON_ICAL
	 * @param string $a_href href
	 * @return string icon html
	 */
	static function get($a_type, $a_href = "")
	{
		$tpl = new ilTemplate("tpl.rss_icon.html", true, true, "Services/News");

		if ($a_href != "")
		{
			$tpl->setCurrentBlock("a_start");
			$tpl->setVariable("HREF", $a_href);
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("a_end");
		}

		$text = "";

		switch ($a_type)
		{
			case self::ICON_RSS:
				$text = "RSS";
				break;

			case self::ICON_RSS_AUDIO:
				$text = "RSS Audio";
				break;

			case self::ICON_RSS_VIDEO:
				$text = "RSS Video";
				break;

			case self::ICON_ICAL:
				$text = "iCal";
				break;

			case self::ICON_ITUNES:
				$text = "iTunes";
				break;

			case self::ICON_ITUNES_AUDIO:
				$text = "iTunes Audio";
				break;

			case self::ICON_ITUNES_VIDEO:
				$text = "iTunes Video";
				break;
		}

		$tpl->setVariable("TEXT", $text);

		return $tpl->get();
	}

}

?>