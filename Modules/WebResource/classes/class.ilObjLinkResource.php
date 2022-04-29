<?php declare(strict_types=1);

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
 * Class ilObjLinkResource
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesWebResource
 */
class ilObjLinkResource extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "webr";
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @todo how to handle this meta data switch
     */
    public function create($a_upload = false) : int
    {
        $new_id = parent::create();
        if (!$a_upload) {
            $this->createMetaData();
        }
        return $new_id;
    }

    public function update() : bool
    {
        $this->updateMetaData();
        return parent::update();
    }

    protected function doMDUpdateListener(string $a_element) : void
    {
        $md = new ilMD($this->getId(), 0, $this->getType());
        if (!is_object($md_gen = $md->getGeneral())) {
            return;
        }
        $title = $md_gen->getTitle();
        $description = '';
        foreach ($md_gen->getDescriptionIds() as $id) {
            $md_des = $md_gen->getDescription($id);
            $description = $md_des->getDescription();
            break;
        }
        switch ($a_element) {
            case 'General':
                if (ilLinkResourceItems::lookupNumberOfLinks(
                    $this->getId()
                ) == 1) {
                    $link_arr = ilLinkResourceItems::_getFirstLink(
                        $this->getId()
                    );
                    $link = new ilLinkResourceItems($this->getId());
                    $link->readItem($link_arr['link_id']);
                    $link->setTitle($title);
                    $link->setDescription($description);
                    $link->update();
                }
                break;
        }
    }

    public function delete() : bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete items and list
        ilLinkResourceItems::_deleteAll($this->getId());
        $list = new ilLinkResourceList($this->getId());
        $list->delete();

        // delete meta data
        $this->deleteMetaData();

        return true;
    }

    public function cloneObject(
        int $target_id,
        int $copy_id = 0,
        bool $omit_tree = false
    ) : ?ilObject {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneMetaData($new_obj);

        // object created now copy other settings
        $links = new ilLinkResourceItems($this->getId());
        $links->cloneItems($new_obj->getId());

        // append copy info weblink title
        if (ilLinkResourceItems::_isSingular($new_obj->getId())) {
            $first = ilLinkResourceItems::_getFirstLink($new_obj->getId());
            ilLinkResourceItems::updateTitle(
                $first['link_id'],
                $new_obj->getTitle()
            );
        }
        return $new_obj;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $attribs = array("obj_id" => "il_" . IL_INST_ID . "_webr_" . $this->getId(
            )
        );

        $writer->xmlStartTag('WebLinks', $attribs);

        // LOM MetaData
        $md2xml = new ilMD2XML($this->getId(), $this->getId(), 'webr');
        $md2xml->startExport();
        $writer->appendXML($md2xml->getXML());

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

        // All links
        $links = new ilLinkResourceItems($this->getId());
        $links->toXML($writer);
        $writer->xmlEndTag('WebLinks');
    }
}
