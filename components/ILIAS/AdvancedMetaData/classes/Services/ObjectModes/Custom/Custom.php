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

namespace ILIAS\AdvancedMetaData\Services\ObjectModes\Custom;

class Custom implements CustomInterface
{
    protected \ilObjUser $user;

    /**
     * @var SetInterface[]
     */
    protected array $sets = [];

    public function __construct(
        \ilObjUser $user,
        string $type,
        int $ref_id,
        string $sub_type = '',
        int $sub_id = 0
    ) {
        $this->user = $user;
        $this->initSets($type, $ref_id, $sub_type, $sub_id);
    }

    /**
     * @return SetInterface[]
     */
    public function sets(): array
    {
        return $this->sets;
    }

    protected function initSets(
        string $type,
        int $ref_id,
        string $sub_type,
        int $sub_id
    ): void {
        foreach (\ilAdvancedMDValues::getInstancesForObjectId(
            \ilObject::_lookupObjId($ref_id),
            $type,
            $sub_type,
            $sub_id
        ) as $record_id => $a_values) {
            // this correctly binds group and definitions
            $a_values->read();

            $fields = [];
            $defs = $a_values->getDefinitions();
            foreach ($a_values->getADTGroup()->getElements() as $element_id => $element) {
                if (!$element->isNull()) {
                    $field_translations = \ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);
                    $title = $field_translations->getTitleForLanguage($element_id, $this->user->getLanguage());

                    $presentation_bridge = \ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);
                    if ($element instanceof \ilADTLocation) {
                        $presentation_bridge->setSize('500px', '300px');
                    }

                    $fields[] = new Field(
                        $title,
                        $presentation_bridge->getSortable(),
                        $presentation_bridge->getHTML()
                    );
                }
            }

            $record_translations = \ilAdvancedMDRecordTranslations::getInstanceByRecordId($record_id);
            $this->sets[] = new Set(
                $record_translations->getTitleForLanguage($this->user->getLanguage()),
                ...$fields
            );
        }
    }
}
