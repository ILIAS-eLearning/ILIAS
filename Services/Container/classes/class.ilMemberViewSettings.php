<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

use Psr\Http\Message\RequestInterface;

/**
 * @classDescription Settings for members view
 * @author Stefan Meyer <meyer@leifos.com>
 *
 */
class ilMemberViewSettings
{
    public const SESSION_MEMBER_VIEW_CONTAINER = 'member_view_container';

    /**
     * @var ilLogger
     */
    protected $logger;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilMemberViewSettings
     */
    private static $instance = null;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var null | int
     */
    private $container = null;

    /**
     * @var int[]
     */
    private $container_items = array();

    /**
     * @var int
     */
    private $current_ref_id = 0;
    
    /**
     * Constructor (singleton)
     */
    private function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->request = $DIC->http()->request();
        $this->ctrl = $DIC->ctrl();
        $this->logger = $DIC->logger()->cont();
        $this->read();
    }


    /**
     * @return ilMemberViewSettings
     */
    public static function getInstance() : ilMemberViewSettings
    {
        if (self::$instance != null) {
            return self::$instance;
        }
        return self::$instance = new ilMemberViewSettings();
    }

    /**
     * @return int|null
     */
    public function getContainer() : ?int
    {
        return $this->container;
    }

    /**
     * @return int
     */
    public function getCurrentRefId() : int
    {
        return $this->current_ref_id;
    }

    /**
     * @param int $container
     */
    public function setContainer(int $container)
    {
        $this->container = $container;
        ilSession::set(self::SESSION_MEMBER_VIEW_CONTAINER, $this->container);
        ilSession::set('il_cont_admin_panel', false);
    }

    /**
     * Check if member view currently enabled
     * @return bool
     */
    public function isActive() : bool
    {
        static $mv_status;
        if (!isset($mv_status)) {
            if (!$this->active) {
                // Not active
                return $mv_status = false;
            }

            if (!$this->getCurrentRefId()) {
                // No ref id given => mail, search, personal desktop menu in other tab
                return $mv_status = false;
            }

            if (!in_array($this->getCurrentRefId(), $this->container_items) and
                $this->getContainer() != $this->getCurrentRefId()
            ) {
                // outside of course
                return $mv_status = false;
            }
            return $mv_status = true;
        }

        return $mv_status;
    }
    
    /**
     * Check if member view is currently enabled for given ref id
     * @param int $a_ref_id
     * @return bool
     */
    public function isActiveForRefId(int $a_ref_id) : bool
    {
        if (!$this->active || !(int) $a_ref_id) {
            return false;
        }
        
        if (
            !in_array($a_ref_id, $this->container_items) &&
            $this->getContainer() != $a_ref_id) {
            return false;
        }
        return true;
    }
    
    /**
     * Enable member view for this session and container.
     * @param int $a_ref_id
     */
    public function activate(int $a_ref_id) : void
    {
        $this->active = true;
        $this->setContainer($a_ref_id);
    }
    
    /**
     * Deactivate member view
     * @return
     */
    public function deactivate() : void
    {
        $this->active = false;
        $this->container = null;
        ilSession::clear(self::SESSION_MEMBER_VIEW_CONTAINER);
    }
    
    /**
     * Toggle activation status
     * @param int  $a_ref_id
     * @param bool $a_activation
     */
    public function toggleActivation(int $a_ref_id, bool $a_activation) : void
    {
        if ($a_activation) {
            $this->activate($a_ref_id);
        } else {
            $this->deactivate($a_ref_id);
        }
    }
    
    /**
     * Check if members view is enabled in the administration
     * @return bool
     */
    public function isEnabled() : bool
    {
        return (bool) $this->enabled;
    }
    
    /**
     * Read settings
     */
    protected function read() : void
    {
        $current_ref_id = $this->findEffectiveRefId();
        
        // member view is always enabled
        // (2013-06-18, http://www.ilias.de/docu/goto_docu_wiki_1357_Reorganising_Administration.html)

        // $this->enabled = $ilSetting->get('preview_learner');
        $this->enabled = true;

        if (ilSession::get(self::SESSION_MEMBER_VIEW_CONTAINER)) {
            $this->active = true;
            $this->container = (int) ilSession::get(self::SESSION_MEMBER_VIEW_CONTAINER);
            $this->container_items = $this->tree->getSubTreeIds($this->getContainer());

            // deactivate if out of scope
            if (
                $this->getCurrentRefId() &&
                !in_array($this->getCurrentRefId(), $this->container_items) &&
                $this->getCurrentRefId() != $this->getContainer()
            ) {
                $this->deactivate();
            }
        }
    }
    
    /**
     * Find effective ref_id for request
     */
    protected function findEffectiveRefId()
    {
        if ($this->ctrl->isAsynch()) {
            // Ignore asynchronous requests
            return;
        }

        $ref_id = (int) $this->request->getQueryParams()['ref_id'] ?? 0;
        if ($ref_id) {
            return $this->current_ref_id = $ref_id;
        }
        $target_str = (string) $this->request->getQueryParams()['target'] ?? '';
        if (strlen($target_str)) {
            $target_arr = explode('_', (string) $target_str);
            if (isset($target_arr[1]) && (int) $target_arr[1]) {
                $this->current_ref_id = (int) $target_arr[1];
            }
        }
    }
}
