<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilObjectCustomIconUploadPostProcessor
{
    public function process(string $fileName) : void;
}
