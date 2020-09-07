<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectCustomIconPresenter
 */
class ilObjectCustomIconPresenterImpl implements \ilObjectCustomIconPresenter
{
    /** @var \ilObjectCustomIcon */
    private $icon = null;

    /**
     * ilObjectCustomIconPresenter constructor.
     * @param ilObjectCustomIcon $icon
     */
    public function __construct(\ilObjectCustomIcon $icon)
    {
        $this->icon = $icon;
    }

    /**
     * @inheritdoc
     */
    public function exists() : bool
    {
        return $this->icon->exists();
    }

    /**
     * @inheritdoc
     */
    public function getFullPath() : string
    {
        return $this->icon->getFullPath();
    }
}
