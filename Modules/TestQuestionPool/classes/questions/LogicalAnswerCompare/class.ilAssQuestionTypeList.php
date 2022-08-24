<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/TestQuestionPool
 */
class ilAssQuestionTypeList implements Iterator
{
    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * @var ilDBPdo
     */
    protected $db;

    /**
     * @var array[ilAssQuestionType]
     */
    protected $types = array();

    /**
     * ilAssQuestionTypeList constructor.
     */
    public function __construct()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->db = $DIC['ilDB'];
    }

    public function load(): void
    {
        $res = $this->db->query("SELECT * FROM qpl_qst_type");

        while ($row = $this->db->fetchAssoc($res)) {
            $row = ilAssQuestionType::completeMissingPluginName($row);

            $qstType = new ilAssQuestionType();
            $qstType->setId($row['question_type_id']);
            $qstType->setTag($row['type_tag']);
            $qstType->setPlugin($row['plugin']);
            $qstType->setPluginName($row['plugin_name']);
            $this->types[] = $qstType;
        }
    }


    public function existByTag($questionTypeTag): bool
    {
        return $this->getByTag($questionTypeTag) instanceof ilAssQuestionType;
    }

    /**
     * @param $questionTypeTag
     * @return ilAssQuestionType|null
     */
    public function getByTag($questionTypeTag): ?ilAssQuestionType
    {
        foreach ($this as $qstType) {
            if ($qstType->getTag() != $questionTypeTag) {
                continue;
            }

            return $qstType;
        }

        return null;
    }

    /** @return ilAssQuestionType */
    public function current(): ilAssQuestionType
    {
        return current($this->types);
    }
    /** @return ilAssQuestionType */
    public function next(): ilAssQuestionType
    {
        return next($this->types);
    }
    /** @return string */
    public function key(): string
    {
        return key($this->types);
    }
    /** @return bool */
    public function valid(): bool
    {
        return key($this->types) !== null;
    }
    /** @return ilAssQuestionType */
    public function rewind(): ilAssQuestionType
    {
        return reset($this->types);
    }

    /**
     * @param string $questionTypeTag
     * @return bool
     */
    public static function isImportable($questionTypeTag): bool
    {
        if (!self::getInstance()->existByTag($questionTypeTag)) {
            return false;
        }

        return self::getInstance()->getByTag($questionTypeTag)->isImportable();
    }

    /**
     * @return ilAssQuestionTypeList
     */
    public static function getInstance(): ?ilAssQuestionTypeList
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->load();
        }

        return self::$instance;
    }
}
