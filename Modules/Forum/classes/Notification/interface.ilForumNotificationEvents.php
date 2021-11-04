<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

interface ilForumNotificationEvents
{
    public const DEACTIVATED = 0;
    public const UPDATED = 1;
    public const CENSORED = 2;
    public const UNCENSORED = 4;
    public const POST_DELETED = 8;
    public const THREAD_DELETED = 16;
}
