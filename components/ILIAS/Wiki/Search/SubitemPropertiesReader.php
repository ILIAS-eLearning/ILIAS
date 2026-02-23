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

namespace ILIAS\Wiki\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ilObject;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ILIAS\Search\Presentation\Result\Subitem\ID;
use ilWikiPage;
use ilObjWikiGUI;
use ilCtrlInterface;
use ilWikiPageGUI;
use ilWikiHandlerGUI;
use ILIAS\Search\Presentation\Result\Subitem\Properties;
use Generator;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;
    protected ilCtrlInterface $ctrl;

    public static function type(): string
    {
        return 'wiki';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->data_factory = new DataFactory();
        $this->ctrl = $dic->ctrl();
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID ...$subitem_ids
    ): Generator {
        $obj_id = ilObject::_lookupObjId($parent_ref_id);
        foreach ($subitem_ids as $subitem_id) {
            switch ($subitem_id->type()) {
                case 'file':
                    if (ilObject::_exists((int) $subitem_id->id())) {
                        yield $this->getPropertiesForFile($factory, $parent_ref_id, $subitem_id);
                    }
                    break;

                case 'wpg':
                    $title = (string) ilWikiPage::lookupTitle((int) $subitem_id->id());
                    if ($title !== '') {
                        yield $this->getPropertiesForWikiPage($factory, $parent_ref_id, $subitem_id, $title);
                    }
                    break;
            }
        }
    }

    protected function getPropertiesForWikiPage(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID $subitem_id,
        string $title
    ): Properties {
        $link = rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            ilObjWikiGUI::getGotoLink($parent_ref_id, $title) . '&srcstring=1';
        return $factory->get(
            $subitem_id,
            $title,
            $this->data_factory->uri($link),
            false,
            $this->lng->txt('obj_pg')
        );
    }

    protected function getPropertiesForFile(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID $subitem_id
    ): Properties {
        $this->ctrl->setParameterByClass(ilWikiPageGUI::class, 'file_id', 'il__file_' . $subitem_id->id());
        $this->ctrl->setParameterByClass(ilWikiPageGUI::class, 'ref_id', $parent_ref_id);
        $link = rtrim(ILIAS_HTTP_PATH, '/') . '/' .
            $this->ctrl->getLinkTargetByClass([ilWikiHandlerGUI::class, ilObjWikiGUI::class, ilWikiPageGUI::class], 'downloadFile');
        $this->ctrl->clearParameterByClass(ilWikiPageGUI::class, 'file_id');
        $this->ctrl->clearParameterByClass(ilWikiPageGUI::class, 'ref_id');
        return $factory->get(
            $subitem_id,
            ilObject::_lookupTitle((int) $subitem_id->id()),
            $this->data_factory->uri($link),
            false,
            $this->lng->txt('obj_file')
        );
    }
}
