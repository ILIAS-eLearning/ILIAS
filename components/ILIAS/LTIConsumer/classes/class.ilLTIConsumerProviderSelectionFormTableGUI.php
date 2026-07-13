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

use ILIAS\UI\Component\Input\Container\Filter\Standard;

/**
 * Class ilLTIConsumerProviderSelectionFormGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package components\ILIAS/LTIConsumer
 */
class ilLTIConsumerProviderSelectionFormTableGUI extends ilPropertyFormGUI
{
    /**
     * @var ilLTIConsumerProviderTableGUI
     */
    protected ilLTIConsumerProviderTableGUI $table;
    protected Standard $filter;

    /**
     * ilLTIConsumerProviderSelectionFormGUI constructor.
     * @throws ilCtrlException
     */
    public function __construct(string $newType, ilObjLTIConsumerGUI $parentGui, string $parentCmd)
    {
        global $DIC;

        parent::__construct();

        $this->table = new ilLTIConsumerProviderTableGUI($parentGui, $parentCmd);
        $this->table->enableSelectProviderForm();

        $this->filter = $this->table->getFilter();
        $filter_params = $DIC->uiService()->filter()->getData($this->filter);

        $providerList = new ilLTIConsumeProviderList();
        $providerList->setTitleFilter($filter_params['title'] ?? '');
        $providerList->setKeywordFilter($filter_params['keywords'] ?? '');
        $providerList->setHasOutcomeFilter(($filter_params['outcome'] ?? '') === '' ? null : $filter_params['outcome'] === 'yes');
        $providerList->setIsExternalFilter(($filter_params['internal'] ?? '') === '' ? null : $filter_params['internal'] !== 'yes');
        $providerList->setIsProviderKeyCustomizableFilter(($filter_params['with_key'] ?? '') === '' ? null : !($filter_params['with_key'] === 'yes'));
        $providerList->setCategoryFilter($filter_params['category'] ?? '');

        $providerList->load();

        $data = $providerList->getTableData();

        foreach ($data as $key => $value) {
            $data[$key]["own_provider"] = $value['creator'] == $DIC->user()->getId();
        }

        $this->table->setData($data);

        $this->setTitle($DIC->language()->txt($newType . '_select_provider'));
    }

    /**
     * @throws ilCtrlException
     */
    public function getHTML(): string
    {
        global $DIC;

        return "<div style='margin: 15px'>" . $DIC->ui()->renderer()->render($this->filter) . $this->table->getHTML() . "</div>";
    }
}
