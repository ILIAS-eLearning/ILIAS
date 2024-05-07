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
}
