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

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

/**
 * Class ilObjLinkResource
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilObjLinkResource extends ilObject
{
    protected LOMServices $lom_services;
    protected ilWebLinkDatabaseRepository $repo;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->type = "webr";
        parent::__construct($a_id, $a_call_by_reference);

        $this->lom_services = $DIC->learningObjectMetadata();
    }

    protected function getWebLinkRepo(): ilWebLinkRepository
    {
        if (isset($this->repo)) {
            return $this->repo;
        }
        return $this->repo = new ilWebLinkDatabaseRepository($this->getId());
    }

    /**
     * @todo how to handle this meta data switch
     */
    public function create($a_upload = false): int
    {
        $new_id = parent::create();
        if (!$a_upload) {
            $this->createMetaData();
        }
        return $new_id;
    }

    public function update(): bool
    {
        $this->updateMetaData();
        return parent::update();
    }

    protected function doMDUpdateListener(string $a_element): void
    {
        if ($a_element !== 'General') {
            return;
        }

        $paths = $this->lom_services->paths();
        $reader = $this->lom_services->read(
            $this->getId(),
            0,
            $this->getType(),
            $paths->custom()->withNextStep('general')->get()
        );

        $title = $reader->firstData($paths->title())->value();
        $description = $reader->firstData($paths->firstDescription())->value();

        if (
            !$this->getWebLinkRepo()->doesListExist() &&
            $this->getWebLinkRepo()->doesOnlyOneItemExist()
        ) {
            $item = $this->getWebLinkRepo()->getAllItemsAsContainer()->getFirstItem();
            $draft = new ilWebLinkDraftItem(
                $item->isInternal(),
                $title,
                $description,
                $item->getTarget(),
                $item->isActive(),
                $item->getParameters()
            );
            $this->getWebLinkRepo()->updateItem($item, $draft);
        }
        if ($this->getWebLinkRepo()->doesListExist()) {
            $list = $this->getWebLinkRepo()->getList();
            $draft = new ilWebLinkDraftList(
                $title,
                $description
            );
            $this->getWebLinkRepo()->updateList($list, $draft);
        }
    }

    public function delete(): bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete items and list
        $this->getWebLinkRepo()->deleteAllItems();
        if ($this->getWebLinkRepo()->doesListExist()) {
            $this->getWebLinkRepo()->deleteList();
        }

        // delete meta data
        $this->deleteMetaData();

        return true;
    }

    public function cloneObject(
        int $target_id,
        int $copy_id = 0,
        bool $omit_tree = false
    ): ?ilObject {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneMetaData($new_obj);

        // object created, now copy items and parameters
        $items = $this->getWebLinkRepo()->getAllItemsAsContainer()->getItems();
        $container = new ilWebLinkDraftItemsContainer();

        foreach ($items as $item) {
            $draft = new ilWebLinkDraftItem(
                $item->isInternal(),
                $item->getTitle(),
                $item->getDescription(),
                $item->getTarget(),
                $item->isActive(),
                $item->getParameters()
            );

            $container->addItem($draft);
        }

        $new_web_link_repo = new ilWebLinkDatabaseRepository($new_obj->getId());
        $new_web_link_repo->createAllItemsInDraftContainer($container);

        // append copy info weblink title
        if ($new_web_link_repo->doesOnlyOneItemExist(true)) {
            $item = ilObjLinkResourceAccess::_getFirstLink($new_obj->getId());
            $draft = new ilWebLinkDraftItem(
                $item->isInternal(),
                $new_obj->getTitle(),
                $new_obj->getDescription(),
                $item->getTarget(),
                $item->isActive(),
                $item->getParameters()
            );
            $new_web_link_repo->updateItem($item, $draft);
        }
        return $new_obj;
    }

    public function toXML(ilXmlWriter $writer, bool $skip_lom = false): void
    {
        $attribs = array("obj_id" => "il_" . IL_INST_ID . "_webr_" . $this->getId(
        )
        );

        $writer->xmlStartTag('WebLinks', $attribs);

        if (!$skip_lom) {
            // LOM MetaData
            $md2xml = new ilMD2XML($this->getId(), $this->getId(), 'webr');
            $md2xml->startExport();
            $writer->appendXML($md2xml->getXML());
        }

        // Sorting
        switch (ilContainerSortingSettings::_lookupSortMode($this->getId())) {
            case ilContainer::SORT_MANUAL:
                $writer->xmlElement(
                    'Sorting',
                    array('type' => 'Manual')
                );
                break;

            case ilContainer::SORT_TITLE:
            default:
                $writer->xmlElement(
                    'Sorting',
                    array('type' => 'Title')
                );
                break;
        }

        if ($this->getWebLinkRepo()->doesListExist()) {
            $writer->xmlStartTag('ListSettings');
            $writer->xmlElement('ListTitle', [], $this->getTitle());
            $writer->xmlElement('ListDescription', [], $this->getDescription());
            $writer->xmlEndTag('ListSettings');
        }

        // All items
        $items = $this->getWebLinkRepo()->getAllItemsAsContainer()
                                        ->sort()
                                        ->getItems();

        $position = 0;
        foreach ($items as $item) {
            ++$position;
            $item->toXML($writer, $position);
        }

        $writer->xmlEndTag('WebLinks');
    }
}
