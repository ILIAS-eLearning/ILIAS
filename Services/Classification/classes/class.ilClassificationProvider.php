<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use Psr\Http\Message\RequestInterface;

/**
 * Classification provider
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilClassificationProvider
{
    protected int $parent_ref_id;
    protected int $parent_obj_id;
    protected string $parent_type;
    protected RequestInterface $request;
    
    public function __construct(
        int $a_parent_ref_id,
        int $a_parent_obj_id,
        string $a_parent_obj_type
    ) {
        global $DIC;

        $this->request = $DIC->http()->request();
        $this->parent_ref_id = $a_parent_ref_id;
        $this->parent_obj_id = $a_parent_obj_id;
        $this->parent_type = $a_parent_obj_type;
        
        $this->init();
    }
    
    protected function init() : void
    {
    }
        
    /**
     * Get all valid providers (for parent container)
     */
    public static function getValidProviders(
        int $a_parent_ref_id,
        int $a_parent_obj_id,
        string $a_parent_obj_type
    ) : array {
        $res = array();
        
        if (ilTaxonomyClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)) {
            $res[] = new ilTaxonomyClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);
        }
        
        if (ilTaggingClassificationProvider::isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)) {
            $res[] = new ilTaggingClassificationProvider($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);
        }
        
        return $res;
    }
    
    /**
     * Is provider currently active?
     */
    abstract public static function isActive(
        int $a_parent_ref_id,
        int $a_parent_obj_id,
        string $a_parent_obj_type
    ) : bool;

    /**
     * Render HTML chunks
     */
    abstract public function render(array &$a_html, object $a_parent_gui) : void;

    /**
     * Import post data
     * @param array|null $a_saved
     */
    abstract public function importPostData(?array $a_saved = null) : array;

    /**
     * Set selection
     */
    abstract public function setSelection(array $a_value) : void;

    /**
     * Get filtered object ref ids
     */
    abstract public function getFilteredObjects() : array;
    
    /**
     * Init list gui properties
     */
    public function initListGUI(ilObjectListGUI $a_list_gui) : void
    {
    }
}
