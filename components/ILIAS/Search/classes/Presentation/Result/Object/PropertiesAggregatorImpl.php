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

namespace ILIAS\Search\Presentation\Result\Object;

use DateTimeImmutable;
use ILIAS\Data\URI;
use ilObject;
use ilObjectDefinition;
use ilLanguage;
use ilObjectPlugin;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\Data\Factory as DataFactory;
use ilPathGUI;

class PropertiesAggregatorImpl implements PropertiesAggregator
{
    public function __construct(
        protected AccessChecker $access,
        protected ilObjectDefinition $obj_definition,
        protected ilLanguage $lng,
        protected StaticURL $static_url,
        protected DataFactory $data_factory
    ) {
    }

    public function lookupTitle(int $obj_id): string
    {
        return ilObject::_lookupTitle($obj_id);
    }

    public function lookupDescription(int $obj_id): string
    {
        return ilObject::_lookupDescription($obj_id);
    }

    public function lookupCreationDate(int $obj_id): DateTimeImmutable
    {
        return new DateTimeImmutable(ilObject::_lookupCreationDate($obj_id));
    }

    public function lookupType(int $obj_id): string
    {
        return ilObject::_lookupType($obj_id);
    }

    public function buildLink(int $ref_id, string $type): ?URI
    {
        if (!$this->access->canAccessLinkToObject($ref_id)) {
            return null;
        }
        $ref_id = $this->data_factory->refId($ref_id);
        return $this->static_url->builder()->build($type, $ref_id);
    }

    public function buildRepositoryPath(int $ref_id): string
    {
        $path_gui = new ilPathGUI();
        $path_gui->enableTextOnly(true);
        return $path_gui->getPath(ROOT_FOLDER_ID, $ref_id);
    }

    public function makeTypePresentable(string $type): string
    {
        if (!$this->obj_definition->isPlugin($type)) {
            return $this->lng->txt('obj_' . $type);
        } else {
            return ilObjectPlugin::lookupTxtById($type, "obj_" . $type);
        }
    }

    public function buildIconPath(int $obj_id, string $type): string
    {
        return ilObject::_getIcon($obj_id, 'small', $type);
    }
}
