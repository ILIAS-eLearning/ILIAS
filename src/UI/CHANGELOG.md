# 5.2.4

* `ButtonFactory::standard`, `ButtonFactory::primary`, `ButtonFactory::shy`, `ButtonFactory::tag`
  can get a signal as second parameter instead of an action. If an url was set, the
  previously defined action-url will be deleted when a click-signal is added. Button::getAction
  may thus also return a list of Signals instead of an url-string.
* `ILIAS\UI\Implementation\Component\Triggerer::addTriggeredSignal` has been renamed to 
  `ILIAS\UI\Implementation\Component\Triggerer::withTriggeredSignal`.

