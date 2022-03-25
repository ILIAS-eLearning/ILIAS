<?php

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
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->repository();
    }
    
    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
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
                    $this->globalScreen()->layout()->meta()->addMetaDatum('keywords', implode($delimiter, $keywords));
                }
                // Languages
                $languages = [];
                foreach ($general->getLanguageIds() as $language_id) {
                    $language = $general->getLanguage($language_id);
                    $languages[] = $language->getLanguageCode();
                }
                if (count($languages) > 0) {
                    $this->globalScreen()->layout()->meta()->addMetaDatum('languages', implode($delimiter, $languages));
                }
            }
    
            if ($rights = $this->getRights($object_id)) {
                // Copyright
                $copy_right_id = ilMDCopyrightSelectionEntry::_extractEntryId($rights->getDescription());
                if ($copy_right_id > 0) {
                    $entry = new ilMDCopyrightSelectionEntry($copy_right_id);
                    $this->globalScreen()->layout()->meta()->addMetaDatum('copyright', $entry->getTitle());
                }
            }
        }
        
        return null;
    }
    
    private function getGeneral(int $object_id) : ?ilMDGeneral
    {
        if ($id = ilMDGeneral::_getId($object_id, $object_id)) {
            $gen = new ilMDGeneral();
            $gen->setMetaId($id);
            
            return $gen;
        }
        return null;
    }
    
    private function getRights(int $object_id) : ?ilMDRights
    {
        if ($id = ilMDRights::_getId($object_id, $object_id)) {
            $rig = new ilMDRights();
            $rig->setMetaId($id);
            
            return $rig;
        }
        return null;
    }
    
}
