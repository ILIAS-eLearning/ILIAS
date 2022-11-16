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

/**
 * Class ilMDKeywordExposer
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilMDKeywordExposer extends AbstractModificationProvider
{
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    public function getContentModification(CalledContexts $screen_context_stack): ?ContentModification
    {
        if ($screen_context_stack->current()->hasReferenceId()) {
            $object_id = $screen_context_stack->current()->getReferenceId()->toObjectId()->toInt();

            if ($general = $this->getGeneral($object_id)) {
                // Keywords
                $keywords = [];
                foreach ($general->getKeywordIds() as $keyword_id) {
                    $keyword = $general->getKeyword($keyword_id);
                    $keywords[] = $keyword->getKeyword();
                }

                $delimiter = ilMDSettings::_getInstance()->getDelimiter() ?? ",";

                if (count($keywords) > 0) {
                    $this->globalScreen()->layout()->meta()->addMetaDatum(
                        $this->data->htmlMetadata()->userDefined('keywords', implode($delimiter, $keywords))
                    );
                }
                // Languages
                $languages = [];
                foreach ($general->getLanguageIds() as $language_id) {
                    $language = $general->getLanguage($language_id);
                    $languages[] = $language->getLanguageCode();
                }
                if (count($languages) > 0) {
                    $this->globalScreen()->layout()->meta()->addMetaDatum(
                        $this->data->htmlMetadata()->userDefined('languages', implode($delimiter, $languages))
                    );
                }
            }

            if ($rights = $this->getRights($object_id)) {
                // Copyright
                $copy_right_id = ilMDCopyrightSelectionEntry::_extractEntryId($rights->getDescription());
                if ($copy_right_id > 0) {
                    $entry = new ilMDCopyrightSelectionEntry($copy_right_id);
                    $this->globalScreen()->layout()->meta()->addMetaDatum(
                        $this->data->htmlMetadata()->userDefined('copyright', $entry->getTitle())
                    );
                }
            }
        }

        return null;
    }

    private function getGeneral(int $object_id): ?ilMDGeneral
    {
        if ($id = ilMDGeneral::_getId($object_id, $object_id)) {
            $gen = new ilMDGeneral();
            $gen->setMetaId($id);

            return $gen;
        }
        return null;
    }

    private function getRights(int $object_id): ?ilMDRights
    {
        if ($id = ilMDRights::_getId($object_id, $object_id)) {
            $rig = new ilMDRights();
            $rig->setMetaId($id);

            return $rig;
        }
        return null;
    }
}
