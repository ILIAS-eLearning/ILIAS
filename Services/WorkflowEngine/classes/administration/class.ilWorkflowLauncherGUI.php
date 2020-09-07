<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilWorkflowLauncherGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowLauncherGUI
{
    /** @var string $form_action */
    protected $form_action;

    /** @var \ilLanguage $lng */
    protected $lng;

    /**
     * ilWorkflowLauncherGUI constructor.
     *
     * @param string $form_action
     */
    public function __construct($form_action)
    {
        global $DIC;
        /** @var ilLanguage $lng */
        $this->lng = $DIC['lng'];

        $this->form_action = $form_action;
    }

    /**
     * @param array $input_vars
     *
     * @return ilPropertyFormGUI
     */
    public function getForm($input_vars)
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
            $item->setRequired($input_var['requirement'] == 'required' ? true : false);
            $item->setInfo($input_var['description']);
            $form->addItem($item);
        }

        $form->addCommandButton('start', $this->lng->txt('start_process'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        return $form;
    }

    public function getRepositoryObjectSelector($config)
    {
        /** @var ilTree $tree */
        global $DIC;
        $tree = $DIC['tree'];

        $item = new ilSelectInputGUI($config['caption'], $config['name']);

        $children = $tree->getFilteredSubTree($tree->getRootId());

        $options = array();
        foreach ($children as $child) {
            if (strtolower($config['allowedtype']) != $child['type']) {
                continue;
            }

            $path = $tree->getPathFull($child['child']);
            $option_elements = array();
            foreach ($path as $node) {
                if ($node['type'] == 'root') {
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
