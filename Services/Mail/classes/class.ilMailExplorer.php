<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Tree\Node\Factory;
use ILIAS\UI\Component\Tree\Node\Node;
use ILIAS\UI\Component\Tree\Tree;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class Mail Explorer
 * class for explorer view for mailboxes
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Michael Jansen <mjansen@databay.de>
 */
class ilMailExplorer extends ilTreeExplorerGUI
{
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    private ilMailGUI $parentObject;
    protected int $currentFolderId = 0;

    public function __construct(ilMailGUI $parentObject, int $userId)
    {
        global $DIC;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->parentObject = $parentObject;

        $this->tree = new ilTree($userId);
        $this->tree->setTableNames('mail_tree', 'mail_obj_data');

        parent::__construct('mail_exp', $parentObject, '', $this->tree);

        $this->initFolder();

        $this->setSkipRootNode(true);
        $this->setAjax(false);
        $this->setOrderField('title,m_type');
    }

    protected function initFolder() : void
    {
        $folderId = 0;
        if ($this->http->wrapper()->post()->has('mobj_id')) {
            $folderId = $this->http->wrapper()->post()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        } elseif ($this->http->wrapper()->query()->has('mobj_id')) {
            $folderId = $this->http->wrapper()->query()->retrieve('mobj_id', $this->refinery->kindlyTo()->int());
        } else {
            $folderId = $this->refinery->kindlyTo()->int()->transform(ilSession::get('mobj_id'));
        }

        $this->currentFolderId = $folderId;
    }

    public function getTreeLabel() : string
    {
        return $this->lng->txt("mail_folders");
    }

    public function getTreeComponent() : Tree
    {
        $f = $this->ui->factory();

        $tree = $f->tree()
            ->expandable($this->getTreeLabel(), $this)
            ->withData($this->tree->getChilds($this->tree->readRootId()))
            ->withHighlightOnNodeClick(false);

        return $tree;
    }

    public function build(
        Factory $factory,
        $record,
        $environment = null
    ) : Node {
        $node = parent::build($factory, $record, $environment);

        return $node->withHighlighted($this->currentFolderId === (int) $record['child']);
    }

    protected function getNodeStateToggleCmdClasses($record) : array
    {
        return [
            ilMailGUI::class,
        ];
    }

    public function getNodeContent($a_node) : string
    {
        $content = $a_node['title'];

        if ((int) $a_node['child'] === (int) $this->getNodeId($this->getRootNode())) {
            $content = $this->lng->txt('mail_folders');
        } elseif ($a_node['depth'] < 3) {
            $content = $this->lng->txt('mail_' . $a_node['title']);
        }

        return $content;
    }

    public function getNodeIconAlt($a_node) : string
    {
        return $this->getNodeContent($a_node);
    }

    public function getNodeIcon($a_node) : string
    {
        if ((int) $a_node['child'] === (int) $this->getNodeId($this->getRootNode())) {
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

    public function getNodeHref($a_node) : string
    {
        if ((int) $a_node['child'] === (int) $this->getNodeId($this->getRootNode())) {
            $a_node['child'] = 0;
        }

        $this->ctrl->setParameterByClass(ilMailFolderGUI::class, 'mobj_id', $a_node['child']);
        $href = $this->ctrl->getLinkTargetByClass([ilMailGUI::class, ilMailFolderGUI::class]);
        $this->ctrl->clearParametersByClass(ilMailFolderGUI::class);

        return $href;
    }
}
