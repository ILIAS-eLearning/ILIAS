<?php declare(strict_types=1);

/**
 * Class ilBaseRandomElementProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseRandomElementProvider implements ilRandomArrayElementProvider
{
    protected int $seed;

    public function __construct()
    {
        $this->setSeed($this->getInitialSeed());
    }

    abstract protected function getInitialSeed() : int;

    public function getSeed() : int
    {
        return $this->seed;
    }

    public function setSeed(int $seed) : void
    {
        $this->seed = $seed;
    }

    public function buildSeedFromString(string $string) : int
    {
        return (int) hexdec(substr(md5($string), 0, 10));
    }
}
