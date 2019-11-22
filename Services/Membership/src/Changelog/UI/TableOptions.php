<?php

/**
 * Class ChangelogTableOptions
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class TableOptions
{

    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var bool
     */
    protected $enable_header = true;
    /**
     * @var bool
     */
    protected $enable_num_info = true;
    /**
     * @var bool
     */
    protected $show_rows_selector = true;
    /**
     * @var string
     */
    protected $default_order_direction = 'asc';


    /**
     * ChangelogTableOptions constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }


    /**
     * @param string $description
     *
     * @return $this
     */
    public function withDescription(string $description) : self
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }


    /**
     * @param string $title
     *
     * @return $this
     */
    public function withTitle(string $title) : self
    {
        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }


    /**
     * @param bool $enable_header
     *
     * @return $this
     */
    public function withEnableHeader(bool $enable_header) : self
    {
        $clone = clone $this;
        $clone->enable_header = $enable_header;

        return $clone;
    }

    /**
     * @param bool $enable_num_info
     *
     * @return $this
     */
    public function withEnableNumInfo(bool $enable_num_info) : self
    {
        $clone = clone $this;
        $clone->enable_num_info = $enable_num_info;

        return $clone;
    }


    /**
     * @param bool $show_rows_selector
     *
     * @return $this
     */
    public function withShowRowsSelector(bool $show_rows_selector) : self
    {
        $clone = clone $this;
        $clone->show_rows_selector = $show_rows_selector;

        return $clone;
    }


    /**
     * @return $this
     */
    public function withDefaultOrderAscending() : self
    {
        $clone = clone $this;
        $clone->default_order_direction = 'asc';

        return $clone;
    }


    /**
     * @return $this
     */
    public function withDefaultOrderDescending() : self
    {
        $clone = clone $this;
        $clone->default_order_direction = 'desc';

        return $clone;
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }


    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }


    /**
     * @return bool
     */
    public function isHeaderEnabled() : bool
    {
        return $this->enable_header;
    }


    /**
     * @return bool
     */
    public function isNumInfoEnabled() : bool
    {
        return $this->enable_num_info;
    }


    /**
     * @return bool
     */
    public function isRowsSelectorShown() : bool
    {
        return $this->show_rows_selector;
    }


    /**
     * @return string
     */
    public function getDefaultOrderDirection() : string
    {
        return $this->default_order_direction;
    }
}