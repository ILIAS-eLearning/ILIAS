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
 * Class ilBiblRisFileReader
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblRisFileReader extends ilBiblFileReaderBase implements ilBiblFileReaderInterface
{
    protected ilBiblRisFileReaderWrapper $wrapper;
    
    public function __construct(
        ilBiblEntryFactoryInterface $entry_factory,
        ilBiblFieldFactoryInterface $field_factory,
        ilBiblAttributeFactoryInterface $attribute_factory
    ) {
        parent::__construct($entry_factory, $field_factory, $attribute_factory);
        $this->wrapper = new ilBiblRisFileReaderWrapper();
    }
    
    public function parseContent() : array
    {
        $content = $this->wrapper->parseContent($this->file_content);
        
        return $this->flattenContent($content);
    }
    
    private function flattenContent(array $content) : array
    {
        $flattener = function ($i) {
            if (is_array($i)) {
                return implode(", ", $i);
            }
            return $i;
        };
        
        $walker = function ($item) use ($flattener) {
            if (is_array($item)) {
                foreach ($item as $k => $i) {
                    $item[$k] = $flattener($i);
                }
                return $item;
            }
            return $item;
        };
        
        return array_map($walker, $content);
    }
}
