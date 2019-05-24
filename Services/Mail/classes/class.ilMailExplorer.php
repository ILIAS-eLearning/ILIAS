<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class Mail Explorer
 * class for explorer view for mailboxes
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilMailExplorer extends ilTreeExplorerGUI
{
    /** @var \ilLanguage */
    protected $lng;

    /** @var \ilCtrl */
    protected $ctrl;

    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $httpRequest;

    /**
     * ilMailExplorer constructor.
     * @param $a_parent_obj
     * @param $a_parent_cmd
     * @param $a_user_id
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
    {
        global $DIC;

        $this->lng         = $DIC->language();
        $this->ctrl        = $DIC->ctrl();
        $this->httpRequest = $DIC->http()->request();

        $this->tree = new ilTree($a_user_id);
        $this->tree->setTableNames('mail_tree', 'mail_obj_data');

        parent::__construct('mail_exp', $a_parent_obj, $a_parent_cmd, $this->tree);

        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField('title,m_type');
    }

    function getNodeContent($a_node)
    {
        $content = $a_node['title'];

        if ($a_node['child'] == $this->getNodeId($this->getRootNode())) {
            $content = $this->lng->txt('mail_folders');
        } elseif ($a_node['depth'] < 3) {
            $content = $this->lng->txt('mail_' . $a_node['title']);
        }

        return $content;
    }

    function getNodeIcon($a_node)
    {
        if ($a_node['child'] == $this->getNodeId($this->getRootNode())) {
            $icon = ilUtil::getImagePath('icon_mail.svg');
        } else {
            $iconType = $a_node['m_type'];
            if ($a_node['m_type'] === 'user_folder') {
                $iconType = 'local';
            }

            $icon = ilUtil::getImagePath('icon_' . $iconType . '.svg');
        }

        return $icon;
    }

    function getNodeIconAlt($a_node)
    {
        $text = $this->lng->txt('icon') . ' ' . $this->lng->txt($a_node['m_type']);

        if ($a_node['child'] == $this->getNodeId($this->getRootNode())) {
            $text = $this->lng->txt('icon') . ' ' . $this->lng->txt('mail_folders');
        }

        return $text;
    }

    function getNodeHref($a_node)
    {
        if ($a_node['child'] == $this->getNodeId($this->getRootNode())) {
            $a_node['child'] = 0;
        }

        $this->ctrl->setParameterByClass('ilMailFolderGUI', 'mobj_id', $a_node['child']);
        $href = $this->ctrl->getLinkTargetByClass('ilMailFolderGUI');
        $this->ctrl->clearParametersByClass('ilMailFolderGUI');

        return $href;
    }

    function isNodeHighlighted($a_node)
    {
        $folderId = (int) ($this->httpRequest->getQueryParams()['mobj_id'] ?? 0);

        if (
            $a_node['child'] == $folderId ||
            (0 === $folderId && $a_node['child'] == $this->getNodeId($this->getRootNode()))
        ) {
            return true;
        }

        return false;
    }
}