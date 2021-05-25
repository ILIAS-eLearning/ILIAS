<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Symbol\Glyph;

use \ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Component\Clickable;

/**
 * This describes how a glyph could be modified during construction of UI.
 */
interface Glyph extends \ILIAS\UI\Component\Symbol\Symbol, Clickable
{
    // Types of glyphs:
    public const SETTINGS = "settings";
    public const EXPAND = "expand";
    public const COLLAPSE = "collapse";
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
     *
     * @return	string
     */
    public function getType();

    /**
     * Get the action on the glyph.
     *
     * @return	string|null
     */
    public function getAction();

    /**
     * Get all counters attached to this glyph.
     *
     * @return	Counter[]
     */
    public function getCounters();

    /**
     * Get a glyph like this, but with a counter on it.
     *
     * If there already is a counter of the given counter type, replace that
     * counter by the new one.
     *
     * @param	Counter $counter
     * @return	Glyph
     */
    public function withCounter(Counter $counter);


    /**
     * Returns whether the Glyph is highlighted.
     *
     * @return bool
     */
    public function isHighlighted();

    /**
     * Get a Glyph like this with a highlight.
     *
     * @param bool|true $highlighted
     * @return mixed
     */
    public function withHighlight();

    /**
     * Get to know if the glyph is activated.
     *
     * @return 	bool
     */
    public function isActive();

    /**
     * Get a glyph like this, but action should be unavailable atm.
     *
     * The glyph will still have an action afterwards, this might be useful
     * at some point where we want to reactivate the glyph client side.
     *
     * @return Glyph
     */
    public function withUnavailableAction();

    /**
    * Get a Glyph like this with an action.
    *
    * @param string $action
    * @return mixed
    */
    public function withAction($action);
}
