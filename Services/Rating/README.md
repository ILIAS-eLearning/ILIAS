# Rating Service

## General

## Activation

* There is currently no global activation of the rating service.
* Rating can be activated in learning modules, wikis and files "per object".
* Courses and groups have a setting for the default rating activation for new objects (learning modules, wikis, files).
* Some components use the rating widget for subitems: forum postings, exercise peer feedbacks, data collection columns.

## API

Activation settings need to be implemented by the consumer.

The service provides a class `ilRatingGUI` to output the rating widget.
```
    $rating = new ilRatingGUI();
    $rating->setObject(
        $object_id,
        $object_type,
        $sub_object_id,
        $sub_object_type
    );
    $rating->setUserId($this->user->getId());
    $html = $rating->getHTML();
```
The consuming GUI class needs to forward to this class, too.
```
...
    * @ilCtrl_Calls ilMyGUI: ilRatingGUI
...
    public function executeCommand() : void
...
            case strtolower(ilRatingGUI::class):
                $rating_gui = new ilRatingGUI();
                $rating_gui->setObject(
                    $object_id,
                    $object_type,
                    $sub_object_id,
                    $sub_object_type
                );
                $this->ctrl->forwardCommand($rating_gui);
...
```