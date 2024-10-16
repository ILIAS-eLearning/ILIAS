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

use ILIAS\Data\ObjectId;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;

class ilLearningModuleExportOptionXML extends ilLegacyExportOption
{
    protected ilLanguage $lng;

    public function init(Container $DIC): void
    {
        $this->lng = $DIC->language();
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return 'xml';
    }

    public function getExportOptionId(): string
    {
        return 'lm_exp_option_xml';
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ["lm"];
    }

    public function isObjectSupported(
        ObjectId $object_id
    ): bool {
        $ot = ilObjectTranslation::getInstance($object_id->toInt());
        return $ot->getContentActivated();
    }

    public function isPublicAccessPossible(): bool
    {
        return true;
    }

    public function getLabel(): string
    {
        $this->lng->loadLanguageModule('exp');
        return $this->lng->txt("exp_xml");
    }

    public function onExportOptionSelected(\ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface $context): void
    {
        $this->ctrl->redirectToURL($this->ctrl->getLinkTargetByClass(ilObjContentObjectGUI::class, "showExportOptionsXML"));
    }
}
