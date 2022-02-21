<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilObjectCustomIconPresenter
{
    public function exists() : bool;

    public function getFullPath() : string;
}
