<?php declare(strict_types=1);

interface ilForumNotificationEvents
{
    public const UPDATED = 1;
    public const CENSORED = 2;
    public const UNCENSORED = 4;
    public const POST_DELETED = 8;
    public const THREAD_DELETED = 16;
}
