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

namespace ILIAS\Style\Content\Object;

use ILIAS\Style\Content\InternalRepoService;
use ILIAS\Style\Content\InternalDomainService;
use ILIAS\Style\Content\Container\ContainerDBRepository;
use ilSetting;
use ilObject;
use ilObjStyleSheet;
use ilObjectFactory;

/**
 * Manages repository object related content style behaviour
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectManager
{
    protected ilSetting $settings;
    protected ObjectDBRepository $object_repo;
    protected int $obj_id;
    protected ContainerDBRepository $container_repo;
    protected int $ref_id;
    protected InternalRepoService $repo_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalRepoService $repo_service,
        InternalDomainService $domain_service,
        int $ref_id,
        int $obj_id = 0
    ) {
        $this->settings = $domain_service->settings();
        $this->ref_id = $ref_id;
        $this->obj_id = ($obj_id > 0)
            ? $obj_id
            : ilObject::_lookupObjId($ref_id);
        $this->repo_service = $repo_service;
        $this->domain_service = $domain_service;
        $this->container_repo = $repo_service->repositoryContainer();
        $this->object_repo = $repo_service->object();
    }

    /**
     * Get all selectable styles. If a global fixed style is set,
     * this returns an empty array. If a ref id is provided for the manager,
     * upper container will be searched for shared local content styles.
     */
    public function getSelectableStyles() : array
    {
        $settings = $this->settings;
        $tree = $this->domain_service->repositoryTree();
        $container_repo = $this->container_repo;

        $fixed_style = $settings->get("fixed_content_style_id");
        if ($fixed_style > 0) {
            return [];
        } else {
            $st_styles = ilObjStyleSheet::_getStandardStyles(
                true,
                false,
                $this->ref_id
            );

            if ($this->ref_id > 0) {
                $path = $tree->getPathId($this->ref_id);
                $reuse_ref_ids = $container_repo->filterByReuse($path);
                $container_obj_ids = array_map(function ($ref_id) {
                    return ilObject::_lookupObjId($ref_id);
                }, $reuse_ref_ids);
                foreach ($this->object_repo->getOwnedStyles($container_obj_ids) as $obj_id => $style_id) {
                    $st_styles[$style_id] =
                        ilObject::_lookupTitle($style_id) .
                        " (" . ilObject::_lookupTitle($obj_id) . ")";
                }
            }
        }
        ksort($st_styles);
        return $st_styles;
    }

    protected function isSelectable(int $style_id) : bool
    {
        $sel_types = $this->getSelectableStyles();
        if (isset($sel_types[$style_id])) {
            return true;
        }
        return false;
    }

    public function updateStyleId(int $style_id) : void
    {
        ilObjStyleSheet::writeStyleUsage($this->obj_id, $style_id);
    }

    public function setOwnerOfStyle(int $style_id) : void
    {
        ilObjStyleSheet::writeOwner($this->obj_id, $style_id);
    }

    public function getStyleId() : int
    {
        return ilObjStyleSheet::lookupObjectStyle($this->obj_id);
    }

    /**
     * Clones a style to a new object (or references the same standard style)
     */
    public function cloneTo(int $obj_id) : void
    {
        $style_id = $this->getStyleId();
        if ($style_id > 0 && !ilObjStyleSheet::_lookupStandard($style_id)) {
            $style_obj = ilObjectFactory::getInstanceByObjId($style_id);
            $new_id = $style_obj->ilClone();
            ilObjStyleSheet::writeStyleUsage($obj_id, $new_id);
            ilObjStyleSheet::writeOwner($obj_id, $new_id);
        } else {
            ilObjStyleSheet::writeStyleUsage($obj_id, $style_id);
        }
    }

    /**
     * Inherits a non local style from the parent container
     */
    public function inheritFromParent() : void
    {
        if ($this->ref_id > 0) {
            $tree = $this->domain_service->repositoryTree();
            $parent_ref_id = $tree->getParentId($this->ref_id);
            $parent_id = ilObject::_lookupObjId((int) $parent_ref_id);
            $obj_id = ilObject::_lookupObjId($this->ref_id);
            $style_id = ilObjStyleSheet::lookupObjectStyle($parent_id);
            if ($style_id > 0) {
                if (ilObjStyleSheet::_lookupStandard($style_id)) {
                    ilObjStyleSheet::writeStyleUsage($obj_id, $style_id);
                }
            }
        }
    }

    public function getEffectiveStyleId() : int
    {
        $settings = $this->settings;

        // the currently set/stored style for the object
        $style_id = $this->getStyleId();

        // the set style must either be owned or be selectable
        if (!$this->isOwned($style_id) && !$this->isSelectable($style_id)) {
            $style_id = 0;
        }

        // check global fixed content style, which overwrites anything
        $fixed_style = (int) $settings->get("fixed_content_style_id");
        if ($fixed_style > 0) {
            $style_id = $fixed_style;
        }

        // if no style id is set up to this point, check/use global default style
        if ($style_id <= 0) {
            $style_id = (int) $settings->get("default_content_style_id");
        }

        if ($style_id > 0 && ilObject::_lookupType($style_id) === "sty") {
            return $style_id;
        }
        return 0;
    }

    // is a style owned by an object?
    public function isOwned(int $style_id) : bool
    {
        return $this->object_repo->isOwned($this->obj_id, $style_id);
    }
}
