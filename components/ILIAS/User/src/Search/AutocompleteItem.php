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

namespace ILIAS\User\Search;

interface AutocompleteItem
{
    /**
     * MUST return an array containing three properties "value", "display", and
     * "searchBy". The property "value" MUST be save to transmit as url-parameter.
     * The returned tags will then again be filtered by the value in the property
     * "searchBy". If you need to show the tag even if you are not allowed to
     * divulge the full value of the field the search string was found in, you
     * can simply reuse the search term here. See the {@see DefaultAutocompleteItem}
     * for an implementation of this.
     *
     * @return array{value: string, display: string, searchBy: string}
     */
    public function getTagArray(): array;
}
