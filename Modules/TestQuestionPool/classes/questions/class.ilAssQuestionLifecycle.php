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
 * Class ilAssQuestionLifecycle
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionLifecycle
{
    public const DRAFT = 'draft';
    public const REVIEW = 'review';
    public const REJECTED = 'rejected';
    public const FINAL = 'final';
    public const SHARABLE = 'sharable';
    public const OUTDATED = 'outdated';

    protected string $identifier;

    private function __construct()
    {
        $this->setIdentifier(self::DRAFT);
    }

    public function getIdentifier() : string
    {
        return $this->identifier;
    }

    private function setIdentifier(string $identifier) : void
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string[]
     */
    public function getValidIdentifiers() : array
    {
        return [self::DRAFT, self::REVIEW, self::REJECTED, self::FINAL, self::SHARABLE, self::OUTDATED];
    }

    public function getMappedLomLifecycle() : string
    {
        switch ($this->getIdentifier()) {
            case self::OUTDATED:
                return ilAssQuestionLomLifecycle::UNAVAILABLE;

            case self::SHARABLE:
            case self::FINAL:
                return ilAssQuestionLomLifecycle::FINAL;

            case self::REJECTED:
            case self::REVIEW:
            case self::DRAFT:
            default:
                return ilAssQuestionLomLifecycle::DRAFT;
        }
    }

    public function getTranslation(ilLanguage $lng) : string
    {
        return $this->getTranslationByIdentifier($lng, $this->getIdentifier());
    }

    private function getTranslationByIdentifier(ilLanguage $lng, string $identifier) : string
    {
        switch ($identifier) {
            case self::DRAFT:
                return $lng->txt('qst_lifecycle_draft');

            case self::REVIEW:
                return $lng->txt('qst_lifecycle_review');

            case self::REJECTED:
                return $lng->txt('qst_lifecycle_rejected');

            case self::FINAL:
                return $lng->txt('qst_lifecycle_final');

            case self::SHARABLE:
                return $lng->txt('qst_lifecycle_sharable');

            case self::OUTDATED:
                return $lng->txt('qst_lifecycle_outdated');

            default:
                return '';
        }
    }

    /**
     * @param ilLanguage $lng
     * @return array<string, string>
     */
    public function getSelectOptions(ilLanguage $lng) : array
    {
        $selectOptions = [];

        foreach ($this->getValidIdentifiers() as $identifier) {
            $selectOptions[$identifier] = $this->getTranslationByIdentifier($lng, $identifier);
        }

        return $selectOptions;
    }

    /**
     * @param mixed $identifier
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    private function validateIdentifier($identifier) : void
    {
        if (!in_array($identifier, $this->getValidIdentifiers(), true)) {
            throw new ilTestQuestionPoolInvalidArgumentException(
                'Invalid ilias lifecycle given: ' . $identifier
            );
        }
    }

    /**
     * @param mixed $identifier
     * @return self
     * @throws ilTestQuestionPoolInvalidArgumentException
     */
    public static function getInstance($identifier) : self
    {
        $lifecycle = new self();
        $lifecycle->validateIdentifier($identifier);
        $lifecycle->setIdentifier($identifier);

        return $lifecycle;
    }

    public static function getDraftInstance() : self
    {
        $lifecycle = new self();
        $lifecycle->setIdentifier(self::DRAFT);

        return $lifecycle;
    }
}
