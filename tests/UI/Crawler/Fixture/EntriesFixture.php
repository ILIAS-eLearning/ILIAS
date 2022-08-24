<?php

return array(
    'Entry1' =>
        array(
            'id' => 'Entry1',
            'title' => 'Entry1Title',
            'abstract' => 1,
            'status_entry' => 'Proposed',
            'status_implementation' => 'Partly implemented',
            'description' =>
                array(
                    'purpose' => 'What is to be done by this control',
                    'composition' => 'What happens if the control is operated',
                    'effect' => 'What happens if the control is operated',
                    'rivals' =>
                        array(
                            'Rival 1' => 'What other controls are similar, what is their distinction',
                        ),
                ),
            'background' => 'Relevant academic information',
            'context' =>
                array(
                    0 => 'The context states where this control is used specifically (this list might not be complete) and how common is this control used',
                ),
            'selector' => '',
            'feature_wiki_references' =>
                array(
                ),
            'rules' =>
                array(
                    'usage' =>
                        array(
                            1 => 'Where and when an element is to be used or not.',
                        ),
                    'composition' =>
                        array(
                        ),
                    'interaction' =>
                        array(
                            2 => 'How the interaction with this object takes place.',
                        ),
                    'wording' =>
                        array(
                            3 => 'How the wording of labels or captions must be.',
                        ),
                    'ordering' =>
                        array(
                            5 => 'How different elements of this instance are to be ordered.',
                        ),
                    'style' =>
                        array(
                            4 => 'How this element should look like.',
                        ),
                    'responsiveness' =>
                        array(
                            6 => 'How this element behaves on changing screen sizes',
                        ),
                    'accessibility' =>
                        array(
                            7 => 'How this element is made accessible',
                        ),
                ),
            'parent' => false,
            'children' =>
                array("Entry2"),
            'less_variables' =>
                array(
                ),
            'path' => 'src/UI/Factory',
            'namespace' => 'ILIAS\\UI\\Factory',
        ),
    'Entry2' =>
        array(
            'id' => 'Entry2',
            'title' => 'Entry2Title',
            'abstract' => 1,
            'status_entry' => 'Proposed',
            'status_implementation' => 'Partly implemented',
            'description' =>
                array(
                    'purpose' => 'What is to be done by this control',
                    'composition' => 'What happens if the control is operated',
                    'effect' => 'What happens if the control is operated',
                    'rivals' =>
                        array(
                            'Rival 1' => 'What other controls are similar, what is their distinction',
                        ),
                ),
            'background' => 'Relevant academic information',
            'context' =>
                array(
                    0 => 'The context states where this control is used specifically (this list might not be complete) and how common is this control used',
                ),
            'selector' => '',
            'feature_wiki_references' =>
                array(
                ),
            'rules' =>
                array(
                    'usage' =>
                        array(
                            1 => 'Where and when an element is to be used or not.',
                        ),
                    'composition' =>
                        array(
                        ),
                    'interaction' =>
                        array(
                            2 => 'How the interaction with this object takes place.',
                        ),
                    'wording' =>
                        array(
                            3 => 'How the wording of labels or captions must be.',
                        ),
                    'ordering' =>
                        array(
                            5 => 'How different elements of this instance are to be ordered.',
                        ),
                    'style' =>
                        array(
                            4 => 'How this element should look like.',
                        ),
                    'responsiveness' =>
                        array(
                            6 => 'How this element behaves on changing screen sizes',
                        ),
                    'accessibility' =>
                        array(
                            7 => 'How this element is made accessible',
                        ),
                ),
            'parent' => "Entry1",
            'children' =>
                array(),
            'less_variables' =>
                array(
                ),
            'path' => 'src/UI/Factory',
            'namespace' => 'ILIAS\\UI\\Factory'
        ),
);
