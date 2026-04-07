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
    public function settings(): Glyph
    {
        return new Glyph(G\Glyph::SETTINGS, "settings");
    }

    public function collapse(): Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE, "collapse_content");
    }

    public function expand(): Glyph
    {
        return new Glyph(G\Glyph::EXPAND, "expand_content");
    }

    public function add(): Glyph
    {
        return new Glyph(G\Glyph::ADD, "add");
    }

    public function remove(): Glyph
    {
        return new Glyph(G\Glyph::REMOVE, "remove");
    }

    public function up(): Glyph
    {
        return new Glyph(G\Glyph::UP, "up");
    }

    public function down(): Glyph
    {
        return new Glyph(G\Glyph::DOWN, "down");
    }

    public function back(): Glyph
    {
        return new Glyph(G\Glyph::BACK, "back");
    }

    public function next(): Glyph
    {
        return new Glyph(G\Glyph::NEXT, "next");
    }

    public function sortAscending(): Glyph
    {
        return new Glyph(G\Glyph::SORT_ASCENDING, "sort_ascending");
    }

    public function briefcase(): Glyph
    {
        return new Glyph(G\Glyph::BRIEFCASE, "briefcase");
    }

    public function sortDescending(): Glyph
    {
        return new Glyph(G\Glyph::SORT_DESCENDING, "sort_descending");
    }

    public function user(): Glyph
    {
        return new Glyph(G\Glyph::USER, "show_who_is_online");
    }

    public function mail(): Glyph
    {
        return new Glyph(G\Glyph::MAIL, "mail");
    }

    public function notification(): Glyph
    {
        return new Glyph(G\Glyph::NOTIFICATION, "notifications");
    }

    public function tag(): Glyph
    {
        return new Glyph(G\Glyph::TAG, "tags");
    }

    public function note(): Glyph
    {
        return new Glyph(G\Glyph::NOTE, "notes");
    }

    public function comment(): Glyph
    {
        return new Glyph(G\Glyph::COMMENT, "comments");
    }

    public function like(): Glyph
    {
        return new Glyph(G\Glyph::LIKE, "like");
    }

    public function love(): Glyph
    {
        return new Glyph(G\Glyph::LOVE, "love");
    }

    public function dislike(): Glyph
    {
        return new Glyph(G\Glyph::DISLIKE, "dislike");
    }

    public function laugh(): Glyph
    {
        return new Glyph(G\Glyph::LAUGH, "laugh");
    }

    public function astounded(): Glyph
    {
        return new Glyph(G\Glyph::ASTOUNDED, "astounded");
    }

    public function sad(): Glyph
    {
        return new Glyph(G\Glyph::SAD, "sad");
    }

    public function angry(): Glyph
    {
        return new Glyph(G\Glyph::ANGRY, "angry");
    }

    public function eyeopen(): Glyph
    {
        return new Glyph(G\Glyph::EYEOPEN, "eyeopened");
    }

    public function eyeclosed(): Glyph
    {
        return new Glyph(G\Glyph::EYECLOSED, "eyeclosed");
    }

    public function attachment(): Glyph
    {
        return new Glyph(G\Glyph::ATTACHMENT, "attachment");
    }

    public function reset(): Glyph
    {
        return new Glyph(G\Glyph::RESET, "reset");
    }

    public function apply(): Glyph
    {
        return new Glyph(G\Glyph::APPLY, "apply");
    }

    public function search(): Glyph
    {
        return new Glyph(G\Glyph::SEARCH, "search");
    }

    public function help(): Glyph
    {
        return new Glyph(G\Glyph::HELP, "help");
    }

    public function calendar(): Glyph
    {
        return new Glyph(G\Glyph::CALENDAR, "calendar");
    }

    public function time(): Glyph
    {
        return new Glyph(G\Glyph::TIME, "time");
    }

    public function close(): Glyph
    {
        return new Glyph(G\Glyph::CLOSE, "close");
    }

    public function more(): Glyph
    {
        return new Glyph(G\Glyph::MORE, "show_more");
    }

    public function disclosure(): Glyph
    {
        return new Glyph(G\Glyph::DISCLOSURE, "disclose");
    }

    public function language(): Glyph
    {
        return new Glyph(G\Glyph::LANGUAGE, "switch_language");
    }

    public function login(): Glyph
    {
        return new Glyph(G\Glyph::LOGIN, "log_in");
    }

    public function logout(): Glyph
    {
        return new Glyph(G\Glyph::LOGOUT, "log_out");
    }

    public function bulletlist(): Glyph
    {
        return new Glyph(G\Glyph::BULLETLIST, "bulletlist_action");
    }

    public function numberedlist(): Glyph
    {
        return new Glyph(G\Glyph::NUMBEREDLIST, "numberedlist_action");
    }

    public function listindent(): Glyph
    {
        return new Glyph(G\Glyph::LISTINDENT, "listindent");
    }

    public function listoutdent(): Glyph
    {
        return new Glyph(G\Glyph::LISTOUTDENT, "listoutdent");
    }

    public function filter(): Glyph
    {
        return new Glyph(G\Glyph::FILTER, "filter");
    }

    public function collapseHorizontal(): Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE_HORIZONTAL, "collapse/back");
    }

    public function header(): Glyph
    {
        return new Glyph(G\Glyph::HEADER, "header_action");
    }

    public function italic(): Glyph
    {
        return new Glyph(G\Glyph::ITALIC, "italic_action");
    }

    public function bold(): Glyph
    {
        return new Glyph(G\Glyph::BOLD, "bold_action");
    }

    public function link(): Glyph
    {
        return new Glyph(G\Glyph::LINK, "link_action");
    }

    public function launch(): Glyph
    {
        return new Glyph(G\Glyph::LAUNCH, "launch");
    }

    public function enlarge(): Glyph
    {
        return new Glyph(G\Glyph::ENLARGE, "enlarge");
    }

    public function listView(): Glyph
    {
        return new Glyph(G\Glyph::LIST_VIEW, "list_view");
    }

    public function preview(): Glyph
    {
        return new Glyph(G\Glyph::PREVIEW, "preview");
    }

    public function sort(): Glyph
    {
        return new Glyph(G\Glyph::SORT, "sort");
    }

    public function columnSelection(): Glyph
    {
        return new Glyph(G\Glyph::COLUMN_SELECTION, "column_selection");
    }

    public function tileView(): Glyph
    {
        return new Glyph(G\Glyph::TILE_VIEW, "tile_view");
    }

    public function dragHandle(): G\Glyph
    {
        return new Glyph(G\Glyph::DRAG_HANDLE, "drag_handle");
    }

    public function checked(): G\Glyph
    {
        return new Glyph(G\Glyph::CHECKED, "checked");
    }

    public function unchecked(): G\Glyph
    {
        return new Glyph(G\Glyph::UNCHECKED, "unchecked");
    }
}
