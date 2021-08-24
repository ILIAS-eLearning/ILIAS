<?php

/**
 * Class ilForumNotificationEvents
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup ModulesForum
 */
class ilForumNotificationEvents
{
    public const UPDATED = 1;
    public const CENSORED = 2;
    public const UNCENSORED = 4;
    public const POST_DELETED = 8;
    public const THREAD_DELETED = 16;
    
    protected bool $is_notify_updated_enabled = false;
    protected bool $is_notify_censored_enabled = false;
    protected bool $is_notify_post_deleted_enabled = false;
    protected bool $is_notify_thread_deleted_enabled = false;
    
    protected $interested_events = 0;
    
    public function __construct()
    {
    }
}
