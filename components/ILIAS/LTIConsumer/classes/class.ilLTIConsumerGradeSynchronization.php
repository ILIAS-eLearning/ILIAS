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
 * Class ilObjLTIConsumerResult
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 *
 * @package components\ILIAS/LTIConsumer
 */
class ilLTIConsumerGradeSynchronization
{
    public int $id;
    public int $obj_id;
    public int $usr_id;

    /**
     * @var float|null
     */
    public ?float $result = null;

    /**
     * Fill the properties with data from an array
     * @param array assoc data
     */
    //    protected function fillData(array $data): void
    //    {
    //        $this->id = (int) $data['id'];
    //        $this->obj_id = (int) $data['obj_id'];
    //        $this->usr_id = (int) $data['usr_id'];
    //        $this->result = (float) $data['result'];
    //    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function getResult(): ?float
    {
        return $this->result;
    }

    /**
     * @param $objId
     * @return ilLTIConsumerResult[]
     */
    public static function getGradesForObject(int $objId, ?int $usrID = null): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = 'SELECT * FROM lti_consumer_grades'
            . ' WHERE obj_id = ' . $DIC->database()->quote($objId, 'integer');

        $res = $DIC->database()->query($query);

        $results = [];

        if ($row = $DIC->database()->fetchAssoc($res)) {
            $results[] = $row;
        }

        return $results;
    }
}
