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
    public function settings(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SETTINGS, "settings", $action);
    }

    public function collapse(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE, "collapse_content", $action);
    }

    public function expand(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EXPAND, "expand_content", $action);
    }

    public function add(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ADD, "add", $action);
    }

    public function remove(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::REMOVE, "remove", $action);
    }

    public function up(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::UP, "up", $action);
    }

    public function down(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DOWN, "down", $action);
    }

    public function back(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BACK, "back", $action);
    }

    public function next(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NEXT, "next", $action);
    }

    public function sortAscending(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT_ASCENDING, "sort_ascending", $action);
    }

    public function briefcase(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BRIEFCASE, "briefcase", $action);
    }

    public function sortDescending(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT_DESCENDING, "sort_descending", $action);
    }

    public function user(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::USER, "show_who_is_online", $action);
    }

    public function mail(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::MAIL, "mail", $action);
    }

    public function notification(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NOTIFICATION, "notifications", $action);
    }

    public function tag(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TAG, "tags", $action);
    }

    public function note(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NOTE, "notes", $action);
    }

    public function comment(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COMMENT, "comments", $action);
    }

    public function like(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LIKE, "like", $action);
    }

    public function love(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOVE, "love", $action);
    }

    public function dislike(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DISLIKE, "dislike", $action);
    }

    public function laugh(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LAUGH, "laugh", $action);
    }

    public function astounded(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ASTOUNDED, "astounded", $action);
    }

    public function sad(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SAD, "sad", $action);
    }

    public function angry(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ANGRY, "angry", $action);
    }

    public function eyeopen(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EYEOPEN, "eyeopened", $action);
    }

    public function eyeclosed(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EYECLOSED, "eyeclosed", $action);
    }

    public function attachment(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ATTACHMENT, "attachment", $action);
    }

    public function reset(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::RESET, "reset", $action);
    }

    public function apply(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::APPLY, "apply", $action);
    }

    public function search(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SEARCH, "search", $action);
    }

    public function help(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::HELP, "help", $action);
    }

    public function calendar($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::CALENDAR, "calendar", $action);
    }

    public function time($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TIME, "time", $action);
    }

    public function close($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::CLOSE, "close", $action);
    }

    public function more($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::MORE, "show_more", $action);
    }

    public function disclosure($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DISCLOSURE, "disclose", $action);
    }

    public function language(?string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LANGUAGE, "switch_language", $action);
    }

    public function login(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOGIN, "log_in", $action);
    }

    public function logout(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOGOUT, "log_out", $action);
    }

    public function bulletlist(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BULLETLIST, "bulletlist_action", $action);
    }

    public function numberedlist(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NUMBEREDLIST, "numberedlist_action", $action);
    }

    public function listindent(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LISTINDENT, "listindent", $action);
    }

    public function listoutdent(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LISTOUTDENT, "listoutdent", $action);
    }

    public function filter(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::FILTER, "filter", $action);
    }

    public function collapseHorizontal(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE_HORIZONTAL, "collapse/back", $action);
    }

    public function header(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::HEADER, "header_action", $action);
    }

    public function italic(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ITALIC, "italic_action", $action);
    }

    public function bold(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BOLD, "bold_action", $action);
    }

    public function link(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LINK, "link_action", $action);
    }

    public function launch(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LAUNCH, "launch", $action);
    }

    public function enlarge(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ENLARGE, "enlarge", $action);
    }

    public function listView(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LIST_VIEW, "list_view", $action);
    }

    public function preview(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::PREVIEW, "preview", $action);
    }

    public function sort(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT, "sort", $action);
    }

    public function columnSelection(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLUMN_SELECTION, "column_selection", $action);
    }

    public function tileView(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TILE_VIEW, "tile_view", $action);
    }
}
