<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\LoadingAnimationOnClick;

class Primary extends Button implements C\Button\Primary
{
    use LoadingAnimationOnClick;
}
