<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilWorkflowArmerGUI
 *
 * GUI showed when 'arming' a workflow definition, preparing it to listen for incoming events.
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowArmerGUI
{
    /** @var string $form_action */
    protected $form_action;

    /** @var \ilLanguage $lng */
    protected $lng;

    /** @var \ilTree $tree */
    protected $tree;

    /**
     * ilWorkflowLauncherGUI constructor.
     *
     * @param string $form_action
     */
    public function __construct($form_action)
    {
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->tree = $DIC['tree'];

        $this->form_action = $form_action;
    }

    /**
     * @param array $input_vars
     * @param array $event_definition
     *
     * @return \ilPropertyFormGUI
     */
    public function getForm($input_vars, $event_definition)
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('input_variables_required'));
        $form->setDescription($this->lng->txt('input_static_variables_desc'));

        $process_id_input = new ilHiddenInputGUI('process_id');
        $process_id_input->setValue(stripslashes($_GET['process_id']));
        $form->addItem($process_id_input);

        $event = $event_definition[0];
        $this->addStartEventInputItems($this->lng, $event, $form);

        $this->addStaticInputItems($this->lng, $input_vars, $form);

        $form->addCommandButton('listen', $this->lng->txt('start_listening'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));
        return $form;
    }

    public function getRepositoryObjectSelector($config)
    {
        $item = new ilSelectInputGUI($config['caption'], $config['name']);

        $children = $this->tree->getFilteredSubTree($this->tree->getRootId());

        $options = array();
        foreach ($children as $child) {
            if (strtolower($config['allowedtype']) != $child['type']) {
                continue;
            }

            $path = $this->tree->getPathFull($child['child']);
            $option_elements = array();
            foreach ($path as $node) {
                if ($node['type'] == 'root') {
                    continue;
                }
                $option_elements[] = $node['title'];
            }

            $options[ $child['child'] ] = implode(' / ', $option_elements);
        }

        $item->setOptions($options);

        return $item;
    }

    /**
     * @param ilLanguage        $lng
     * @param array             $input_vars
     * @param ilPropertyFormGUI $form
     */
    public function addStaticInputItems($lng, $input_vars, $form)
    {
        if (count($input_vars)) {
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($lng->txt('static_input_form_header'));
            $form->addItem($section);
        }

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
    }

    /**
     * @param ilLanguage        $lng
     * @param array             $event
     * @param ilPropertyFormGUI $form
     */
    public function addStartEventInputItems($lng, $event, $form)
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($lng->txt('start_event_form_header'));
        $form->addItem($section);

        $type_input = new ilTextInputGUI($lng->txt('type'), 'se_type');
        $type_input->setValue($event['type']);
        $type_input->setDisabled(true);
        $form->addItem($type_input);

        $content_input = new ilTextInputGUI($lng->txt('content'), 'se_content');
        $content_input->setValue($event['content']);
        $content_input->setDisabled(true);
        $form->addItem($content_input);

        $subject_type_input = new ilTextInputGUI($lng->txt('subject_type'), 'se_subject_type');
        $subject_type_input->setValue($event['subject_type']);
        $subject_type_input->setDisabled(true);
        $form->addItem($subject_type_input);

        $subject_selector = new ilRadioGroupInputGUI($lng->txt('subject_id'), 'se_subject_id');

        $subject_by_id_selector = new ilRadioOption($lng->txt('subject_by_id'), 'se_subject_by_id');
        $subject_id_input = new ilNumberInputGUI($lng->txt('subject_id'), 'se_subject_id');
        $subject_id_input->setValue($event['subject_id']);
        $subject_by_id_selector->addSubItem($subject_id_input);
        $subject_selector->addOption($subject_by_id_selector);

        $subject_by_mask_selector = new ilRadioOption($lng->txt('subject_by_mask'), 'se_subject_by_mask');
        $subject_by_mask_selector->setValue($event['subject_id']);
        $subject_selector->addOption($subject_by_mask_selector);

        $subject_selector->setValue($event['subject_id']);
        $form->addItem($subject_selector);

        $context_type_input = new ilTextInputGUI($lng->txt('context_type'), 'se_context_type');
        $context_type_input->setValue($event['context_type']);
        $context_type_input->setDisabled(true);
        $form->addItem($context_type_input);

        $context_selector = new ilRadioGroupInputGUI($lng->txt('context_id'), 'se_context_id');

        $context_by_id_selector = new ilRadioOption($lng->txt('context_by_id'), 'se_context_by_id');
        $context_id_input = new ilNumberInputGUI($lng->txt('context_id'), 'se_context_id');
        $context_id_input->setValue($event['context_id']);
        $context_by_id_selector->addSubItem($context_id_input);
        $context_selector->addOption($context_by_id_selector);

        $context_by_mask_selector = new ilRadioOption($lng->txt('context_by_mask'), 'se_context_by_mask');
        $context_by_mask_selector->setValue($event['subject_id']);
        $context_selector->addOption($context_by_mask_selector);

        $context_selector->setValue($event['subject_id']);
        $form->addItem($context_selector);
    }
}
