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
 * Matrix question GUI representation
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 */
class SurveyMatrixQuestionGUI extends SurveyQuestionGUI
{
    protected bool $show_layout_row = false;
    
    protected function initObject() : void
    {
        $this->object = new SurveyMatrixQuestion();
    }
    
    //
    // EDITOR
    //
    
    public function setQuestionTabs() : void
    {
        $this->setQuestionTabsForClass("surveymatrixquestiongui");
    }

    protected function addFieldsToEditForm(ilPropertyFormGUI $a_form) : void
    {
        // subtype
        $subtype = new ilRadioGroupInputGUI($this->lng->txt("subtype"), "type");
        $subtype->setRequired(false);
        $subtypes = array(
            "0" => "matrix_subtype_sr",
            "1" => "matrix_subtype_mr",
            //"2" => "matrix_subtype_text",
            //"3" => "matrix_subtype_integer",
            //"4" => "matrix_subtype_double",
            //"5" => "matrix_subtype_date",
            //"6" => "matrix_subtype_time"
        );
        foreach ($subtypes as $idx => $st) {
            $subtype->addOption(new ilRadioOption($this->lng->txt($st), $idx));
        }
        $a_form->addItem($subtype);

        
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("matrix_appearance"));
        $a_form->addItem($header);
        
        // column separators
        $column_separators = new ilCheckboxInputGUI($this->lng->txt("matrix_column_separators"), "column_separators");
        $column_separators->setValue(1);
        $column_separators->setInfo($this->lng->txt("matrix_column_separators_description"));
        $column_separators->setRequired(false);
        $a_form->addItem($column_separators);

        // row separators
        $row_separators = new ilCheckboxInputGUI($this->lng->txt("matrix_row_separators"), "row_separators");
        $row_separators->setValue(1);
        $row_separators->setInfo($this->lng->txt("matrix_row_separators_description"));
        $row_separators->setRequired(false);
        $a_form->addItem($row_separators);

        // neutral column separators
        $neutral_column_separator = new ilCheckboxInputGUI($this->lng->txt("matrix_neutral_column_separator"), "neutral_column_separator");
        $neutral_column_separator->setValue(1);
        $neutral_column_separator->setInfo($this->lng->txt("matrix_neutral_column_separator_description"));
        $neutral_column_separator->setRequired(false);
        $a_form->addItem($neutral_column_separator);

        
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("matrix_columns"));
        $a_form->addItem($header);
        
        // Answers
        $columns = new ilCategoryWizardInputGUI("", "columns");
        $columns->setRequired(false);
        $columns->setAllowMove(true);
        $columns->setShowWizard(true);
        $columns->setShowNeutralCategory(true);
        $columns->setDisabledScale(false);
        $columns->setNeutralCategoryTitle($this->lng->txt('matrix_neutral_answer'));
        $columns->setCategoryText($this->lng->txt('matrix_standard_answers'));
        $columns->setShowSavePhrase(true);
        $a_form->addItem($columns);
        
        
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("matrix_column_settings"));
        $a_form->addItem($header);
        
        // bipolar adjectives
        $bipolar = new ilCustomInputGUI($this->lng->txt("matrix_bipolar_adjectives"));
        $bipolar->setInfo($this->lng->txt("matrix_bipolar_adjectives_description"));
        
        // left pole
        $bipolar1 = new ilTextInputGUI($this->lng->txt("matrix_left_pole"), "bipolar1");
        $bipolar1->setRequired(false);
        $bipolar->addSubItem($bipolar1);
        
        // right pole
        $bipolar2 = new ilTextInputGUI($this->lng->txt("matrix_right_pole"), "bipolar2");
        $bipolar2->setRequired(false);
        $bipolar->addSubItem($bipolar2);

        $a_form->addItem($bipolar);
        

        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->lng->txt("matrix_rows"));
        $a_form->addItem($header);

        // matrix rows
        $rows = new ilMatrixRowWizardInputGUI("", "rows");
        $rows->setRequired(false);
        $rows->setAllowMove(true);
        $rows->setLabelText($this->lng->txt('label'));
        $rows->setUseOtherAnswer(true);
        $a_form->addItem($rows);
        
        
        // values
        $subtype->setValue($this->object->getSubtype());
        $column_separators->setChecked($this->object->getColumnSeparators());
        $row_separators->setChecked($this->object->getRowSeparators());
        $neutral_column_separator->setChecked($this->object->getNeutralColumnSeparator());
        
        if (!$this->object->getColumnCount()) {
            $this->object->columns->addCategory("");
        }
        $columns->setValues($this->object->getColumns());
        
        $bipolar1->setValue($this->object->getBipolarAdjective(0));
        $bipolar2->setValue($this->object->getBipolarAdjective(1));
        
        if ($this->object->getRowCount() === 0) {
            $this->object->getRows()->addCategory("");
        }
        $rows->setValues($this->object->getRows());
    }
    
    protected function importEditFormValues(ilPropertyFormGUI $a_form) : void
    {
        $this->object->setSubtype($a_form->getInput("type"));
        $this->object->setRowSeparators($a_form->getInput("row_separators") ? 1 : 0);
        $this->object->setColumnSeparators($a_form->getInput("column_separators") ? 1 : 0);
        $this->object->setNeutralColumnSeparator($a_form->getInput("neutral_column_separator") ? 1 : 0);
        
        // Set bipolar adjectives
        $this->object->setBipolarAdjective(0, $a_form->getInput("bipolar1"));
        $this->object->setBipolarAdjective(1, $a_form->getInput("bipolar2"));
        
        // set columns
        $this->object->flushColumns();

        $columns = $this->request->getColumns();
        foreach ($columns['answer'] as $key => $value) {
            if (strlen($value)) {
                $this->object->getColumns()->addCategory($value, $columns['other'][$key] ?? 0, 0, null, $columns['scale'][$key]);
            }
        }
        if (isset($columns["neutral"]) && is_string($columns["neutral"])) {
            $this->object->getColumns()->addCategory(
                $columns['neutral'],
                0,
                1,
                null,
                $this->request->getColumnNeutralScale()
            );
        }
        
        // set rows
        $this->object->flushRows();
        $rows = $this->request->getRows();
        foreach ($rows['answer'] as $key => $value) {
            if (strlen($value)) {
                $this->object->getRows()->addCategory($value, $rows['other'][$key] ?? 0, 0, $rows['label'][$key] ?? null);
            }
        }
    }
    
    public function getParsedAnswers(
        array $a_working_data = null,
        bool $a_only_user_anwers = false
    ) : array {
        if (is_array($a_working_data)) {
            $user_answers = $a_working_data;
        }
        
        $options = array();
        for ($i = 0; $i < $this->object->getRowCount(); $i++) {
            $rowobj = $this->object->getRow($i);
            
            $text = null;
            
            $cols = array();
            for ($j = 0; $j < $this->object->getColumnCount(); $j++) {
                $cat = $this->object->getColumn($j);
                $value = ($cat->scale) ? ($cat->scale - 1) : $j;
            
                $checked = "unchecked";
                if (is_array($a_working_data)) {
                    foreach ($user_answers as $user_answer) {
                        if ($user_answer["rowvalue"] == $i &&
                            $user_answer["value"] == $value) {
                            $checked = "checked";
                            if ($user_answer["textanswer"]) {
                                $text = $user_answer["textanswer"];
                            }
                        }
                    }
                }
                
                if (!$a_only_user_anwers || $checked === "checked") {
                    $cols[$value] = array(
                        "title" => trim($cat->title)
                        ,"neutral" => (bool) $cat->neutral
                        ,"checked" => $checked
                    );
                }
            }
            
            if ($a_only_user_anwers || count($cols) || $text) {
                $row_idx = $i;
                $options[$row_idx] = array(
                    "title" => trim($rowobj->title)
                    ,"other" => (bool) $rowobj->other
                    ,"textanswer" => $text
                    ,"cols" => $cols
                );
            }
        }
        
        return $options;
    }
    
    public function getPrintView(
        int $question_title = 1,
        bool $show_questiontext = true,
        ?int $survey_id = null,
        ?array $working_data = null
    ) : string {
        $options = $this->getParsedAnswers($working_data);
                        
        $layout = $this->object->getLayout();
        $neutralstyle = "3px solid #808080";
        $bordercolor = "#808080";
        $template = new ilTemplate("tpl.il_svy_qpl_matrix_printview.html", true, true, "Modules/SurveyQuestionPool");

        if ($this->show_layout_row) {
            $layout_row = $this->getLayoutRow();
            $template->setCurrentBlock("matrix_row");
            $template->setVariable("ROW", $layout_row);
            $template->parseCurrentBlock();
        }
        
        $tplheaders = new ilTemplate("tpl.il_svy_out_matrix_columnheaders.html", true, true, "Modules/SurveyQuestionPool");
        if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
            $tplheaders->setCurrentBlock("bipolar_start");
            $style = array();
            $style[] = sprintf("width: %.2F%s!important", $layout["percent_bipolar_adjective1"], "%");
            if (count($style) > 0) {
                $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
            }
            $tplheaders->parseCurrentBlock();
        }
        // column headers
        for ($i = 0; $i < $this->object->getColumnCount(); $i++) {
            $cat = $this->object->getColumn($i);
            if ($cat->neutral) {
                $tplheaders->setCurrentBlock("neutral_column_header");
                $tplheaders->setVariable("TEXT", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                $tplheaders->setVariable("CLASS", "rsep");
                $style = array();
                $style[] = sprintf("width: %.2F%s!important", $layout["percent_neutral"], "%");
                if ($this->object->getNeutralColumnSeparator()) {
                    $style[] = "border-left: $neutralstyle!important;";
                }
            } else {
                $style = array();
                if ($this->object->getColumnSeparators()) {
                    if (($i < $this->object->getColumnCount() - 1)) {
                        $style[] = "border-right: 1px solid $bordercolor!important";
                    }
                }
                $style[] = sprintf(
                    "width: %.2F%s!important",
                    $layout["percent_columns"] / $this->object->getColumnCount(),
                    "%"
                );
                $tplheaders->setCurrentBlock("column_header");
                $tplheaders->setVariable("TEXT", ilLegacyFormElementsUtil::prepareFormOutput($cat->title));
                $tplheaders->setVariable("CLASS", "center");
            }
            if (count($style) > 0) {
                $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
            }
            $tplheaders->parseCurrentBlock();
        }

        if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
            $tplheaders->setCurrentBlock("bipolar_end");
            $style = array();
            $style[] = sprintf("width: %.2F%s!important", $layout["percent_bipolar_adjective2"], "%");
            if (count($style) > 0) {
                $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
            }
            $tplheaders->parseCurrentBlock();
        }

        $style = array();
        $style[] = sprintf("width: %.2F%s!important", $layout["percent_row"], "%");
        if (count($style) > 0) {
            $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
        }
        
        $template->setCurrentBlock("matrix_row");
        $template->setVariable("ROW", $tplheaders->get());
        $template->parseCurrentBlock();

        $rowclass = array("tblrow1", "tblrow2");
        
        for ($i = 0; $i < $this->object->getRowCount(); $i++) {
            $rowobj = $this->object->getRow($i);
            $tplrow = new ilTemplate("tpl.il_svy_qpl_matrix_printview_row.html", true, true, "Modules/SurveyQuestionPool");
            for ($j = 0; $j < $this->object->getColumnCount(); $j++) {
                $cat = $this->object->getColumn($j);
                if ($i === 0 && $j === 0) {
                    if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
                        $tplrow->setCurrentBlock("bipolar_start");
                        $tplrow->setVariable(
                            "TEXT_BIPOLAR_START",
                            ilLegacyFormElementsUtil::prepareFormOutput($this->object->getBipolarAdjective(0))
                        );
                        $tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
                        $tplrow->parseCurrentBlock();
                    }
                }
                if ($i === 0 && $j === $this->object->getColumnCount() - 1) {
                    if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
                        $tplrow->setCurrentBlock("bipolar_end");
                        $tplrow->setVariable(
                            "TEXT_BIPOLAR_END",
                            ilLegacyFormElementsUtil::prepareFormOutput($this->object->getBipolarAdjective(1))
                        );
                        $tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
                        $tplrow->parseCurrentBlock();
                    }
                }
                
                $value = ($cat->scale) ? ($cat->scale - 1) : $j;
                $col = $options[$i]["cols"][$value];
                
                switch ($this->object->getSubtype()) {
                    case 0:
                        if ($cat->neutral) {
                            $tplrow->setCurrentBlock("neutral_radiobutton");
                        } else {
                            $tplrow->setCurrentBlock("radiobutton");
                        }
                        $tplrow->setVariable("IMAGE_RADIO", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_" . $col["checked"] . ".png")));
                        $tplrow->setVariable("ALT_RADIO", $this->lng->txt($col["checked"]));
                        $tplrow->setVariable("TITLE_RADIO", $this->lng->txt($col["checked"]));
                        $tplrow->parseCurrentBlock();
                        break;
                    case 1:
                        if ($cat->neutral) {
                            $tplrow->setCurrentBlock("neutral_checkbox");
                        } else {
                            $tplrow->setCurrentBlock("checkbox");
                        }
                        $tplrow->setVariable("IMAGE_CHECKBOX", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_" . $col["checked"] . ".png")));
                        $tplrow->setVariable("ALT_CHECKBOX", $this->lng->txt($col["checked"]));
                        $tplrow->setVariable("TITLE_CHECKBOX", $this->lng->txt($col["checked"]));
                        $tplrow->parseCurrentBlock();
                        break;
                }
                if ($cat->neutral) {
                    $tplrow->setCurrentBlock("neutral_answer");
                    $style = array();
                    if ($this->object->getNeutralColumnSeparator()) {
                        $style[] = "border-left: $neutralstyle!important";
                    }
                } else {
                    $tplrow->setCurrentBlock("answer");
                    $style = array();
                }
                if ($this->object->getColumnSeparators()) {
                    if ($j < $this->object->getColumnCount() - 1) {
                        $style[] = "border-right: 1px solid $bordercolor!important";
                    }
                }
                if ($this->object->getRowSeparators()) {
                    if ($i < $this->object->getRowCount() - 1) {
                        $style[] = "border-bottom: 1px solid $bordercolor!important";
                    }
                }
                if (count($style)) {
                    $tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
                }
                $tplrow->parseCurrentBlock();
            }

            if ($rowobj->other) {
                $text = $options[$i]["textanswer"];
                $tplrow->setCurrentBlock("text_other");
                $tplrow->setVariable("TEXT_OTHER", $text
                    ?: "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
                $tplrow->parseCurrentBlock();
            }

            #force to have always the title
            #22526
            $row_title = ilLegacyFormElementsUtil::prepareFormOutput($rowobj->title);
            if ($question_title === 3) {
                if (trim($rowobj->label)) {
                    $row_title .= ' <span class="questionLabel">(' . ilLegacyFormElementsUtil::prepareFormOutput(
                        $rowobj->label
                    ) . ')</span>';
                }
            }

            $tplrow->setVariable("TEXT_ROW", $row_title);
            $tplrow->setVariable("ROWCLASS", $rowclass[$i % 2]);
            if ($this->object->getRowSeparators()) {
                if ($i < $this->object->getRowCount() - 1) {
                    $tplrow->setVariable("STYLE", " style=\"border-bottom: 1px solid $bordercolor!important\"");
                }
            }
            $template->setCurrentBlock("matrix_row");
            $template->setVariable("ROW", $tplrow->get());
            $template->parseCurrentBlock();
        }
        
        if ($question_title) {
            $template->setVariable("QUESTION_TITLE", $this->getPrintViewQuestionTitle($question_title));
        }
        $template->setCurrentBlock();
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->parseCurrentBlock();
        return $template->get();
    }

        
    //
    // LAYOUT
    //

    /**
     * Creates a layout view of the question
     */
    public function layout() : void
    {
        $ilTabs = $this->tabs;
        
        $ilTabs->activateTab("layout");
        
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_layout.html", "Modules/SurveyQuestionPool");
        $this->show_layout_row = true;
        $question_output = $this->getWorkingForm();
        $this->tpl->setVariable("QUESTION_OUTPUT", $question_output);
        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "saveLayout"));
        $this->tpl->setVariable("SAVE", $this->lng->txt("save"));
    }
    
    /**
     * Saves the layout for the matrix question
     */
    public function saveLayout() : void
    {
        $percent_values = array(
            "percent_row" => $this->request->getPercentRow(),
            "percent_columns" => $this->request->getPercentColumns(),
            "percent_bipolar_adjective1" => $this->request->getPercentBipAdj1(),
            "percent_bipolar_adjective2" => $this->request->getPercentBipAdj2(),
            "percent_neutral" => $this->request->getPercentNeutral()
        );
        $this->object->setLayout($percent_values);
        
        // #9364
        if (array_sum($percent_values) == 100) {
            $this->object->saveLayout(
                $percent_values["percent_row"],
                $percent_values['percent_columns'],
                $percent_values['percent_bipolar_adjective1'],
                $percent_values['percent_bipolar_adjective2'],
                $percent_values["percent_neutral"]
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"));
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("svy_matrix_layout_percentages_sum_invalid"));
        }
        $this->layout();
    }

    /**
     * Creates a row to define the matrix question layout with percentage values
     */
    public function getLayoutRow() : string
    {
        $percent_values = $this->object->getLayout();
        $template = new ilTemplate("tpl.il_svy_out_matrix_layout.html", true, true, "Modules/SurveyQuestionPool");
        if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
            $template->setCurrentBlock("bipolar_start");
            $template->setVariable("VALUE_PERCENT_BIPOLAR_ADJECTIVE1", " value=\"" . $percent_values["percent_bipolar_adjective1"] . "\"");
            $template->setVariable("STYLE", " style=\"width:" . $percent_values["percent_bipolar_adjective1"] . "%\"");
            $template->parseCurrentBlock();
            $template->setCurrentBlock("bipolar_end");
            $template->setVariable("VALUE_PERCENT_BIPOLAR_ADJECTIVE2", " value=\"" . $percent_values["percent_bipolar_adjective2"] . "\"");
            $template->setVariable("STYLE", " style=\"width:" . $percent_values["percent_bipolar_adjective2"] . "%\"");
            $template->parseCurrentBlock();
        }
        $counter = $this->object->getColumnCount();
        if ($this->object->hasNeutralColumn()) {
            $template->setCurrentBlock("neutral_start");
            $template->setVariable("VALUE_PERCENT_NEUTRAL", " value=\"" . $percent_values["percent_neutral"] . "\"");
            $template->setVariable("STYLE_NEUTRAL", " style=\"width:" . $percent_values["percent_neutral"] . "%\"");
            $template->parseCurrentBlock();
            $counter--;
        }
        $template->setVariable("VALUE_PERCENT_ROW", " value=\"" . $percent_values["percent_row"] . "\"");
        $template->setVariable("STYLE_ROW", " style=\"width:" . $percent_values["percent_row"] . "%\"");
        $template->setVariable("COLSPAN_COLUMNS", $counter);
        $template->setVariable("VALUE_PERCENT_COLUMNS", " value=\"" . $percent_values["percent_columns"] . "\"");
        $template->setVariable("STYLE_COLUMNS", " style=\"width:" . $percent_values["percent_columns"] . "%\"");
        return $template->get();
    }
    
    
    //
    // EXECUTION
    //
    
    /**
     * Creates the question output form for the learner
     */
    public function getWorkingForm(
        array $working_data = null,
        int $question_title = 1,
        bool $show_questiontext = true,
        string $error_message = "",
        int $survey_id = null,
        bool $compress_view = false
    ) : string {
        $layout = $this->object->getLayout();
        $neutralstyle = "3px solid #808080";
        $bordercolor = "#808080";
        $template = new ilTemplate("tpl.il_svy_out_matrix.html", true, true, "Modules/SurveyQuestionPool");
        $template->setCurrentBlock("material_matrix");
        $template->setVariable("TEXT_MATERIAL", $this->getMaterialOutput());
        $template->parseCurrentBlock();
        
        if ($this->show_layout_row) {
            $layout_row = $this->getLayoutRow();
            $template->setCurrentBlock("matrix_row");
            $template->setVariable("ROW", $layout_row);
            $template->parseCurrentBlock();
        }
        
        $tplheaders = new ilTemplate("tpl.il_svy_out_matrix_columnheaders.html", true, true, "Modules/SurveyQuestionPool");
        if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
            $tplheaders->setCurrentBlock("bipolar_start");
            $style = array();
            $style[] = sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective1"], "%");
            if (count($style) > 0) {
                $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
            }
            $tplheaders->parseCurrentBlock();
        }
        // column headers
        for ($i = 0; $i < $this->object->getColumnCount(); $i++) {
            $style = array();
            $col = $this->object->getColumn($i);
            if ($col->neutral) {
                $tplheaders->setCurrentBlock("neutral_column_header");
                $tplheaders->setVariable("TEXT", ilLegacyFormElementsUtil::prepareFormOutput($col->title));
                $tplheaders->setVariable("CLASS", "rsep");
                $style[] = sprintf("width: %.2f%s!important", $layout["percent_neutral"], "%");
                if ($this->object->getNeutralColumnSeparator()) {
                    $style[] = "border-left: $neutralstyle!important;";
                }
            } else {
                if ($this->object->getColumnSeparators()) {
                    if (($i < $this->object->getColumnCount() - 1)) {
                        $style[] = "border-right: 1px solid $bordercolor!important";
                    }
                }
                $style[] = sprintf(
                    "width: %.2f%s!important",
                    $layout["percent_columns"] / $this->object->getColumnCount(),
                    "%"
                );
                $tplheaders->setCurrentBlock("column_header");
                $tplheaders->setVariable("TEXT", ilLegacyFormElementsUtil::prepareFormOutput($col->title));
                $tplheaders->setVariable("CLASS", "center");
            }
            if (count($style) > 0) {
                $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
            }
            $tplheaders->parseCurrentBlock();
        }
        if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
            $tplheaders->setCurrentBlock("bipolar_end");
            $style = array();
            $style[] = sprintf("width: %.2f%s!important", $layout["percent_bipolar_adjective2"], "%");
            if (count($style) > 0) {
                $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
            }
            $tplheaders->parseCurrentBlock();
        }

        $style = array();
        $style[] = sprintf("width: %.2f%s!important", $layout["percent_row"], "%");
        if (count($style) > 0) {
            $tplheaders->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
        }
        
        $template->setCurrentBlock("matrix_row");
        $template->setVariable("ROW", $tplheaders->get());
        $template->parseCurrentBlock();

        $rowclass = array("tblrow1", "tblrow2");
        for ($i = 0; $i < $this->object->getRowCount(); $i++) {
            $rowobj = $this->object->getRow($i);
            $tplrow = new ilTemplate("tpl.il_svy_out_matrix_row.html", true, true, "Modules/SurveyQuestionPool");
            for ($j = 0; $j < $this->object->getColumnCount(); $j++) {
                $cat = $this->object->getColumn($j);
                if ($i === 0 && $j === 0) {
                    if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
                        $tplrow->setCurrentBlock("bipolar_start");
                        $tplrow->setVariable(
                            "TEXT_BIPOLAR_START",
                            ilLegacyFormElementsUtil::prepareFormOutput($this->object->getBipolarAdjective(0))
                        );
                        $tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
                        $tplrow->parseCurrentBlock();
                    }
                }
                if ($i === 0 && $j === $this->object->getColumnCount() - 1) {
                    if ($this->object->getBipolarAdjective(0) !== '' && $this->object->getBipolarAdjective(1) !== '') {
                        $tplrow->setCurrentBlock("bipolar_end");
                        $tplrow->setVariable(
                            "TEXT_BIPOLAR_END",
                            ilLegacyFormElementsUtil::prepareFormOutput($this->object->getBipolarAdjective(1))
                        );
                        $tplrow->setVariable("ROWSPAN", $this->object->getRowCount());
                        $tplrow->parseCurrentBlock();
                    }
                }
                switch ($this->object->getSubtype()) {
                    case 0:
                        if ($cat->neutral) {
                            $tplrow->setCurrentBlock("neutral_radiobutton");
                        } else {
                            $tplrow->setCurrentBlock("radiobutton");
                        }
                        $tplrow->setVariable("QUESTION_ID", $this->object->getId());
                        $tplrow->setVariable("ROW", $i);
                        $tplrow->setVariable("VALUE", ($cat->scale) ? ($cat->scale - 1) : $j);
                        if (is_array($working_data)) {
                            foreach ($working_data as $data) {
                                if (($data["value"] == $cat->scale - 1) && ($data["rowvalue"] == $i)) {
                                    $tplrow->setVariable("CHECKED_RADIOBUTTON", " checked=\"checked\"");
                                }
                            }
                        }
                        $tplrow->parseCurrentBlock();
                        break;
                    case 1:
                        if ($cat->neutral) {
                            $tplrow->setCurrentBlock("neutral_checkbox");
                        } else {
                            $tplrow->setCurrentBlock("checkbox");
                        }
                        $tplrow->setVariable("QUESTION_ID", $this->object->getId());
                        $tplrow->setVariable("ROW", $i);
                        $tplrow->setVariable("VALUE", ($cat->scale) ? ($cat->scale - 1) : $j);
                        if (is_array($working_data)) {
                            foreach ($working_data as $data) {
                                if (($data["value"] == $cat->scale - 1) && ($data["rowvalue"] == $i)) {
                                    $tplrow->setVariable("CHECKED_CHECKBOX", " checked=\"checked\"");
                                }
                            }
                        }
                        $tplrow->parseCurrentBlock();
                        break;
                }
                if ($cat->neutral) {
                    $tplrow->setCurrentBlock("neutral_answer");
                    $style = array();
                    if ($this->object->getNeutralColumnSeparator()) {
                        $style[] = "border-left: $neutralstyle!important";
                    }
                } else {
                    $tplrow->setCurrentBlock("answer");
                    $style = array();
                }
                if ($this->object->getColumnSeparators()) {
                    if ($j < $this->object->getColumnCount() - 1) {
                        $style[] = "border-right: 1px solid $bordercolor!important";
                    }
                }
                if ($this->object->getRowSeparators()) {
                    if ($i < $this->object->getRowCount() - 1) {
                        $style[] = "border-bottom: 1px solid $bordercolor!important";
                    }
                }
                if (count($style)) {
                    $tplrow->setVariable("STYLE", " style=\"" . implode(";", $style) . "\"");
                }
                $tplrow->parseCurrentBlock();
            }

            if ($rowobj->other) {
                $tplrow->setCurrentBlock("row_other");
                $tplrow->setVariable("QUESTION_ID", $this->object->getId());
                $tplrow->setVariable("ROW", $i);
                if (is_array($working_data)) {
                    foreach ($working_data as $data) {
                        if ($data["rowvalue"] == $i) {
                            $tplrow->setVariable(
                                "VALUE_OTHER",
                                ilLegacyFormElementsUtil::prepareFormOutput($data['textanswer'])
                            );
                        }
                    }
                }
                $tplrow->parseCurrentBlock();
            }
            $tplrow->setVariable("TEXT_ROW", ilLegacyFormElementsUtil::prepareFormOutput($rowobj->title));
            $tplrow->setVariable("ROWCLASS", $rowclass[$i % 2]);
            if ($this->object->getRowSeparators()) {
                if ($i < $this->object->getRowCount() - 1) {
                    $tplrow->setVariable("STYLE", " style=\"border-bottom: 1px solid $bordercolor!important\"");
                }
            }
            $template->setCurrentBlock("matrix_row");
            $template->setVariable("ROW", $tplrow->get());
            $template->parseCurrentBlock();
        }
        
        if ($question_title) {
            $template->setVariable(
                "QUESTION_TITLE",
                ilLegacyFormElementsUtil::prepareFormOutput($this->object->getTitle())
            );
        }
        $template->setCurrentBlock("question_data_matrix");
        if (strcmp($error_message, "") !== 0) {
            $template->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
        }
        if ($show_questiontext) {
            $this->outQuestionText($template);
        }
        $template->parseCurrentBlock();
        return $template->get();
    }
}
