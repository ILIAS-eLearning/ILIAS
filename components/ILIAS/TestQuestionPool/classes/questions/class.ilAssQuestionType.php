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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssQuestionType
{
    protected ilComponentRepository $component_repository;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var bool
     */
    protected $plugin;

    /**
     * @var string
     */
    protected $pluginName;

    /**
     * ilAssQuestionType constructor.
     */
    public function __construct()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->component_repository = $DIC['component.repository'];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return bool
     */
    public function isPlugin(): bool
    {
        return $this->plugin;
    }

    /**
     * @param bool $plugin
     */
    public function setPlugin($plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * @param string $pluginName
     */
    public function setPluginName($pluginName): void
    {
        $this->pluginName = $pluginName;
    }

    /**
     * @return bool
     */
    public function isImportable(): bool
    {
        if (!$this->isPlugin()) {
            return true;
        }

        // Plugins MAY overwrite this method an report back their activation status
        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($this->getPluginName())) {
            return false;
        }

        return $this->component_repository
            ->getComponentByTypeAndName(
                ilComponentInfo::TYPE_MODULES,
                'TestQuestionPool'
            )
            ->getPluginSlotById(
                'qst'
            )
            ->getPluginByName(
                $this->getPluginName()
            )->isActive();
    }

    /**
     * @param array $questionTypeData
     * @return array
     */
    public static function completeMissingPluginName($questionTypeData): array
    {
        if ($questionTypeData['plugin'] && !strlen($questionTypeData['plugin_name'])) {
            $questionTypeData['plugin_name'] = $questionTypeData['type_tag'];
        }

        return $questionTypeData;
    }
}
