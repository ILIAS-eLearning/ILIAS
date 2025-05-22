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
 * @package components\ILIAS/Test(QuestionPool)
 */
class ilAssQuestionType
{
    protected ilComponentRepository $component_repository;

    protected int $id;
    protected string $tag = '';
    protected bool $is_plugin;
    protected ?string $plugin_name = null;

    public function __construct()
    {
        global $DIC;
        $this->component_repository = $DIC['component.repository'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function setPlugin(bool $is_plugin): void
    {
        $this->is_plugin = $is_plugin;
    }

    public function setPluginName(?string $plugin_name): void
    {
        $this->plugin_name = $plugin_name;
    }

    /**
     * @return bool
     */
    public function isImportable(): bool
    {
        if (!$this->is_plugin) {
            return true;
        }

        if (!isset($this->plugin_name)) {
            return false;
        }

        // Plugins MAY overwrite this method an report back their activation status
        if (!$this->component_repository->getComponentByTypeAndName(
            ilComponentInfo::TYPE_MODULES,
            'TestQuestionPool'
        )->getPluginSlotById('qst')->hasPluginName($this->plugin_name)) {
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
                $this->plugin_name
            )->isActive();
    }

    public static function completeMissingPluginName(array $question_type_data): array
    {
        if ($question_type_data['plugin']
            && $question_type_data['plugin_name'] !== null
            && $question_type_data['plugin_name'] !== '') {
            $question_type_data['plugin_name'] = $question_type_data['type_tag'];
        }

        return $question_type_data;
    }
}
