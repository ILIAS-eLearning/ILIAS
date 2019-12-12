<?php

declare(strict_types=1);

/**
 * Persistence for online/activation period
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearningSequenceActivationDB
{
    const TABLE_NAME = 'lso_activation';

    /**
     * @var ilDBInterface
     */
    protected $database;

    public function __construct(ilDBInterface $database)
    {
        $this->database = $database;
    }

    public function getActivationForRefId(int $ref_id) : ilLearningSequenceActivation
    {
        $data = $this->select($ref_id);
        if (count($data) == 0) {
            $settings = $this->buildActivationSettings($ref_id);
            $this->insert($settings);
        } else {
            if ($data['activation_start_ts']) {
                $start = new \DateTime();
                $start->setTimestamp((int) $data['activation_start_ts']);
            } else {
                $start = null;
            }

            if ($data['activation_end_ts']) {
                $end = new \DateTime();
                $end->setTimestamp((int) $data['activation_end_ts']);
            } else {
                $end = null;
            }

            $settings = $this->buildActivationSettings(
                (int) $data['ref_id'],
                (bool) $data['online'],
                (bool) $data['effective_online'],
                $start,
                $end
            );
        }

        return $settings;
    }

    public function deleteForRefId(int $ref_id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id = " . $this->database->quote($ref_id, "integer") . PHP_EOL
        ;
        $this->database->manipulate($query);
    }

    public function store(ilLearningSequenceActivation $settings)
    {
        $where = array(
            "ref_id" => array("integer", $settings->getRefId())
        );

        $start = $settings->getActivationStart();
        $end = $settings->getActivationEnd();

        if ($start) {
            $start = $start->getTimestamp();
        }
        if ($end) {
            $end = $end->getTimestamp();
        }
        $values = array(
            "online" => array("integer", $settings->getIsOnline()),
            "activation_start_ts" => array("integer", $start),
            "activation_end_ts" => array("integer", $end)
        );
        $this->database->update(static::TABLE_NAME, $values, $where);
    }

    protected function insert(ilLearningSequenceActivation $settings)
    {
        $start = $settings->getActivationStart();
        $end = $settings->getActivationEnd();
        if ($start) {
            $start = $start->getTimestamp();
        }
        if ($end) {
            $end = $end->getTimestamp();
        }
        $values = array(
            "ref_id" => array("integer", $settings->getRefId()),
            "online" => array("integer", $settings->getIsOnline()),
            "effective_online" => array("integer", $settings->getEffectiveOnlineStatus()),
            "activation_start_ts" => array("integer", $start),
            "activation_end_ts" => array("integer", $end)
        );
        $this->database->insert(static::TABLE_NAME, $values);
    }

    protected function select(int $ref_id) : array
    {
        $ret = [];
        $query =
             "SELECT ref_id, online, effective_online, activation_start_ts, activation_end_ts" . PHP_EOL
            . "FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE ref_id = " . $this->database->quote($ref_id, "integer") . PHP_EOL
        ;

        $result = $this->database->query($query);

        if ($result->numRows() !== 0) {
            $ret = $this->database->fetchAssoc($result);
        }

        return $ret;
    }

    protected function buildActivationSettings(
        int $ref_id,
        bool $online = false,
        bool $effective_online = false,
        \DateTime $activation_start = null,
        \DateTime $activation_end = null
    ) : ilLearningSequenceActivation {
        return new ilLearningSequenceActivation(
            $ref_id,
            $online,
            $effective_online,
            $activation_start,
            $activation_end
        );
    }

    public function setEffectiveOnlineStatus(int $ref_id, bool $status)
    {
        $where = array(
            "ref_id" => array("integer", $ref_id)
        );

        $values = array(
            "effective_online" => array("integer", $status),
        );

        $this->database->update(static::TABLE_NAME, $values, $where);
    }
}
