<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Utilities/classes/class.ilStr.php';

/**
 * Class ilAssQuestionSkillAssignmentRegistry
 */
class ilAssQuestionSkillAssignmentRegistry
{
    const DEFAULT_CHUNK_SIZE = 1000;

    /**
     * @var \ilSetting
     */
    protected $settings;

    /**
     * @var int
     */
    protected $chunkSize = self::DEFAULT_CHUNK_SIZE;

    /**
     * ilAssQuestionSkillAssignmentRegistry constructor.
     * @param \ilSetting $setting
     */
    public function __construct(\ilSetting $setting)
    {
        $this->settings = $setting;
    }

    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     * @throws \InvalidArgumentException
     */
    public function setChunkSize($chunkSize)
    {
        if (!is_numeric($chunkSize) || $chunkSize <= 0) {
            throw new \InvalidArgumentException(sprintf("The passed chunk size is not a valid/supported integer: %s", var_export($chunkSize, true)));
        }

        $this->chunkSize = $chunkSize;
    }

    /**
     * @param string $key
     * @return int
     */
    protected function getNumberOfChunksByKey($key)
    {
        return (int) $this->settings->get($key . '_num', 0);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getStringifiedImports($key, $default = null)
    {
        $value = '';

        for ($i = 1, $numberOfChunks = $this->getNumberOfChunksByKey($key); $i <= $numberOfChunks; $i++) {
            $value .= $this->settings->get($key . '_' . $i);
        }

        return \ilStr::strLen($value) > 0 ? $value : $default;
    }

    /**
     * @param string $key
     * @param string $value A serialized value
     */
    public function setStringifiedImports($key, $value)
    {
        $i = 0;

        while (\ilStr::strLen($value) > 0) {
            ++$i;

            $valueToStore = \ilStr::subStr($value, 0, $this->getChunkSize());
            $this->settings->set($key . '_' . $i, $valueToStore);

            $truncatedValue = \ilStr::subStr($value, $this->getChunkSize(), \ilStr::strLen($value) - $this->getChunkSize());

            $value = $truncatedValue;
        }

        if ($i > 0) {
            $this->settings->set($key . '_num', $i);
        }
    }

    /**
     * @param string $key
     */
    public function deleteStringifiedImports($key)
    {
        for ($i = 1, $numberOfChunks = $this->getNumberOfChunksByKey($key); $i <= $numberOfChunks; $i++) {
            $this->settings->delete($key . '_' . $i);
        }

        $this->settings->delete($key . '_num');
    }
}
