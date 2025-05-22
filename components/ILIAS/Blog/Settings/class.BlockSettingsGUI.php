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

namespace ILIAS\Blog\Settings;

use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Blog\InternalDataService;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Table\Ordering;

class BlockSettingsGUI
{
    protected SettingsManager $settings;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected int $obj_id,
        protected bool $in_repository
    ) {
        $this->settings = $domain->blogSettings();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->gui->ctrl();
        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("edit");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["edit", "saveOrder"])) {
                    $this->$cmd();
                }
        }
    }

    protected function edit(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $r = $this->gui->ui()->renderer();
        $mt->setContent($r->render($this->getTable()));
    }

    protected function getTable(): Ordering
    {
        $f = $this->gui->ui()->factory();
        $lng = $this->domain->lng();
        $settings = $this->settings;
        $ctrl = $this->gui->ctrl();

        $request = $this->gui->http()->request();

        $columns = [
            'block' => $f->table()->column()->text($lng->txt("blog_side_blocks"))
                                            ->withHighlight(true)
        ];

        $actions = [];

        $data_retrieval = new class (
            $settings,
            $this->obj_id,
            $this->in_repository
        ) implements OrderingRetrieval {
            protected array $records;

            public function __construct(
                protected SettingsManager $settings,
                protected int $blog_id,
                protected bool $in_repository
            ) {
            }

            public function getRows(
                OrderingRowBuilder $row_builder,
                array $visible_column_ids
            ): \Generator {
                foreach ($this->settings->getOrderingOptions(
                    $this->settings->getByObjId(
                        $this->blog_id
                    ),
                    $this->in_repository
                ) as $id => $option) {
                    yield $row_builder->buildOrderingRow(
                        $id,
                        ["block" => $option]
                    );
                }
            }
        };

        $target = $ctrl->getLinkTargetByClass([self::class], "saveOrder");
        $target = (new URI(ILIAS_HTTP_PATH . "/" . $target));
        $table = $f->table()->ordering($data_retrieval, $target, $lng->txt("blog_nav_sortorder"), $columns)
                   ->withActions($actions)
                   ->withRequest($request);

        return $table;
    }

    protected function saveOrder(): void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();

        $this->settings->saveOrder(
            $this->obj_id,
            $this->getTable()->getData()
        );

        $mt->setOnScreenMessage("success", $lng->txt("msg_obj_modified"), true);
        $ctrl->redirectByClass(self::class, "edit");
    }
}
