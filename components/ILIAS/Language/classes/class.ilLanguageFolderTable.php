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

use ILIAS\UI\Component\Table as DataTableInterface;
use ILIAS\UI\Implementation\Component\Table as DataTable;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;

/**
 * Table class for
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilLanguageFolderTable implements DataTableInterface\DataRetrieval
{
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $row_id_token;
    protected ILIAS\UI\Factory $ui_factory;
    protected ilLanguage $lng;
    protected ilObjLanguageFolder $folder;
    protected ilCtrlInterface $ctrl;

    public function __construct(
        ilObjLanguageFolder $a_folder,
        URLBuilder $url_builder,
        URLBuilderToken $action_token,
        URLBuilderToken $row_id_token
    ) {
        global $DIC;
        $this->ui_factory = $DIC['ui.factory'];
        $this->lng = $DIC->language();
        $this->folder = $a_folder;
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;
        $this->ctrl = $DIC->ctrl();
    }

    public function getTable(): DataTable\Data
    {
        return $this->ui_factory->table()->data(
            '',
            $this->getColums(),
            $this
        )->withActions($this->getActions());
    }

    protected function getColums(): array
    {
        $f = $this->ui_factory;
        return [
            'language' => $f->table()->column()->link($this->lng->txt("language"))->withIsSortable(false),
            'status' => $f->table()->column()->text($this->lng->txt("status"))->withIsSortable(false),
            'users' => $f->table()->column()->text($this->lng->txt("users"))->withIsSortable(false),
            'last_refresh' => $f->table()->column()->text($this->lng->txt("last_refresh"))->withIsSortable(false),
            'last_change' => $f->table()->column()->text($this->lng->txt("last_change"))->withIsSortable(false)
        ];
    }

    protected function getActions(): array
    {
        $actions = array_merge(
            $this->buildAction('refresh', 'standard', true),
            $this->buildAction('install', 'standard'),
            $this->buildAction('install_local', 'standard'),
            $this->buildAction('uninstall', 'standard', true),
            $this->buildAction('lang_uninstall_changes', 'standard', true),
            $this->buildAction('setSystemLanguage', 'single'),
            $this->buildAction('setUserLanguage', 'single'),
        );
        return $actions;
    }

    protected function buildAction(string $act, string $type, bool $async = false): array
    {
        $action = $this->ui_factory->table()->action()
                                   ->$type(
                                       $this->lng->txt($act),
                                       $this->url_builder->withParameter($this->action_token, $act),
                                       $this->row_id_token
                                   );
        if ($async) {
            $action = $action->withAsync(true);
        }

        return [$act => $action];
    }

    /**
     * Get language data
     */
    public function getItems(?Range $range = null, ?Order $order = null): array
    {
        $languages = $this->folder->getLanguages();
        $data = [];
        $names = [];
        $installed = [];

        foreach ($languages as $k => $l) {
            $data[] = array_merge($l, ["key" => $k]);
            $names[] = $l['name'];
            $installed[] = str_starts_with($l["desc"], 'installed') ? 1 : 2;
        }

        // sort alphabetically but show installed languages first
        array_multisort($installed, SORT_ASC, $names, SORT_ASC, $data);

        if ($range) {
            $data = array_slice($data, $range->getStart(), $range->getLength());
        }

        return $data;
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        foreach ($this->getItems($range, $order) as $idx => $record) {
            $obj_id = (string) $record['obj_id'];
            $language = $record['name'];
            if ($record["status"]) {
                $language .= ' (' . $this->lng->txt($record["status"]) . ')';
            }
            $to_language = $this->url_builder
                ->withParameter($this->action_token, 'view')
                ->withParameter($this->row_id_token, $obj_id)
                ->withURI($this->getURIWithTargetClass(ilObjLanguageExtGUI::class, 'view'))
                ->buildURI()->__toString();

            $record['language'] = $this->ui_factory->link()->standard($language, $to_language)->withDisabled();

            $record['status'] = $this->lng->txt($record['desc']);
            $record['users'] = ilObjLanguage::countUsers($record["key"]);
            if ($record["desc"] !== "not_installed") {
                $record['language'] = $record['language']->withDisabled(false);
                $record['last_refresh'] = ilDatePresentation::formatDate(new ilDateTime($record["last_update"], IL_CAL_DATETIME));

                $last_change = ilObjLanguage::_getLastLocalChange($record["key"]);
                $record['last_change'] = ilDatePresentation::formatDate(new ilDateTime($last_change, IL_CAL_DATETIME));
            }

            yield $row_builder->buildDataRow($obj_id, $record)
                              ->withDisabledAction('setSystemLanguage', ($record['desc'] === 'not_installed') || ($record['key'] === $this->lng->getDefaultLanguage()))
                              ->withDisabledAction('setUserLanguage', ($record['desc'] === 'not_installed') || ($record['key'] === $this->lng->getUserLanguage()))
                              ->withDisabledAction('refresh', ($record['desc'] === 'not_installed'))
                              ->withDisabledAction('uninstall', ($record['desc'] === 'not_installed'))
                              ->withDisabledAction('lang_uninstall_changes', ($record['desc'] === 'not_installed'))
                              ->withDisabledAction('install', ($record['desc'] !== 'not_installed'))
                              ->withDisabledAction('install_local', ($record['desc'] !== 'not_installed'));
        }
    }

    protected function getURIWithTargetClass(string $target_gui, string $command): URI
    {
        return new URI(
            ILIAS_HTTP_PATH . "/" . $this->ctrl->getLinkTargetByClass(
                $target_gui,
                $command
            )
        );
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return 1;
    }
}
