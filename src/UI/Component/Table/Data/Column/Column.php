<?php

declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Data\Column;

use ILIAS\UI\Component\Table\Data\Column\Formater\Formater;
use ILIAS\UI\Component\Table\Data\Settings\Sort\SortField;

/**
 * Interface Column
 *
 * @package ILIAS\UI\Component\Table\Data\Column
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Column
{

    /**
     * @return string
     */
    public function getKey() : string;


    /**
     * @param string $key
     *
     * @return self
     */
    public function withKey(string $key) : self;


    /**
     * @return string
     */
    public function getTitle() : string;


    /**
     * @param string $title
     *
     * @return self
     */
    public function withTitle(string $title) : self;


    /**
     * @return Formater
     */
    public function getFormater() : Formater;


    /**
     * @param Formater $formater
     *
     * @return self
     */
    public function withFormater(Formater $formater) : self;


    /**
     * @return bool
     */
    public function isSortable() : bool;


    /**
     * @param bool $sortable
     *
     * @return self
     */
    public function withSortable(bool $sortable = true) : self;


    /**
     * @return bool
     */
    public function isDefaultSort() : bool;


    /**
     * @param bool $default_sort
     *
     * @return self
     */
    public function withDefaultSort(bool $default_sort = false) : self;


    /**
     * @return int
     */
    public function getDefaultSortDirection() : int;


    /**
     * @param int $default_sort_direction
     *
     * @return self
     */
    public function withDefaultSortDirection(int $default_sort_direction = SortField::SORT_DIRECTION_UP) : self;


    /**
     * @return bool
     */
    public function isSelectable() : bool;


    /**
     * @param bool $selectable
     *
     * @return self
     */
    public function withSelectable(bool $selectable = true) : self;


    /**
     * @return bool
     */
    public function isDefaultSelected() : bool;


    /**
     * @param bool $default_selected
     *
     * @return self
     */
    public function withDefaultSelected(bool $default_selected = true) : self;


    /**
     * @return bool
     */
    public function isExportable() : bool;


    /**
     * @param bool $exportable
     *
     * @return self
     */
    public function withExportable(bool $exportable = true) : self;
}
