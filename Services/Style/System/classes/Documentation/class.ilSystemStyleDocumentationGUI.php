<?php

declare(strict_types=1);

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;

/**
 * Renders the Overview of the Examples in the Administration
 */
class ilSystemStyleDocumentationGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ILIAS\UI\Factory $f;
    protected ILIAS\UI\Renderer $r;

    public const SHOW_TREE = 'system_styles_show_tree';
    public const DATA_PATH = './Services/Style/System/data/data.php';
    public const ROOT_FACTORY_PATH = './Services/Style/System/data/abstractDataFactory.php';

    public function __construct(
        ilGlobalTemplateInterface $tpl,
        ilCtrl $ctrl,
        ILIAS\UI\Factory $f,
        ILIAS\UI\Renderer $r
    ) {
        $this->f = $f;
        $this->r = $r;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
    }

    public function show(Entries $entries, string $current_opened_node_id)
    {
        $entry_gui = new ilKSDocumentationEntryGUI(
            $this->f,
            $this->ctrl,
            $entries,
            $current_opened_node_id
        );

        $this->tpl->setContent($this->r->render($entry_gui->createUIComponentOfEntry()));
    }
}
