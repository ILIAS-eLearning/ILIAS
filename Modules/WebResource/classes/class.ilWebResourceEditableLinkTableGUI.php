<?php declare(strict_types=1);

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
 
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPService;

/**
 * TableGUI class for search results
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesWebResource
 */
class ilWebResourceEditableLinkTableGUI extends ilTable2GUI
{
    protected Refinery $refinery;
    protected HTTPService $http;
    protected ilSetting $settings;

    protected ilWebLinkRepository $web_link_repo;
    protected array $invalid = [];

    /**
     * TODO Move most of this stuff to an init method.
     */
    public function __construct(?object $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->settings = $DIC->settings();
        $this->web_link_repo = new ilWebLinkDatabaseRepository(
            $this->getParentObject()->getObject()->getId()
        );

        $this->setTitle($this->lng->txt('webr_edit_links'));
        $this->addColumn('', '', '1px');
        $this->addColumn($this->lng->txt('title'), 'title', '25%');
        $this->addColumn($this->lng->txt('target'), 'target', '25%');
        $this->addColumn($this->lng->txt('webr_active'), 'active');

        $this->setEnableHeader(true);
        $this->setFormAction(
            $this->ctrl->getFormAction($this->getParentObject())
        );
        $this->setRowTemplate(
            "tpl.webr_editable_link_row.html",
            'Modules/WebResource'
        );
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setSelectAllCheckbox('link_ids');

        $this->addMultiCommand('confirmDeleteLink', $this->lng->txt('delete'));
        $this->addCommandButton('updateLinks', $this->lng->txt('save'));
    }

    public function setInvalidLinks(array $a_links) : void
    {
        $this->invalid = $a_links;
    }

    public function getInvalidLinks() : array
    {
        return $this->invalid;
    }

    /**
     * @param int[] $a_link_ids
     */
    public function parseSelectedLinks(array $a_link_ids) : void
    {
        $rows = [];

        $items = $this->web_link_repo->getAllItemsAsContainer()
                                     ->sort()
                                     ->getItems();

        foreach ($items as $item) {
            if (!in_array($item->getLinkId(), $a_link_ids)) {
                continue;
            }

            $tmp['id'] = $item->getLinkId();
            $tmp['title'] = $item->getTitle();
            $tmp['description'] = $item->getDescription();
            $tmp['target'] = $item->getTarget();
            $tmp['active'] = $item->isActive();
            $tmp['params'] = [];

            $rows[] = $tmp;
        }
        $this->setData($rows);
    }

    public function updateFromPost() : void
    {
        $request_link_info = (array) (
            $this->http->request()
                                                 ->getParsedBody()['links'] ?? []
        );

        $rows = [];
        foreach ($this->getData() as $link) {
            $link_id = $link['id'];
            $tmp = $link;
            $tmp['title'] = $request_link_info[$link_id]['title'] ?? null;
            $tmp['description'] = $request_link_info[$link_id]['desc'] ?? null;
            $tmp['target'] = $request_link_info[$link_id]['tar'] ?? null;
            $tmp['active'] = $request_link_info[$link_id]['act'] ?? null;
            $tmp['value'] = $request_link_info[$link_id]['val'] ?? null;
            $tmp['name'] = $request_link_info[$link_id]['nam'] ?? null;
            $tmp['params'] = [];
            $rows[] = $tmp;
        }
        $this->setData($rows);
    }

    public function parse() : void
    {
        $rows = [];

        $items = $this->web_link_repo->getAllItemsAsContainer()
                                     ->sort()
                                     ->getItems();

        foreach ($items as $item) {
            $tmp['id'] = $item->getLinkId();
            $tmp['title'] = $item->getTitle();
            $tmp['description'] = $item->getDescription();
            $tmp['target'] = $item->getTarget();
            $tmp['active'] = $item->isActive();

            /**
             * This is a bit of a messy solution, but to avoid implicit method calls
             * I prefer not to pass objects as table data.
             */
            $tmp['params'] = array_map(
                function ($p) {
                    return [
                    'info' => $p->getInfo(),
                    'param_id' => $p->getParamId()
                ];
                },
                $item->getParameters()
            );

            $rows[] = $tmp;
        }
        $this->setData($rows);
    }

    protected function fillRow(array $a_set) : void
    {
        if (!stristr($a_set['target'], '|')) {
            $this->tpl->setCurrentBlock('external');
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->setVariable(
                'VAL_TARGET',
                ilLegacyFormElementsUtil::prepareFormOutput(
                    $a_set['target']
                )
            );
        } else {
            $this->ctrl->setParameterByClass(
                'ilinternallinkgui',
                'postvar',
                'tar_' . $a_set['id']
            );
            $trigger_link = [
                get_class($this->parent_obj),
                'ilinternallinkgui'
            ];
            $trigger_link = $this->ctrl->getLinkTargetByClass(
                $trigger_link,
                '',
                '',
                true,
                false
            );
            $this->ctrl->setParameterByClass(
                'ilinternallinkgui',
                'postvar',
                ''
            );

            $this->tpl->setCurrentBlock('internal');
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->setVariable('VAL_TRIGGER_INTERNAL', $trigger_link);
            $this->tpl->setVariable(
                'TXT_TRIGGER_INTERNAL',
                $this->lng->txt('edit')
            );

            // info about current link
            if ($a_set['target']) {
                $parts = explode('|', $a_set['target']);

                $this->tpl->setVariable('VAL_INTERNAL_TYPE', $parts[0]);
                $this->tpl->setVariable('VAL_INTERNAL_ID', $parts[1]);

                $parts = ilLinkInputGUI::getTranslatedValue($a_set['target']);
                if ($parts !== []) {
                    $this->tpl->setVariable(
                        'TXT_TRIGGER_INFO',
                        $parts['type'] . ' "' . $parts['name'] . '"'
                    );
                }
            }
        }

        $this->tpl->parseCurrentBlock();

        // Active
        $this->tpl->setVariable(
            'VAL_ACTIVE',
            ilLegacyFormElementsUtil::formCheckbox(
                $a_set['active'],
                'links[' . $a_set['id'] . '][act]',
                '1'
            )
        );

        // Dynamic parameters
        foreach ($a_set['params'] as $param) {
            $this->tpl->setCurrentBlock('dyn_del_row');
            $this->tpl->setVariable('TXT_DYN_DEL', $this->lng->txt('delete'));
            $this->ctrl->setParameterByClass(
                get_class($this->getParentObject()),
                'param_id',
                $param['param_id']
            );
            $this->ctrl->setParameterByClass(
                get_class($this->getParentObject()),
                'link_id',
                $a_set['id']
            );
            $this->tpl->setVariable(
                'DYN_DEL_LINK',
                $this->ctrl->getLinkTarget(
                    $this->getParentObject(),
                    'deleteParameter'
                )
            );
            $this->tpl->setVariable(
                'VAL_DYN',
                $param['info']
            );
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set['params']) {
            $this->tpl->setCurrentBlock('dyn_del_rows');
            $this->tpl->setVariable(
                'TXT_EXISTING',
                $this->lng->txt('links_existing_params')
            );
            $this->tpl->parseCurrentBlock();
        }

        if ($this->settings->get('links_dynamic')) {
            $this->tpl->setCurrentBlock('dyn_add');
            $this->tpl->setVariable(
                'TXT_DYN_ADD',
                $this->lng->txt('links_add_param')
            );

            $this->tpl->setVariable(
                'TXT_DYN_NAME',
                $this->lng->txt('links_name')
            );
            $this->tpl->setVariable(
                'TXT_DYN_VALUE',
                $this->lng->txt('links_value')
            );
            $this->tpl->setVariable('VAL_DYN_NAME', $a_set['name'] ?? '');
            $this->tpl->setVariable('DYN_ID', $a_set['id']);
            $this->tpl->setVariable(
                'SEL_DYN_VAL',
                ilLegacyFormElementsUtil::formSelect(
                    $a_set['value'] ?? 0,
                    'links[' . $a_set['id'] . '][val]',
                    array_map(function ($s) {
                        return $this->lng->txt($s);
                    }, ilWebLinkBaseParameter::VALUES_TEXT),
                    false,
                    true
                )
            );
            $this->tpl->parseCurrentBlock();
        }

        if (in_array($a_set['id'], $this->getInvalidLinks())) {
            $this->tpl->setVariable('CSS_ROW', 'warn');
        }

        // Check
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable(
            'VAL_CHECKBOX',
            ilLegacyFormElementsUtil::formCheckbox(
                false,
                'link_ids[]',
                (string) $a_set['id']
            )
        );

        // Column title
        $this->tpl->setVariable('TXT_TITLE', $this->lng->txt('title'));
        $this->tpl->setVariable(
            'VAL_TITLE',
            ilLegacyFormElementsUtil::prepareFormOutput(
                $a_set['title']
            )
        );
        $this->tpl->setVariable('TXT_DESC', $this->lng->txt('description'));
        $this->tpl->setVariable(
            'VAL_DESC',
            ilLegacyFormElementsUtil::prepareFormOutput(
                $a_set['description'] ?? ''
            )
        );

        // Column Target
        $this->tpl->setVariable('TXT_TARGET', $this->lng->txt('target'));
    }
}
