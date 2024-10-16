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
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\Consumer\ExportOption\BasicLegacyHandler as ilBasicLegacyExportOption;
use ILIAS\Export\ExportHandler\I\Consumer\Context\HandlerInterface as ilExportHandlerConsumerContextInterface;
use ILIAS\DI\Container;
use ILIAS\Export\ExportHandler\I\Info\File\CollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\CollectionInterface as ilExportHandlerConsumerFileIdentifierCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\HandlerInterface as ilExportHandlerConsumerFileIdentifierInterface;
use ILIAS\Wiki\Export\WikiHtmlExport;

class ilWikiExportOptionHTMLWithComments extends ilBasicLegacyExportOption
{
    protected ilLanguage $lng;

    public function init(Container $DIC): void
    {
        $this->lng = $DIC->language();
        parent::init($DIC);
    }

    public function getExportType(): string
    {
        return 'html_comments';
    }

    public function getExportOptionId(): string
    {
        return 'wiki_exp_option_html_with_comments';
    }

    public function getSupportedRepositoryObjectTypes(): array
    {
        return ['wiki'];
    }

    public function getLabel(): string
    {
        return "HTML (" . $this->lng->txt("wiki_incl_comments") . ")";
    }

    public function isObjectSupported(ObjectId $object_id): bool
    {
        try {
            return (
                ilObjWiki::_exists($object_id->toInt()) and
                (new ilObjWiki($object_id->toInt(), false))->isCommentsExportPossible()
            );
        } catch (ilObjectTypeMismatchException $exception) {
            return false;
        }
    }

    public function onExportOptionSelected(ilExportHandlerConsumerContextInterface $context): void
    {
        /** @var ilObjWiki $wiki */
        $wiki = $context->exportObject();
        $cont_exp = new WikiHtmlExport($wiki);
        $cont_exp->setMode(WikiHtmlExport::MODE_COMMENTS);
        $cont_exp->buildExportFile();
        $this->ctrl->redirectByClass(ilExportGUI::class, ilExportGUI::CMD_LIST_EXPORT_FILES);
    }
}
