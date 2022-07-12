<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * News render interface
 * @author Alexander Killing <killing@leifos.de>
 */
interface ilNewsRendererGUI
{
    public function __construct();

    public function setLanguage(string $lang_key) : void;

    public function setNewsItem(ilNewsItem $a_news_item, int $a_news_ref_id) : void;

    /**
     * Render content for timeline
     */
    public function getTimelineContent() : string;

    /**
     * Render content for detail view
     */
    public function getDetailContent() : string;

    public function addTimelineActions(ilAdvancedSelectionListGUI $list) : void;

    /**
     * Get link href for object link
     */
    public function getObjectLink() : string;
}
