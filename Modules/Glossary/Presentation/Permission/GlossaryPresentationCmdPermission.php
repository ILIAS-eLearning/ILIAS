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

namespace ILIAS\Glossary\Presentation\Permission;

use ILIAS\Glossary\Presentation\PresentationGUIRequest;
use ILIAS\Repository\Permission\CmdEntity;
use ILIAS\Repository\Permission\CmdPermission;

class GlossaryPresentationCmdPermission extends CmdPermission
{
    protected const GLOSSARY = "glossary";
    protected const DEFINITION_PAGE = "definition_page";
    protected const INFO_SCREEN = "info_screen";
    protected const MEDIA_OBJECT = "media_object";
    protected const NOTE = "note";

    public function __construct(
        \ilLanguage $lng,
        protected \ilAccessHandler $access,
        \ilGlobalTemplateInterface $tpl,
        \ilCtrlInterface $ctrl,
        protected \ilObjGlossary $glossary,
        protected ?PresentationGUIRequest $request = null
    ) {
        parent::__construct($lng, $tpl, $ctrl);
    }

    public function isCommandPermitted(
        string $cmd,
        int $node_id,
        string $entity,
        string $entity_id = ""
    ): bool {
        if ($node_id !== $this->glossary->getRefId()) {
            return false;
        }

        if ($entity === self::INFO_SCREEN || ($entity === self::GLOSSARY && $cmd === "infoScreen")) {
            return ($entity === self::GLOSSARY || in_array($cmd, [
                "showSummary",
                "showNews",
                "showLearningProgress",
                "showPrivateNotes",
            ], true)) && ($this->access->checkAccess("read", "", $node_id)
                || $this->access->checkAccess("visible", "", $node_id));
        }

        if (!$this->access->checkAccess("read", "", $node_id)) {
            return false;
        }

        if ($entity === self::NOTE) {
            return $cmd === "getListHTML";
        }

        if ($entity === self::DEFINITION_PAGE) {
            return $this->definitionPageBelongsToGlossary((int) $entity_id);
        }

        if ($entity === self::MEDIA_OBJECT) {
            return in_array($cmd, ["fullscreen", "media"], true)
                && $this->mediaObjectBelongsToGlossary((int) $entity_id);
        }

        if ($entity !== self::GLOSSARY) {
            return false;
        }

        // all commands with read permission
        return in_array($cmd, [
            "listTerms",
            "listDefinitions",
            "listTermByGivenAsTable",
            "listTermByGivenAsPanel",
            "printViewSelection",
            "printView",
            "showFlashcards",
            "showTaxonomy",
            "show",
            "applyFilter",
            "resetFilter",
            "showDownloadList",
            "downloadExportFile",
            "chooseLetter",
            "listBoxes",
            "confirmResetBoxes",
            "resetBoxes",
            "cancelResetBoxes",
            "showAllItems",
            "showRemainingItems",
            "showHidden",
            "showRevealed",
            "answerCorrectly",
            "answerInCorrectly",
        ], true);
    }

    public function getDefaultCommand(): string
    {
        if ($this->isClass(\ilGlossaryPresentationGUI::class)) {
            return $this->glossary->isActiveFlashcards() ? "showFlashcards" : "listTerms";
        }
        if ($this->isClass(\ilPresentationListTableGUI::class)) {
            return "listTerms";
        }
        if ($this->isClass(\ilPresentationFullGUI::class) ||
            $this->isClass(\ilGlossaryFlashcardBoxGUI::class)) {
            return "show";
        }
        if ($this->isClass(\ilGlossaryFlashcardGUI::class)) {
            return "listBoxes";
        }
        if ($this->isClass(\ilNoteGUI::class)) {
            return "getListHTML";
        }
        return "";
    }

    public function getRequestCommand(): string
    {
        if ($this->ctrl === null) {
            return "";
        }
        return $this->ctrl->getCmd($this->getDefaultCommand());
    }

    public function getRequestEntity(): ?CmdEntity
    {
        if ($this->isClass(\ilGlossaryPresentationGUI::class) &&
            in_array($this->getRequestCommand(), ["fullscreen", "media"], true)) {
            return $this->cmdEntity(
                self::MEDIA_OBJECT,
                (string) ($this->request?->getMobId() ?? 0)
            );
        }
        if ($this->isClass(\ilGlossaryDefPageGUI::class)) {
            if ($this->request === null) {
                return null;
            }
            return $this->cmdEntity(
                self::DEFINITION_PAGE,
                (string) $this->request->getDefinitionPageId()
            );
        }
        if ($this->isClass(\ilInfoScreenGUI::class)) {
            return $this->cmdEntity(self::INFO_SCREEN);
        }
        if ($this->isClass(\ilNoteGUI::class)) {
            return $this->cmdEntity(self::NOTE);
        }
        if ($this->isClass(\ilGlossaryPresentationGUI::class) ||
            $this->isClass(\ilPresentationFullGUI::class) ||
            $this->isClass(\ilPresentationListTableGUI::class) ||
            $this->isClass(\ilGlossaryFlashcardGUI::class) ||
            $this->isClass(\ilGlossaryFlashcardBoxGUI::class)) {
            return $this->cmdEntity(self::GLOSSARY);
        }
        return null;
    }

    public function getRequestNodeId(): int
    {
        return $this->request?->getRefId() ?? $this->glossary->getRefId();
    }

    public function isRequestCommandPermitted(): bool
    {
        $entity = $this->getRequestEntity();
        if ($entity === null) {
            return false;
        }
        return $this->isCommandPermitted(
            $this->getRequestCommand(),
            $this->getRequestNodeId(),
            $entity->getType(),
            (string) $entity->getId()
        );
    }

    public function isForwardPermitted(string $from_class, string $to_class): bool
    {
        if ($from_class !== \ilGlossaryPresentationGUI::class) {
            return false;
        }

        if (!in_array($to_class, [
            \ilInfoScreenGUI::class,
            \ilPresentationFullGUI::class,
            \ilPresentationListTableGUI::class,
            \ilGlossaryDefPageGUI::class,
            \ilGlossaryFlashcardGUI::class,
            \ilGlossaryFlashcardBoxGUI::class,
        ], true)) {
            return false;
        }

        return $this->isRequestCommandPermitted();
    }

    protected function definitionPageBelongsToGlossary(int $definition_page_id): bool
    {
        if ($definition_page_id <= 0) {
            return false;
        }

        $glossary_ids = $this->glossary->getAllGlossaryIds();
        return in_array(\ilGlossaryTerm::_lookGlossaryID($definition_page_id), $glossary_ids)
            || \ilGlossaryTermReferences::isReferenced($glossary_ids, $definition_page_id);
    }

    protected function mediaObjectBelongsToGlossary(int $media_object_id): bool
    {
        if ($media_object_id <= 0) {
            return false;
        }

        foreach ($this->glossary->getTermList(a_include_references: true) as $term) {
            $page = new \ilGlossaryDefPage((int) $term["id"]);
            if (in_array($media_object_id, array_map("intval", $page->collectMediaObjects(false)), true)) {
                return true;
            }
        }
        return false;
    }
}
