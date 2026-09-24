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
 * The result a tool reported for a user of an LTI object, as the learning progress and the certificate
 * read it. The LTI versions write it through their own classes; this one only reads.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIConsumerResult
{
    private const string TABLE_NAME = 'lti_consumer_results';

    private function __construct(
        private readonly int $obj_id,
        private readonly int $usr_id,
        private readonly ?float $result
    ) {
    }

    /**
     * The result of the user for the object, null when the tool has not reported any.
     */
    public static function getByKeys(int $obj_id, int $usr_id): ?self
    {
        global $DIC;

        $db = $DIC->database();
        $row = $db->fetchAssoc($db->queryF(
            'SELECT result FROM ' . self::TABLE_NAME . ' WHERE obj_id = %s AND usr_id = %s',
            ['integer', 'integer'],
            [$obj_id, $usr_id]
        ));
        if ($row === null) {
            return null;
        }

        return new self($obj_id, $usr_id, $row['result'] === null ? null : (float) $row['result']);
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    /**
     * The share of the maximum result, between 0 and 1, null when the user only launched the tool.
     */
    public function getResult(): ?float
    {
        return $this->result;
    }
}
