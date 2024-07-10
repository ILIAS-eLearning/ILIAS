# Maps

## example for maps in asynch modal

```php

//configure the Maps GUI
function getMap() : \ilMapGUI {
    $map_gui = \ilMapUtil::getMapGUI();
    $map_id = "map_" . uniqid();
    $map_gui->setMapId($map_id)
            ->setLatitude('50.963056294304')
            ->setLongitude('6.9452890361323')
            ->setZoom(10)
            ->setEnableTypeControl(true)
            ->setEnableLargeMapControl(true)
            ->setEnableUpdateListener(false)
            ->setEnableCentralMarker(true)
            ->setWidth("500px")
            ->setHeight("400px");
    return $map_gui;
}

//setup a modal
global $DIC;
$factory = $DIC->ui()->factory();
$renderer = $DIC->ui()->renderer();
$refinery = $DIC->refinery();
$request_wrapper = $DIC->http()->wrapper()->query();

$url = $_SERVER['REQUEST_URI'];
$asyncUrl = $url . '&asynchmaps=maps';
$modal = $factory->modal()->roundtrip('Map in Asynch Modal', $factory->legacy(''))
    ->withAsyncRenderUrl($asyncUrl);

$button = $factory->button()->standard('Map in Asynch Modal', '#')
    ->withOnClick($modal->getShowSignal());

//Javascript and CSS have to be loaded into the main template
$map = getMap();
$map->initJSandCSS();

//return the asynch modal
if ($request_wrapper->has('asynchmaps')) {
    $content = $factory->legacy($map->getHtml(true)); //note the "true" parameter to pass the js along
    $modal = $factory->modal()->roundtrip('Map in Asynch Modal', $content);
    print $renderer->renderAsync($modal);
    exit();
}

//otherwise render the button
print $renderer->render([$button, $modal]);
```
