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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThresholdXmlParser extends ilSaxParser
{
    protected bool $parsingActive = false;
    protected ?string $characterDataBuffer = null;
    protected ?int $curSkillBaseId = null;
    protected ?int $curSkillTrefId = null;

    private ilDBInterface $db;

    protected ?ilTestSkillLevelThresholdImportList $skillLevelThresholdImportList = null;
    protected ?ilTestSkillLevelThresholdImport $curSkillLevelThreshold = null;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC['ilDB'];
        parent::__construct();
    }

    public function isParsingActive(): bool
    {
        return $this->parsingActive;
    }

    public function setParsingActive(bool $parsingActive): void
    {
        $this->parsingActive = $parsingActive;
    }

    protected function getCharacterDataBuffer(): ?string
    {
        return $this->characterDataBuffer;
    }

    /**
     * @param string $characterDataBuffer
     */
    protected function resetCharacterDataBuffer(): void
    {
        $this->characterDataBuffer = '';
    }

    protected function appendToCharacterDataBuffer(string $characterData): void
    {
        $this->characterDataBuffer .= $characterData;
    }

    public function getSkillLevelThresholdImportList(): ?\ilTestSkillLevelThresholdImportList
    {
        return $this->skillLevelThresholdImportList;
    }

    public function initSkillLevelThresholdImportList(): void
    {
        $this->skillLevelThresholdImportList = new ilTestSkillLevelThresholdImportList($this->db);
    }

    public function getCurSkillBaseId(): ?int
    {
        return $this->curSkillBaseId;
    }

    public function setCurSkillBaseId(?int $curSkillBaseId): void
    {
        $this->curSkillBaseId = $curSkillBaseId;
    }

    public function getCurSkillTrefId(): ?int
    {
        return $this->curSkillTrefId;
    }

    public function setCurSkillTrefId(?int $curSkillTrefId): void
    {
        $this->curSkillTrefId = $curSkillTrefId;
    }

    public function getCurSkillLevelThreshold(): ?ilTestSkillLevelThresholdImport
    {
        return $this->curSkillLevelThreshold;
    }

    public function setCurSkillLevelThreshold(?ilTestSkillLevelThresholdImport $curSkillLevelThreshold): void
    {
        $this->curSkillLevelThreshold = $curSkillLevelThreshold;
    }

    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function handlerBeginTag($xmlParser, $tagName, $tagAttributes): void
    {
        if ($tagName != 'SkillsLevelThresholds' && !$this->isParsingActive()) {
            return;
        }

        switch ($tagName) {
            case 'SkillsLevelThresholds':
                $this->setParsingActive(true);
                $this->initSkillLevelThresholdImportList();
                break;

            case 'QuestionsAssignedSkill':
                $this->setCurSkillBaseId($tagAttributes['BaseId']);
                $this->setCurSkillTrefId($tagAttributes['TrefId']);
                break;

            case 'OriginalLevelDescription':
            case 'OriginalLevelTitle':
            case 'ThresholdPercentage':
            case 'OriginalSkillPath':
            case 'OriginalSkillTitle':
                $this->resetCharacterDataBuffer();
                break;

            case 'SkillLevel':
                $skillLevelThreshold = new ilTestSkillLevelThresholdImport($this->db);
                $skillLevelThreshold->setImportSkillBaseId($this->getCurSkillBaseId());
                $skillLevelThreshold->setImportSkillTrefId($this->getCurSkillTrefId());
                $skillLevelThreshold->setImportLevelId($tagAttributes['Id']);
                $skillLevelThreshold->setOrderIndex($tagAttributes['Nr']);
                $this->setCurSkillLevelThreshold($skillLevelThreshold);
                break;
        }
    }

    public function handlerEndTag($xmlParser, $tagName): void
    {
        if (!$this->isParsingActive()) {
            return;
        }

        switch ($tagName) {
            case 'SkillsLevelThresholds':
                $this->setParsingActive(false);
                break;

            case 'QuestionsAssignedSkill':
                $this->setCurSkillBaseId(null);
                $this->setCurSkillTrefId(null);
                break;

            case 'OriginalSkillTitle':
                $this->getSkillLevelThresholdImportList()->addOriginalSkillTitle(
                    $this->getCurSkillBaseId(),
                    $this->getCurSkillTrefId(),
                    $this->getCharacterDataBuffer()
                );
                $this->resetCharacterDataBuffer();
                break;

            case 'OriginalSkillPath':
                $this->getSkillLevelThresholdImportList()->addOriginalSkillPath(
                    $this->getCurSkillBaseId(),
                    $this->getCurSkillTrefId(),
                    $this->getCharacterDataBuffer()
                );
                $this->resetCharacterDataBuffer();
                break;

            case 'SkillLevel':
                $this->getSkillLevelThresholdImportList()->addSkillLevelThreshold(
                    $this->getCurSkillLevelThreshold()
                );
                $this->setCurSkillLevelThreshold(null);
                break;

            case 'ThresholdPercentage':
                $this->getCurSkillLevelThreshold()->setThreshold($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;

            case 'OriginalLevelTitle':
                $this->getCurSkillLevelThreshold()->setOriginalLevelTitle($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;

            case 'OriginalLevelDescription':
                $this->getCurSkillLevelThreshold()->setOriginalLevelDescription($this->getCharacterDataBuffer());
                $this->resetCharacterDataBuffer();
                break;
        }
    }

    public function handlerCharacterData($xmlParser, $charData): void
    {
        if (!$this->isParsingActive()) {
            return;
        }

        if ($charData != "\n") {
            // Replace multiple tabs with one space
            $charData = preg_replace("/\t+/", " ", $charData);

            $this->appendToCharacterDataBuffer($charData);
        }
    }
}
