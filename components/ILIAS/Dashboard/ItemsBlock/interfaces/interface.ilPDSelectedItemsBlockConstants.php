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

interface ilPDSelectedItemsBlockConstants
{
    public const VIEW_SELECTED_ITEMS = 0;
    public const VIEW_RECOMMENDED_CONTENT = 1;
    public const VIEW_MY_MEMBERSHIPS = 2;
    public const VIEW_LEARNING_SEQUENCES = 3;
    public const VIEW_MY_STUDYPROGRAMME = 4;

    public const SORT_BY_TYPE = 'type';
    public const SORT_BY_LOCATION = 'location';
    public const SORT_BY_START_DATE = 'start_date';
    public const SORT_BY_ALPHABET = 'alphabet';

    public const PRESENTATION_LIST = 'list';
    public const PRESENTATION_TILE = 'tile';

    public const AVAILABLE_VIEWS = [
        self::VIEW_SELECTED_ITEMS,
        self::VIEW_RECOMMENDED_CONTENT,
        self::VIEW_MY_MEMBERSHIPS,
        self::VIEW_LEARNING_SEQUENCES,
        self::VIEW_MY_STUDYPROGRAMME,
    ];
    public const VIEW_NAMES = [
        self::VIEW_SELECTED_ITEMS => 'favourites',
        self::VIEW_RECOMMENDED_CONTENT => 'recommended_content',
        self::VIEW_MY_MEMBERSHIPS => 'memberships',
        self::VIEW_LEARNING_SEQUENCES => 'learning_sequences',
        self::VIEW_MY_STUDYPROGRAMME => 'study_programmes',
    ];
    public const AVAILABLE_SORT_OPTIONS_BY_VIEW = [
        self::VIEW_SELECTED_ITEMS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_RECOMMENDED_CONTENT => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_ALPHABET,
            self::SORT_BY_START_DATE,
        ],
        self::VIEW_LEARNING_SEQUENCES => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_MY_STUDYPROGRAMME => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_ALPHABET,
        ],
    ];
    public const AVAILABLE_PRESENTATION_BY_VIEW = [
        self::VIEW_SELECTED_ITEMS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_RECOMMENDED_CONTENT => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_LEARNING_SEQUENCES => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_STUDYPROGRAMME => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],

    ];
}
