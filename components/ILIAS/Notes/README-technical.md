# Embedding Comments

To embed comments on a screen, e.g. at the bottom, you need to retrieve an instance of `ilCommentGUI` via the `$DIC`. You need to add an @ilCtrl_Calls declaration for `ilCommentGUI` and forward commands to the instance.

Please use the following code to integrate comments in various ways.

```php
/**
 * @ilCtrl_Calls ilYourClassGUI: ilCommentGUI
 */
class ilYourClassGUI
{
    // ...

    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case strtolower(ilComment::class):
                $ilCtrl->forwardCommand($this->getCommentGUI());
                break;
        }
    }
    
    protected function getCommentGUI() : ilCommentGUI
    {
        global $DIC;
        
        $rep_obj_id = ... // the object id of your repository object
        $sub_obj_id = ... // any id of a consumer specific sub-object, e.g. a page ID
        $type = ... // type that specifies the consumer specific sub-object
        return $DIC->notes()->gui()->getCommentsGUI(
            $rep_obj_id,
            $sub_obj_id,
            $type
        );
    }
    
    protected function show() : void
    {
        $this->main_tpl->setContent(
            $anything_html . $this->getCommentGUI()->getListHTML();
        );
    }
}



```

To add the comments action to an action dropdown a shy button can be retrieved from the comments gui instance:

```
$button = $comments_gui->getTriggerShyButton();
```

A rendered glyph with current number can be retrieved as string by calling getGlyph().

```
$html = $comments_gui->getGlyph();
```

A rendered linked number can be retrieved by calling getNumber().

```
$html = $comments_gui->getNumber();
```
