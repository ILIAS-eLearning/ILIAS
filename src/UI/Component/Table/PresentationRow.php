<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Triggerable;

/**
 * This describes a Row used in Presentation Table.
 * A row consists (potentially) of title, subtitle, important fields (in the
 * collapased row) and further fields to be shown in the expanded row.
 */
interface PresentationRow extends \ILIAS\UI\Component\Component, Triggerable
{

    /**
     * Get a row like this with the given headline.
     *
     * @param string 	$headline
     * @return \ILIAS\UI\Component\Table\PresentationRow
     */
    public function withHeadline($headline);

    /**
     * Get a row like this with the given subheadline.
     *
     * @param string 	$subheadline
     * @return \ILIAS\UI\Component\Table\PresentationRow
     */
    public function withSubheadline($subheadline);

    /**
     * Get a row like this with the record-fields and labels
     * to be shown in the collapsed row.
     *
     * @param array<string,string> 	$fields
     * @return \ILIAS\UI\Component\Table\PresentationRow
     */
    public function withImportantFields(array $fields);

    /**
     * Get a row like this with a descriptive listing as content.
     *
     * @param \ILIAS\UI\Component\Listing\Descriptive $content
     */
    public function withContent(\ILIAS\UI\Component\Listing\Descriptive $content);

    /**
     * Get a row like this with a headline for the field-list in the expanded row.
     *
     * @param string 	$headline
     * @return \ILIAS\UI\Component\Table\PresentationRow
     */
    public function withFurtherFieldsHeadline($headline);

    /**
     * Get a row like this with the record-fields and labels to be shown
     * in the list of the expanded row.
     *
     * @param array<string,string> 	$fields
     * @return \ILIAS\UI\Component\Table\PresentationRow
     */
    public function withFurtherFields(array $fields);

    /**
     * Get a row like this with a button or a dropdown for actions in the expanded row.
     *
     * @param ILIAS\UI\Component\Button\Button|ILIAS\UI\Component\Dropdown\Dropdown 	$action
     * @return \ILIAS\UI\Component\Table\PresentationRow
     */
    public function withAction($action);

    /**
     * Get the signal to expand the row.
     *
     * @return Signal
     */
    public function getShowSignal();

    /**
     * Get the signal to collapse the row.
     *
     * @return Signal
     */
    public function getCloseSignal();

    /**
     * Get the signal to toggle (expand/collapse) the row.
     *
     * @return Signal
     */
    public function getToggleSignal();
}
