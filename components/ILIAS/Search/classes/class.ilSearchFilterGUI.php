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

use ILIAS\UI\Component\Input\Container\Filter;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSearchFilterGUI
{
    protected ilUIFilterService $filter_service;
    protected \ILIAS\UI\Renderer $renderer;
    protected ilNavigationHistory $nav_history;
    protected Filter\Standard $filter;

    public function __construct(object $parent_gui, int $mode)
    {
        global $DIC;

        $this->filter_service = $DIC->uiService()->filter();
        $this->renderer = $DIC->ui()->renderer();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $field_factory = $DIC->ui()->factory()->input()->field();
        $txt = static function (string $id) use ($DIC): string {
            return $DIC->language()->txt($id);
        };

        $scope_options[ROOT_FOLDER_ID] = $txt("repository");
        $last_items = $this->nav_history->getItems();
        $cnt = 0;
        foreach ($last_items as $item) {
            if ($cnt++ >= 10) {
                break;
            }
            $scope_options[(string) $item["ref_id"]] = strip_tags($item["title"]);
        }
        $inputs["search_scope"] = $field_factory->select($txt("scope"), $scope_options)
                                                ->withRequired(true)
                                                ->withValue(ROOT_FOLDER_ID);
        $inputs_activated[] = true;

        $enabled_types = ilSearchSettings::getInstance()->getEnabledLuceneItemFilterDefinitions();

        if ($mode === ilSearchBaseGUI::SEARCH_FORM_LUCENE) {
            $enabled_types += ilSearchSettings::getInstance()->getEnabledLuceneMimeFilterDefinitions();
        }

        if (ilSearchSettings::getInstance()->isLuceneItemFilterEnabled() && !empty($enabled_types)) {
            $type_options = [];
            foreach ($enabled_types as $type => $pval) {
                $type_options[$type] = $txt($pval["trans"]);
            }
            $inputs["search_type"] = $field_factory->multiSelect($txt("search_type"), $type_options);
            $inputs_activated[] = true;
        }

        if (ilSearchSettings::getInstance()->isDateFilterEnabled()) {
            $inputs["search_date"] = $field_factory->duration($txt("create_date"));
            $inputs_activated[] = true;
        }

        $this->filter = $this->filter_service->standard(
            "search_filter",
            $DIC->ctrl()->getLinkTarget($parent_gui, "performSearchFilter"),
            $inputs,
            $inputs_activated,
            false,
            true
        );
    }

    public function getHTML(): string
    {
        return $this->renderer->render($this->filter);
    }

    public function getFilter(): Filter\Standard
    {
        return $this->filter;
    }

    public function getData(): ?array
    {
        return $this->filter_service->getData($this->filter);
    }
}
