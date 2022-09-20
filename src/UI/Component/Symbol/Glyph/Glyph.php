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

namespace ILIAS\UI\Component\Symbol\Glyph;

use ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * This describes how a glyph could be modified during construction of UI.
 */
interface Glyph extends Symbol, Clickable
{
    // Types of glyphs:
    public const SETTINGS = "settings";
    public const EXPAND = "expand";
    public const COLLAPSE = "collapse";
    public const COLLAPSE_HORIZONTAL = "collapsehorizontal";
    public const ADD = "add";
    public const REMOVE = "remove";
    public const UP = "up";
    public const DOWN = "down";
    public const BACK = "back";
    public const NEXT = "next";
    public const SORT_ASCENDING = "sortAscending";
    public const SORT_DESCENDING = "sortDescending";
    public const USER = "user";
    public const MAIL = "mail";
    public const NOTIFICATION = "notification";
    public const TAG = "tag";
    public const NOTE = "note";
    public const COMMENT = "comment";
    public const BRIEFCASE = "briefcase";
    public const LIKE = "like";
    public const LOVE = "love";
    public const DISLIKE = "dislike";
    public const LAUGH = "laugh";
    public const ASTOUNDED = "astounded";
    public const SAD = "sad";
    public const ANGRY = "angry";
    public const EYEOPEN = "eyeopen";
    public const EYECLOSED = "eyeclosed";
    public const ATTACHMENT = "attachment";
    public const RESET = "reset";
    public const APPLY = "apply";
    public const SEARCH = "search";
    public const HELP = "help";
    public const CALENDAR = "calendar";
    public const TIME = "time";
    public const CLOSE = "close";
    public const MORE = "more";
    public const DISCLOSURE = "disclosure";
    public const LANGUAGE = "language";
    public const LOGIN = "login";
    public const LOGOUT = "logout";
    public const BULLETLIST = "bulletlist";
    public const NUMBEREDLIST = "numberedlist";
    public const LISTINDENT = "listindent";
    public const LISTOUTDENT = "listoutdent";
    public const FILTER = "filter";

    /**
     * Get the type of the glyph.
     */
    public function getType(): string;

    /**
     * Get the action on the glyph.
     */
    public function getAction(): ?string;

    /**
     * Get all counters attached to this glyph.
     *
     * @return	Counter[]
     */
    public function getCounters(): array;

    /**
     * Get a glyph like this, but with a counter on it.
     *
     * If there already is a counter of the given counter type, replace that
     * counter by the new one.
     */
    public function withCounter(Counter $counter): Glyph;

    /**
     * Returns whether the Glyph is highlighted.
     */
    public function isHighlighted(): bool;

    /**
     * Get a Glyph like this with a highlight.
     */
    public function withHighlight(): Glyph;

    /**
     * Get to know if the glyph is activated.
     */
    public function isActive(): bool;

    /**
     * Get a glyph like this, but action should be unavailable atm.
     *
     * The glyph will still have an action afterwards, this might be useful
     * at some point where we want to reactivate the glyph client side.
     */
    public function withUnavailableAction(): Glyph;

    /**
    * Get a Glyph like this with an action.
    */
    public function withAction(string $action): Glyph;
}
