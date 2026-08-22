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

    public function settings(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SETTINGS, $this->language->txt("settings"), $action);
    }

    public function collapse(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE, $this->language->txt("collapse_content"), $action);
    }

    public function expand(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EXPAND, $this->language->txt("expand_content"), $action);
    }

    public function add(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ADD, $this->language->txt("add"), $action);
    }

    public function remove(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::REMOVE, $this->language->txt("remove"), $action);
    }

    public function up(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::UP, $this->language->txt("up"), $action);
    }

    public function down(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DOWN, $this->language->txt("down"), $action);
    }

    public function back(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BACK, $this->language->txt("back"), $action);
    }

    public function next(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NEXT, $this->language->txt("next"), $action);
    }

    public function sortAscending(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT_ASCENDING, $this->language->txt("sort_ascending"), $action);
    }

    public function briefcase(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BRIEFCASE, $this->language->txt("briefcase"), $action);
    }

    public function sortDescending(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT_DESCENDING, $this->language->txt("sort_descending"), $action);
    }

    public function user(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::USER, $this->language->txt("show_who_is_online"), $action);
    }

    public function mail(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::MAIL, $this->language->txt("mail"), $action);
    }

    public function notification(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NOTIFICATION, $this->language->txt("notifications"), $action);
    }

    public function tag(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TAG, $this->language->txt("tags"), $action);
    }

    public function note(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NOTE, $this->language->txt("notes"), $action);
    }

    public function comment(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COMMENT, $this->language->txt("comments"), $action);
    }

    public function like(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LIKE, $this->language->txt("like"), $action);
    }

    public function love(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOVE, $this->language->txt("love"), $action);
    }

    public function dislike(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DISLIKE, $this->language->txt("dislike"), $action);
    }

    public function laugh(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LAUGH, $this->language->txt("laugh"), $action);
    }

    public function astounded(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ASTOUNDED, $this->language->txt("astounded"), $action);
    }

    public function sad(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SAD, $this->language->txt("sad"), $action);
    }

    public function angry(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ANGRY, $this->language->txt("angry"), $action);
    }

    public function eyeopen(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EYEOPEN, $this->language->txt("eyeopened"), $action);
    }

    public function eyeclosed(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EYECLOSED, $this->language->txt("eyeclosed"), $action);
    }

    public function attachment(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ATTACHMENT, $this->language->txt("attachment"), $action);
    }

    public function reset(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::RESET, $this->language->txt("reset"), $action);
    }

    public function apply(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::APPLY, $this->language->txt("apply"), $action);
    }

    public function search(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SEARCH, $this->language->txt("search"), $action);
    }

    public function help(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::HELP, $this->language->txt("help"), $action);
    }

    public function calendar($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::CALENDAR, $this->language->txt("calendar"), $action);
    }

    public function time($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TIME, $this->language->txt("time"), $action);
    }

    public function close($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::CLOSE, $this->language->txt("close"), $action);
    }

    public function more($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::MORE, $this->language->txt("show_more"), $action);
    }

    public function disclosure($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DISCLOSURE, $this->language->txt("disclose"), $action);
    }

    public function language(?string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LANGUAGE, $this->language->txt("switch_language"), $action);
    }

    public function login(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOGIN, $this->language->txt("log_in"), $action);
    }

    public function logout(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOGOUT, $this->language->txt("log_out"), $action);
    }

    public function bulletlist(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BULLETLIST, $this->language->txt("bulletlist_action"), $action);
    }

    public function numberedlist(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NUMBEREDLIST, $this->language->txt("numberedlist_action"), $action);
    }

    public function listindent(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LISTINDENT, $this->language->txt("listindent"), $action);
    }

    public function listoutdent(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LISTOUTDENT, $this->language->txt("listoutdent"), $action);
    }

    public function filter(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::FILTER, $this->language->txt("filter"), $action);
    }

    public function collapseHorizontal(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE_HORIZONTAL, $this->language->txt("collapse/back"), $action);
    }

    public function header(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::HEADER, $this->language->txt("header_action"), $action);
    }

    public function italic(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ITALIC, $this->language->txt("italic_action"), $action);
    }

    public function bold(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BOLD, $this->language->txt("bold_action"), $action);
    }

    public function link(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LINK, $this->language->txt("link_action"), $action);
    }

    public function launch(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LAUNCH, $this->language->txt("launch"), $action);
    }

    public function enlarge(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ENLARGE, $this->language->txt("enlarge"), $action);
    }

    public function listView(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LIST_VIEW, $this->language->txt("list_view"), $action);
    }

    public function preview(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::PREVIEW, $this->language->txt("preview"), $action);
    }

    public function sort(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT, $this->language->txt("sort"), $action);
    }

    public function columnSelection(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLUMN_SELECTION, $this->language->txt("column_selection"), $action);
    }

    public function tileView(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TILE_VIEW, $this->language->txt("tile_view"), $action);
    }

    public function dragHandle(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DRAG_HANDLE, $this->language->txt("drag_handle"), $action);
    }

    public function presenter(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::PRESENTER, "presenter", $action);
    }

    public function owner(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::OWNER, "owner", $action);
    }

    public function date(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DATE, "date", $action);
    }

    public function location(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOCATION, "location", $action);
    }
}
