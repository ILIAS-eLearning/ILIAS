<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery\Random\Transformation;

use ILIAS\Refinery\Transformation;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\Refinery\Random\Seed\Seed;
use ILIAS\Refinery\Random\Effect\ShuffleEffect;
use ILIAS\Refinery\Effect\Effect;
use ILIAS\Refinery\Effect\Transformation\LiftTransformation;

class ShuffleTransformation extends LiftTransformation
{
    private Seed $seed;

    public function __construct(Seed $seed)
    {
        $this->seed = $seed;
    }

    /**
     * @param array $value
     */
    public function createEffect($value) : Effect
    {
        return new ShuffleEffect($value, $this->seed);
    }

    /**
     * @return Result<Effect<array>>
     */
    protected function validate($from) : Result
    {
        return is_array($from) ? new Ok($from) : new Error('I need an array');
    }
}
