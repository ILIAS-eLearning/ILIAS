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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Component\Symbol\Glyph as G;

class Factory implements G\Factory
{
    public function __construct(
        protected \ILIAS\Language\Language $language,
    ) {
    }

    public function settings(): G\Glyph
    {
        return new Glyph(G\Glyph::SETTINGS, $this->language->txt("settings"));
    }

    public function collapse(): Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE, $this->language->txt("collapse_content"));
    }

    public function expand(): Glyph
    {
        return new Glyph(G\Glyph::EXPAND, $this->language->txt("expand_content"));
    }

    public function add(): Glyph
    {
        return new Glyph(G\Glyph::ADD, $this->language->txt("add"));
    }

    public function remove(): Glyph
    {
        return new Glyph(G\Glyph::REMOVE, $this->language->txt("remove"));
    }

    public function up(): Glyph
    {
        return new Glyph(G\Glyph::UP, $this->language->txt("up"));
    }

    public function down(): Glyph
    {
        return new Glyph(G\Glyph::DOWN, $this->language->txt("down"));
    }

    public function back(): Glyph
    {
        return new Glyph(G\Glyph::BACK, $this->language->txt("back"));
    }

    public function next(): Glyph
    {
        return new Glyph(G\Glyph::NEXT, $this->language->txt("next"));
    }

    public function sortAscending(): Glyph
    {
        return new Glyph(G\Glyph::SORT_ASCENDING, $this->language->txt("sort_ascending"));
    }

    public function briefcase(): Glyph
    {
        return new Glyph(G\Glyph::BRIEFCASE, $this->language->txt("briefcase"));
    }

    public function sortDescending(): Glyph
    {
        return new Glyph(G\Glyph::SORT_DESCENDING, $this->language->txt("sort_descending"));
    }

    public function user(): Glyph
    {
        return new Glyph(G\Glyph::USER, $this->language->txt("show_who_is_online"));
    }

    public function mail(): Glyph
    {
        return new Glyph(G\Glyph::MAIL, $this->language->txt("mail"));
    }

    public function notification(): Glyph
    {
        return new Glyph(G\Glyph::NOTIFICATION, $this->language->txt("notifications"));
    }

    public function tag(): Glyph
    {
        return new Glyph(G\Glyph::TAG, $this->language->txt("tags"));
    }

    public function note(): Glyph
    {
        return new Glyph(G\Glyph::NOTE, $this->language->txt("notes"));
    }

    public function comment(): Glyph
    {
        return new Glyph(G\Glyph::COMMENT, $this->language->txt("comments"));
    }

    public function like(): Glyph
    {
        return new Glyph(G\Glyph::LIKE, $this->language->txt("like"));
    }

    public function love(): Glyph
    {
        return new Glyph(G\Glyph::LOVE, $this->language->txt("love"));
    }

    public function dislike(): Glyph
    {
        return new Glyph(G\Glyph::DISLIKE, $this->language->txt("dislike"));
    }

    public function laugh(): Glyph
    {
        return new Glyph(G\Glyph::LAUGH, $this->language->txt("laugh"));
    }

    public function astounded(): Glyph
    {
        return new Glyph(G\Glyph::ASTOUNDED, $this->language->txt("astounded"));
    }

    public function sad(): Glyph
    {
        return new Glyph(G\Glyph::SAD, $this->language->txt("sad"));
    }

    public function angry(): Glyph
    {
        return new Glyph(G\Glyph::ANGRY, $this->language->txt("angry"));
    }

    public function eyeopen(): Glyph
    {
        return new Glyph(G\Glyph::EYEOPEN, $this->language->txt("eyeopened"));
    }

    public function eyeclosed(): Glyph
    {
        return new Glyph(G\Glyph::EYECLOSED, $this->language->txt("eyeclosed"));
    }

    public function attachment(): Glyph
    {
        return new Glyph(G\Glyph::ATTACHMENT, $this->language->txt("attachment"));
    }

    public function reset(): Glyph
    {
        return new Glyph(G\Glyph::RESET, $this->language->txt("reset"));
    }

    public function apply(): Glyph
    {
        return new Glyph(G\Glyph::APPLY, $this->language->txt("apply"));
    }

    public function search(): Glyph
    {
        return new Glyph(G\Glyph::SEARCH, $this->language->txt("search"));
    }

    public function help(): Glyph
    {
        return new Glyph(G\Glyph::HELP, $this->language->txt("help"));
    }

    public function calendar(): Glyph
    {
        return new Glyph(G\Glyph::CALENDAR, $this->language->txt("calendar"));
    }

    public function time(): Glyph
    {
        return new Glyph(G\Glyph::TIME, $this->language->txt("time"));
    }

    public function close(): Glyph
    {
        return new Glyph(G\Glyph::CLOSE, $this->language->txt("close"));
    }

    public function more(): Glyph
    {
        return new Glyph(G\Glyph::MORE, $this->language->txt("show_more"));
    }

    public function disclosure(): Glyph
    {
        return new Glyph(G\Glyph::DISCLOSURE, $this->language->txt("disclose"));
    }

    public function language(): Glyph
    {
        return new Glyph(G\Glyph::LANGUAGE, $this->language->txt("switch_language"));
    }

    public function login(): Glyph
    {
        return new Glyph(G\Glyph::LOGIN, $this->language->txt("log_in"));
    }

    public function logout(): Glyph
    {
        return new Glyph(G\Glyph::LOGOUT, $this->language->txt("log_out"));
    }

    public function bulletlist(): Glyph
    {
        return new Glyph(G\Glyph::BULLETLIST, $this->language->txt("bulletlist_action"));
    }

    public function numberedlist(): Glyph
    {
        return new Glyph(G\Glyph::NUMBEREDLIST, $this->language->txt("numberedlist_action"));
    }

    public function listindent(): Glyph
    {
        return new Glyph(G\Glyph::LISTINDENT, $this->language->txt("listindent"));
    }

    public function listoutdent(): Glyph
    {
        return new Glyph(G\Glyph::LISTOUTDENT, $this->language->txt("listoutdent"));
    }

    public function filter(): Glyph
    {
        return new Glyph(G\Glyph::FILTER, $this->language->txt("filter"));
    }

    public function collapseHorizontal(): Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE_HORIZONTAL, $this->language->txt("collapse/back"));
    }

    public function header(): Glyph
    {
        return new Glyph(G\Glyph::HEADER, $this->language->txt("header_action"));
    }

    public function italic(): Glyph
    {
        return new Glyph(G\Glyph::ITALIC, $this->language->txt("italic_action"));
    }

    public function bold(): Glyph
    {
        return new Glyph(G\Glyph::BOLD, $this->language->txt("bold_action"));
    }

    public function link(): Glyph
    {
        return new Glyph(G\Glyph::LINK, $this->language->txt("link_action"));
    }

    public function launch(): Glyph
    {
        return new Glyph(G\Glyph::LAUNCH, $this->language->txt("launch"));
    }

    public function enlarge(): Glyph
    {
        return new Glyph(G\Glyph::ENLARGE, $this->language->txt("enlarge"));
    }

    public function listView(): Glyph
    {
        return new Glyph(G\Glyph::LIST_VIEW, $this->language->txt("list_view"));
    }

    public function preview(): Glyph
    {
        return new Glyph(G\Glyph::PREVIEW, $this->language->txt("preview"));
    }

    public function sort(): Glyph
    {
        return new Glyph(G\Glyph::SORT, $this->language->txt("sort"));
    }

    public function columnSelection(): Glyph
    {
        return new Glyph(G\Glyph::COLUMN_SELECTION, $this->language->txt("column_selection"));
    }

    public function tileView(): Glyph
    {
        return new Glyph(G\Glyph::TILE_VIEW, $this->language->txt("tile_view"));
    }

    public function dragHandle(): G\Glyph
    {
        return new Glyph(G\Glyph::DRAG_HANDLE, $this->language->txt("drag_handle"));
    }

    public function checked(): G\Glyph
    {
        return new Glyph(G\Glyph::CHECKED, $this->language->txt("checked"));
    }

    public function unchecked(): G\Glyph
    {
        return new Glyph(G\Glyph::UNCHECKED, $this->language->txt("unchecked"));
    }
}
