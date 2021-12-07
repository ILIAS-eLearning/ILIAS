<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectCustomIconPresenterImpl implements ilObjectCustomIconPresenter
{
    private ilObjectCustomIcon $icon;

    public function __construct(ilObjectCustomIcon $icon)
    {
        $this->icon = $icon;
    }

    public function exists() : bool
    {
        return $this->icon->exists();
    }

    public function getFullPath() : string
    {
        return $this->icon->getFullPath();
    }
}
