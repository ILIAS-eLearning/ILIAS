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

use Psr\Http\Message\RequestInterface;
use ILIAS\Container;

/**
 * Settings for members view
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilMemberViewSettings
{
    public const SESSION_MEMBER_VIEW_CONTAINER = 'member_view_container';
    protected ilLogger $logger;
    protected RequestInterface $request;
    protected ilCtrl $ctrl;
    protected ilTree $tree;
    protected ilSetting $settings;
    private static ?ilMemberViewSettings $instance = null;
    private bool $active = false;
    private bool $enabled = false;
    private ?int $container = null;
    private array $container_items = [];
    private int $current_ref_id = 0;
    protected Container\Service $container_service;

    private function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->request = $DIC->http()->request();
        $this->ctrl = $DIC->ctrl();
        $this->logger = $DIC->logger()->cont();
        $this->read();
        $this->container_service = $DIC->container();
    }

    public static function getInstance() : ilMemberViewSettings
    {
        return self::$instance ?? (self::$instance = new ilMemberViewSettings());
    }

    public function getContainer() : ?int
    {
        return $this->container;
    }

    public function getCurrentRefId() : int
    {
        return $this->current_ref_id;
    }

    public function setContainer(int $container) : void
    {
        $this->container = $container;
        ilSession::set(self::SESSION_MEMBER_VIEW_CONTAINER, $this->container);
        $this->container_service
            ->internal()
            ->domain()
            ->content()
            ->view()
            ->setContentView();
    }

    /**
     * Check if member view currently enabled
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

            if (!in_array($this->getCurrentRefId(), $this->container_items) &&
                $this->getContainer() !== $this->getCurrentRefId()
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
     */
    public function isActiveForRefId(int $a_ref_id) : bool
    {
        if (!$this->active || !$a_ref_id) {
            return false;
        }
        
        if (
            !in_array($a_ref_id, $this->container_items) &&
            $this->getContainer() !== $a_ref_id) {
            return false;
        }
        return true;
    }
    
    /**
     * Enable member view for this session and container.
     */
    public function activate(int $a_ref_id) : void
    {
        $this->active = true;
        $this->setContainer($a_ref_id);
    }
    
    public function deactivate() : void
    {
        $this->active = false;
        $this->container = null;
        ilSession::clear(self::SESSION_MEMBER_VIEW_CONTAINER);
    }
    
    /**
     * Toggle activation status
     */
    public function toggleActivation(int $a_ref_id, bool $a_activation) : void
    {
        if ($a_activation) {
            $this->activate($a_ref_id);
        } else {
            $this->deactivate();
        }
    }
    
    /**
     * Check if members view is enabled in the administration
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
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
                $this->getCurrentRefId() !== $this->getContainer()
            ) {
                $this->deactivate();
            }
        }
    }
    
    /**
     * Find effective ref_id for request
     */
    protected function findEffectiveRefId() : int
    {
        if ($this->ctrl->isAsynch()) {
            // Ignore asynchronous requests
            return 0;
        }

        $ref_id = (int) ($this->request->getQueryParams()['ref_id'] ?? 0);
        if ($ref_id) {
            return $this->current_ref_id = $ref_id;
        }
        $target_str = (string) ($this->request->getQueryParams()['target'] ?? '');
        if ($target_str !== '') {
            $target_arr = explode('_', $target_str);
            if (isset($target_arr[1]) && (int) $target_arr[1]) {
                $this->current_ref_id = (int) $target_arr[1];
            }
        }
        return 0;
    }
}
