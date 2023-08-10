# System Notifications

The guideline for this can be found in Feature Wiki: [System Notification Guideline](https://docu.ilias.de/goto_docu_wiki_wpage_783_1357.html).

## ilMailNotification vs. 

All notifications have to be sent (internally) through `ilMailNotification`, which provides a technical abstraction and low-level code to handle context-specific settings, e.g. personal workspace permission handling.
`ilSystemNotification` handles the content structure and should be used whenever possible - the notification will be sent using `ilMailNotification`. If the guideline rules it supports are too strict for your case you can use `ilMailNotification` directly (e.g. `addBody()`), this might be the case for dynamic content as `ilSystemNotification` only supports lang-ids and has no concept of placeholders.

## Basic Usage
The guideline proposes a general structure for system notifications. This structure is supported by `ilSystemNotification` which should be used for any notification issued automatically (via cron or triggered by an event, e.g. object change notifications).
 
The basic blocks of the structure are:

- Subject
- Introduction
- Object-Information
- Additional Information
- Changed by
- Goto Link
- Task
- Reason

### Example 1 (Repository, Module "Exercise")

```php
 1 include_once "./Services/Notification/classes/class.ilSystemNotification.php";
 2 $ntf = new ilSystemNotification();
 3 $ntf->setObjId($ass->getExerciseId());
 4 $ntf->setLangModules(array("exc"));
 5 $ntf->setSubjectLangId("exc_feedback_notification_subject");
 6 $ntf->setIntroductionLangId("exc_feedback_notification_body");
 7 $ntf->addAdditionalInfo("exc_assignment", $ass->getTitle());
 8 $ntf->setGotoLangId("exc_feedback_notification_link");		
 9 $ntf->setReasonLangId("exc_feedback_notification_reason");		
10 
11 include_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";
12 $ntf->sendMail(ilExerciseMembers::_getMembers($ass->getExerciseId()));
```

#### Example 1 Annotations

1. \-
2. \-
3. The object id to add object title to details and a goto link. It is optional - when missing all object-relevant information is removed from the notification.
4. All relevant language modules are given.
5. Keep in mind to set language ids not parsed strings as they are depending on the recipient language!
6. *For custom text blocks you can use setIntroductionDirect() which will be added to the mail without any parsing but you should use this only as a last resort.*
7. Additional information is presented either as single line "[caption]: [value]" or multi-line (3rd Parameter)
8. If no specific caption for the goto link is given, the fallback "URL" is used
9. \-
10. \-
11. \-
12. sendMail() has 3 Parameters:
   - user_ids
   - additional goto suffix, e.g. for sub-"objects" as pages or blog postings
   - the permission to check for the goto link (default: "read"), **if the check fails the recipient will not get the notification!** *You can set the permission to `null` if necessary, e.g. when sending to external email addresses (which should work).*

`sendMail()` will return the user ids which have received the notification.
 
If no specific ref_id was set via `setRefId()` the system will try to find a ref_id by object id. **It will select the first accessible for the recipient.**

### Example 2  (Personal Workspace, "Blog")

```php
 1 include_once "./Services/Notification/classes/class.ilSystemNotification.php";
 2 $ntf = new ilSystemNotification($a_in_wsp);
 3 $ntf->setLangModules(array("blog"));
 4 $ntf->setRefId($a_blog_node_id);
 5 $ntf->setChangedByUserId($ilUser->getId());
 6 $ntf->setSubjectLangId('blog_change_notification_subject');
 7 $ntf->setIntroductionLangId('blog_change_notification_body_'.$a_action);
 8 $ntf->addAdditionalInfo('blog_posting', $posting->getTitle());
 9 if($a_comment)
10 {
11 	$ntf->addAdditionalInfo('comment', $a_comment, true);
12 }	
13 $ntf->setGotoLangId('blog_change_notification_link');				
14 $ntf->setReasonLangId('blog_change_notification_reason');				
15  
16 $notified = $ntf->sendMail($users, "_".$a_posting_id, 
17 	($admin_only ? "write" : "read"));
```

#### Example 2 Annotations

1. \-
2. Parameter: Personal Workspace mode **aka "Where did my ref_id go?"**
3. \-
4. If you have a specific ref_id in your context it should be set (instead of `setObjId()`).
5. If the notification was triggered by an event you can add the "responsible" user (as opposed to cron-jobs)
6. \-
7. `$a_action`: different events lead to different messages.
8. \-
9. \-
10. \-
11. 3rd Parameter: multi-line
12. \-
13. \-
14. \-
15. \-
16. 2nd Parameter: goto suffix
17. 3rd Paramter: goto permission

## Additional notes

`ilSystemNotification` does not have a threshold for repeating messages, see `ilNotification` for that. Attachments and HTML are not supported (yet?). Depending on the user settings the messages will be sent internally or as email.

If there is the need to handle the mail transport separately use `composeAndGetMessage()`.

See `ilCourseMembershipMailNotification` for an example how to use ilMailNotification directly.