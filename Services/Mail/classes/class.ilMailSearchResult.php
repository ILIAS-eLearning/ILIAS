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

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 */
class ilMailSearchResult
{
    /** @var array[] */
    protected array $result = [];

    public function __construct()
    {
    }

    public function addItem(int $id, array $fields): void
    {
        $this->result[$id] = $fields;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return array_keys($this->result);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getFields(int $id): array
    {
        if (!isset($this->result[$id])) {
            throw new OutOfBoundsException('mail_missing_result_fields');
        }

        return $this->result[$id];
    }
}
