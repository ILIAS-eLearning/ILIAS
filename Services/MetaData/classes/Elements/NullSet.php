<?php

namespace ILIAS\MetaData\Elements;

use ILIAS\MetaData\Elements\RessourceID\NullRessourceID;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;

class NullSet implements SetInterface
{
    public function getRessourceID(): RessourceIDInterface
    {
        return new NullRessourceID();
    }

    public function getRoot(): ElementInterface
    {
        return new NullElement();
    }
}
