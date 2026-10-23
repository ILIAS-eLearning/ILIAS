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
 * read it. The LTI versions write the result through their own classes; this one only reads it and
 * keeps the launches.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolResult
{
    private const string TABLE_NAME = 'lti_consumer_results';

    private function __construct(private readonly ?float $result)
    {
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

        return new self($row['result'] === null ? null : (float) $row['result']);
    }

    /**
     * A launch starts the attempt of the user, in progress until the tool reports a result. The learning
     * progress is updated at once, the read event of the object was tracked before the launch.
     */
    public static function start(int $obj_id, int $usr_id): void
    {
        global $DIC;

        $db = $DIC->database();
        if (self::getByKeys($obj_id, $usr_id) === null) {
            $db->insert(self::TABLE_NAME, [
                'id' => ['integer', $db->nextId(self::TABLE_NAME)],
                'obj_id' => ['integer', $obj_id],
                'usr_id' => ['integer', $usr_id],
                'result' => ['float', null],
                'attended' => ['integer', 0],
            ]);
        }
        ilLPStatusWrapper::_updateStatus($obj_id, $usr_id);
    }

    /**
     * The share of the maximum result, between 0 and 1, null when the user only launched the tool.
     */
    public function getResult(): ?float
    {
        return $this->result;
    }
}
