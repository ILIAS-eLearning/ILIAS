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

/**
 * Class ilCtrlStructureCidGenerator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlStructureCidGenerator
{
    /**
     * @var int
     */
    private int $index;

    /**
     * ilCtrlStructureCidGenerator Constructor
     *
     * @param int $starting_index
     */
    public function __construct(int $starting_index = 0)
    {
        $this->index = $starting_index;
    }

    /**
     * Returns the index of a given cid.
     *
     * @param string $cid
     * @return int
     */
    public function getIndexByCid(string $cid): int
    {
        if (strpos($cid, '-') === 0) {
            $inverted_cid = str_replace('-', '', $cid);
            $index = (int) base_convert($inverted_cid, 36, 10);

            return $this->invertIndex($index);
        }

        return (int) base_convert($cid, 36, 10);
    }

    /**
     * Returns the cid for a given index.
     *
     * @param int $index
     * @return string
     */
    public function getCidByIndex(int $index): string
    {
        if (0 > $index) {
            return '-' . base_convert((string) $this->invertIndex($index), 10, 36);
        }

        return base_convert((string) $index, 10, 36);
    }

    /**
     * Returns the next available cid.
     *
     * @return string
     */
    public function getCid(): string
    {
        return $this->getCidByIndex($this->index++);
    }

    /**
     * Helper function that inverts an integer value.
     *
     * @param int $index
     * @return int
     */
    private function invertIndex(int $index): int
    {
        return (-1 * $index);
    }
}
