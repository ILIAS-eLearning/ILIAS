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

/**
 * Container user filer. This holds the current filter data being used for
 * filtering the objects being presented.
 *
 * Currently a plain assoc array as retrieved by $filter->getData
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerUserFilter
{
    protected ?array $data;

    public function __construct(?array $data)
    {
        $this->data = $data;
    }

    public function getData() : ?array
    {
        return $this->data;
    }

    public function isEmpty() : bool
    {
        $empty = true;
        if (is_array($this->data)) {
            foreach ($this->data as $d) {
                if (trim($d) !== "") {
                    $empty = false;
                }
            }
        }
        return $empty;
    }
}
