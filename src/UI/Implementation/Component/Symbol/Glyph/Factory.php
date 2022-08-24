<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Symbol\Glyph;

use ILIAS\UI\Component\Symbol\Glyph as G;

class Factory implements G\Factory
{
    /**
     * @inheritdoc
     */
    public function settings(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SETTINGS, "settings", $action);
    }

    /**
     * @inheritdoc
     */
    public function collapse(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE, "collapse_content", $action);
    }

    /**
     * @inheritdoc
     */
    public function expand(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EXPAND, "expand_content", $action);
    }

    /**
     * @inheritdoc
     */
    public function add(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ADD, "add", $action);
    }

    /**
     * @inheritdoc
     */
    public function remove(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::REMOVE, "remove", $action);
    }

    /**
     * @inheritdoc
     */
    public function up(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::UP, "up", $action);
    }

    /**
     * @inheritdoc
     */
    public function down(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DOWN, "down", $action);
    }

    /**
     * @inheritdoc
     */
    public function back(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BACK, "back", $action);
    }

    /**
     * @inheritdoc
     */
    public function next(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NEXT, "next", $action);
    }


    /**
     * @inheritdoc
     */
    public function sortAscending(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT_ASCENDING, "sort_ascending", $action);
    }

    /**
     * @inheritdoc
     */
    public function briefcase(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BRIEFCASE, "briefcase", $action);
    }

    /**
     * @inheritdoc
     */
    public function sortDescending(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SORT_DESCENDING, "sort_descending", $action);
    }

    /**
     * @inheritdoc
     */
    public function user(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::USER, "show_who_is_online", $action);
    }

    /**
     * @inheritdoc
     */
    public function mail(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::MAIL, "mail", $action);
    }

    /**
     * @inheritdoc
     */
    public function notification(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NOTIFICATION, "notifications", $action);
    }

    /**
     * @inheritdoc
     */
    public function tag(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TAG, "tags", $action);
    }

    /**
     * @inheritdoc
     */
    public function note(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NOTE, "notes", $action);
    }

    /**
     * @inheritdoc
     */
    public function comment(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COMMENT, "comments", $action);
    }

    /**
     * @inheritdoc
     */
    public function like(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LIKE, "like", $action);
    }

    /**
     * @inheritdoc
     */
    public function love(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOVE, "love", $action);
    }

    /**
     * @inheritdoc
     */
    public function dislike(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DISLIKE, "dislike", $action);
    }

    /**
     * @inheritdoc
     */
    public function laugh(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LAUGH, "laugh", $action);
    }

    /**
     * @inheritdoc
     */
    public function astounded(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ASTOUNDED, "astounded", $action);
    }

    /**
     * @inheritdoc
     */
    public function sad(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SAD, "sad", $action);
    }

    /**
     * @inheritdoc
     */
    public function angry(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ANGRY, "angry", $action);
    }

    /**
     * @inheritdoc
     */
    public function eyeopen(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EYEOPEN, "eyeopened", $action);
    }

    /**
     * @inheritdoc
     */
    public function eyeclosed(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::EYECLOSED, "eyeclosed", $action);
    }

    /**
     * @inheritdoc
     */
    public function attachment(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::ATTACHMENT, "attachment", $action);
    }

    /**
     * @inheritdoc
     */
    public function reset(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::RESET, "reset", $action);
    }

    /**
     * @inheritdoc
     */
    public function apply(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::APPLY, "apply", $action);
    }

    /**
     * @inheritdoc
     */
    public function search(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::SEARCH, "search", $action);
    }

    /**
     * @inheritdoc
     */
    public function help(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::HELP, "help", $action);
    }

    /**
    * @inheritdoc
    */
    public function calendar($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::CALENDAR, "calendar", $action);
    }

    /**
     * @inheritdoc
     */
    public function time($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::TIME, "time", $action);
    }

    /**
     * @inheritdoc
     */
    public function close($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::CLOSE, "close", $action);
    }

    /**
     * @inheritdoc
     */
    public function more($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::MORE, "show_more", $action);
    }

    /**
     * @inheritdoc
     */
    public function disclosure($action = null): G\Glyph
    {
        return new Glyph(G\Glyph::DISCLOSURE, "disclose", $action);
    }

    /**
     * @inheritdoc
     */
    public function language(?string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LANGUAGE, "switch_language", $action);
    }

    /**
     * @inheritdoc
     */
    public function login(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOGIN, "log_in", $action);
    }

    /**
     * @inheritdoc
     */
    public function logout(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LOGOUT, "log_out", $action);
    }

    /**
     * @inheritdoc
     */
    public function bulletlist(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::BULLETLIST, "bulletlist", $action);
    }

    /**
     * @inheritdoc
     */
    public function numberedlist(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::NUMBEREDLIST, "numberedlist", $action);
    }

    /**
     * @inheritdoc
     */
    public function listindent(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LISTINDENT, "listindent", $action);
    }

    /**
     * @inheritdoc
     */
    public function listoutdent(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::LISTOUTDENT, "listoutdent", $action);
    }

    /**
     * @inheritdoc
     */
    public function filter(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::FILTER, "filter", $action);
    }

    /**
     * @inheritdoc
     */
    public function collapseHorizontal(string $action = null): G\Glyph
    {
        return new Glyph(G\Glyph::COLLAPSE_HORIZONTAL, "collapse/back", $action);
    }
}
