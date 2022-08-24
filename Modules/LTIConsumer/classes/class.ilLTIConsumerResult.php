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
 * Class ilObjLTIConsumerLaunch
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerResult
{
    /**
     * @var integer
     */
    public int $id;

    /**
     * @var integer
     */
    public int $obj_id;

    /**
     * @var integer
     */
    public int $usr_id;

    /**
     * @var float|null
     */
    public ?float $result = null;

    /**
     * Get a result by id
     */
    public static function getById(int $a_id): ?ilLTIConsumerResult
    {
        global $DIC;

        $query = 'SELECT * FROM lti_consumer_results'
            . ' WHERE id = ' . $DIC->database()->quote($a_id, 'integer');

        $res = $DIC->database()->query($query);
        if ($row = $DIC->database()->fetchAssoc($res)) {
            $resObj = new ilLTIConsumerResult();
            $resObj->fillData($row);
            return $resObj;
        } else {
            return null;
        }
    }

    /**
     * Get a result by object and user key
     * @return ilLTIConsumerResult
     */
    public static function getByKeys(int $a_obj_id, int $a_usr_id, ?bool $a_create = false): ?ilLTIConsumerResult
    {
        global $DIC;

        $query = 'SELECT * FROM lti_consumer_results'
            . ' WHERE obj_id = ' . $DIC->database()->quote($a_obj_id, 'integer')
            . ' AND usr_id = ' . $DIC->database()->quote($a_usr_id, 'integer');

        $res = $DIC->database()->query($query);
        if ($row = $DIC->database()->fetchAssoc($res)) {
            $resObj = new ilLTIConsumerResult();
            $resObj->fillData($row);
            return $resObj;
        } elseif ($a_create) {
            $resObj = new ilLTIConsumerResult();
            $resObj->obj_id = $a_obj_id;
            $resObj->usr_id = $a_usr_id;
            $resObj->result = null;
            $resObj->save();
            return $resObj;
        } else {
            return null;
        }
    }

    /**
     * Fill the properties with data from an array
     * @param array assoc data
     */
    protected function fillData(array $data): void
    {
        $this->id = (int) $data['id'];
        $this->obj_id = (int) $data['obj_id'];
        $this->usr_id = (int) $data['usr_id'];
        $this->result = $data['result'];
    }

    /**
     * Save a result object
     */
    public function save(): bool
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if (!isset($this->usr_id) || !isset($this->obj_id)) {
            return false;
        }
        if (!isset($this->id)) {
            $this->id = $DIC->database()->nextId('lti_consumer_results');
        }
        $DIC->database()->replace(
            'lti_consumer_results',
            array(
                'id' => array('integer', $this->id)
            ),
            array(
                'obj_id' => array('integer', $this->obj_id),
                'usr_id' => array('integer', $this->usr_id),
                'result' => array('float', $this->result)
            )
        );
        return true;
    }

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
    public static function getResultsForObject(int $objId): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = 'SELECT * FROM lti_consumer_results'
            . ' WHERE obj_id = ' . $DIC->database()->quote($objId, 'integer');

        $res = $DIC->database()->query($query);

        $results = [];

        if ($row = $DIC->database()->fetchAssoc($res)) {
            $resObj = new ilLTIConsumerResult();
            $resObj->fillData($row);

            $results[$resObj->getUsrId()] = $resObj;
        }

        return $results;
    }
}
