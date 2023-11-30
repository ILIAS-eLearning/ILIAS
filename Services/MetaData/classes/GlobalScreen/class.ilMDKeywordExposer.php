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
 */

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\MetaData\Services\Services as Metadata;
use ILIAS\MetaData\Services\Reader\ReaderInterface as Reader;

/**
 * Class ilMDKeywordExposer
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilMDKeywordExposer extends AbstractModificationProvider
{
    protected Metadata $md;

    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->md = $dic->learningObjectMetadata();
        parent::__construct($dic);
    }

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    public function getContentModification(CalledContexts $screen_context_stack): ?ContentModification
    {
        if ($screen_context_stack->current()->hasReferenceId()) {
            $object_id = $screen_context_stack->current()->getReferenceId()->toObjectId()->toInt();
            $paths = $this->md->paths();
            $reader = $this->generalReader($object_id);

            // Keywords
            $keywords = [];
            foreach ($reader->allData($paths->keywords()) as $keyword) {
                $keywords[] = $keyword->value();
            }
            if (count($keywords) > 0) {
                $this->globalScreen()->layout()->meta()->addMetaDatum(
                    $this->data->htmlMetadata()->userDefined('keywords', implode(',', $keywords))
                );
            }

            // Languages
            $languages = [];
            foreach ($reader->allData($paths->languages()) as $language) {
                $languages[] = $language->value();
            }
            if (count($languages) > 0) {
                $this->globalScreen()->layout()->meta()->addMetaDatum(
                    $this->data->htmlMetadata()->userDefined('languages', implode(',', $languages))
                );
            }

            if ($settings = ilMDSettings::_getInstance()->isCopyrightSelectionActive()) {
                $reader = $this->copyrightReader($object_id);
                // Copyright
                $copyright = $reader->firstData($paths->copyright())->value();
                $copyright_id = ilMDCopyrightSelectionEntry::_extractEntryId($copyright);
                if ($copyright_id > 0) {
                    $entry = new ilMDCopyrightSelectionEntry($copyright_id);
                    $copyright = $entry->getTitle();
                }
                if ($copyright === '') {
                    $entry = new ilMDCopyrightSelectionEntry(ilMDCopyrightSelectionEntry::getDefault());
                    $copyright = $entry->getTitle();
                }
                $this->globalScreen()->layout()->meta()->addMetaDatum(
                    $this->data->htmlMetadata()->userDefined('copyright', $copyright)
                );
            }
        }

        return null;
    }

    protected function generalReader(int $object_id): Reader
    {
        $path = $this->md->paths()->custom()->withNextStep('general')->get();
        return $this->md->read(
            $object_id,
            $object_id,
            ilObject::_lookupType($object_id),
            $path
        );
    }

    protected function copyrightReader(int $object_id): Reader
    {
        $path = $this->md->paths()->custom()
                                  ->withNextStep('rights')
                                  ->withNextStep('description')
                                  ->withNextStep('string')
                                  ->get();
        return $this->md->read(
            $object_id,
            $object_id,
            ilObject::_lookupType($object_id),
            $path
        );
    }
}
