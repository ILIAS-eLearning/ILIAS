<?php

namespace ILIAS\MetaData\Elements\Markers;

class NullMarkerFactory implements MarkerFactoryInterface
{
    public function marker(Action $action, string $data_value = ''): MarkerInterface
    {
        return new NullMarker();
    }
}
