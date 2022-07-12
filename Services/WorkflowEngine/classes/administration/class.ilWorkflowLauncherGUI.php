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

/**
 * Class ilWorkflowLauncherGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowLauncherGUI
{
    protected string $form_action;
    protected ilLanguage $lng;

    public function __construct(string $form_action)
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->form_action = $form_action;
    }

    /**
     * @param array $input_vars
     * @return ilPropertyFormGUI
     */
    public function getForm(array $input_vars) : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('input_variables_required'));
        $form->setDescription($this->lng->txt('input_variables_desc'));

        foreach ($input_vars as $input_var) {
            $item = null;
            switch (strtolower($input_var['type'])) {
                case 'robjselect':
                    $item = $this->getRepositoryObjectSelector($input_var);
                    break;

                case 'text':
                default:
                    $item = new ilTextInputGUI($input_var['caption'], $input_var['name']);
                    break;

            }
            $item->setRequired($input_var['requirement'] === 'required');
            $item->setInfo($input_var['description']);
            $form->addItem($item);
        }

        $form->addCommandButton('start', $this->lng->txt('start_process'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        return $form;
    }

    public function getRepositoryObjectSelector(array $config) : ilSelectInputGUI
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $item = new ilSelectInputGUI($config['caption'], $config['name']);

        $children = $tree->getFilteredSubTree($tree->getRootId());

        $options = [];
        foreach ($children as $child) {
            if (strtolower($config['allowedtype']) !== $child['type']) {
                continue;
            }

            $path = $tree->getPathFull($child['child']);
            $option_elements = [];
            foreach ($path as $node) {
                if ($node['type'] === 'root') {
                    continue;
                }
                $option_elements[] = $node['title'];
            }

            $options[$child['child']] = implode(' / ', $option_elements);
        }

        $item->setOptions($options);

        return $item;
    }
}
