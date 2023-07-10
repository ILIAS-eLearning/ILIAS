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
 * Class ilAssQuestionSkillAssignmentRegistry
 */
class ilAssQuestionSkillAssignmentRegistry
{
    public const DEFAULT_CHUNK_SIZE = 1000;

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
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     * @throws \InvalidArgumentException
     */
    public function setChunkSize($chunkSize): void
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
    protected function getNumberOfChunksByKey($key): int
    {
        return (int) $this->settings->get($key . '_num', '0');
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
    public function setStringifiedImports($key, $value): void
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
    public function deleteStringifiedImports($key): void
    {
        for ($i = 1, $numberOfChunks = $this->getNumberOfChunksByKey($key); $i <= $numberOfChunks; $i++) {
            $this->settings->delete($key . '_' . $i);
        }

        $this->settings->delete($key . '_num');
    }
}
