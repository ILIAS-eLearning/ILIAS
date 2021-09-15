<?php declare(strict_types=1);

/**
 * Class ilForumNotificationEventsFormGUI
 */
class ilForumNotificationEventsFormGUI extends ilPropertyFormGUI
{
    protected $parent_object;
    protected int $ref_id;
    protected int $thread_id = 0;

    public function __construct($parent_object, $ref_id, $thread_id = 0)
    {
        $this->parent_object = $parent_object;
        $this->ref_id = $ref_id;
        $this->thread_id = $thread_id;

        parent::__construct();

        $this->initForm();
    }

    private function initForm() : void
    {
        $this->setId(uniqid('frm_ntf_set_' . $this->ref_id));

        if ($this->thread_id > 0) {
            $this->ctrl->setParameter($this->parent_object, 'thr_pk', $this->thread_id);
        }

        $this->setFormAction($this->ctrl->getFormAction($this->parent_object, 'saveUserNotificationSettings'));

        $notify_modified = new ilCheckboxInputGUI($this->lng->txt('notify_modified'), 'notify_modified');
        $notify_modified->setValue(ilForumNotificationEvents::UPDATED);
        $this->addItem($notify_modified);

        $notify_censored = new ilCheckboxInputGUI($this->lng->txt('notify_censored'), 'notify_censored');
        $notify_censored->setValue(ilForumNotificationEvents::CENSORED);
        $this->addItem($notify_censored);

        $notify_uncensored = new ilCheckboxInputGUI($this->lng->txt('notify_uncensored'), 'notify_uncensored');
        $notify_uncensored->setValue(ilForumNotificationEvents::UNCENSORED);
        $this->addItem($notify_uncensored);

        $notify_post_deleted = new ilCheckboxInputGUI(
            $this->lng->txt('notify_post_deleted'),
            'notify_post_deleted'
        );
        $notify_post_deleted->setValue(ilForumNotificationEvents::POST_DELETED);
        $this->addItem($notify_post_deleted);

        $notify_thread_deleted = new ilCheckboxInputGUI(
            $this->lng->txt('notify_thread_deleted'),
            'notify_thread_deleted'
        );
        $notify_thread_deleted->setValue(ilForumNotificationEvents::THREAD_DELETED);
        $this->addItem($notify_thread_deleted);

        $hidden_value = new ilHiddenInputGUI('hidden_value');
        $this->addItem($hidden_value);
    }
}
