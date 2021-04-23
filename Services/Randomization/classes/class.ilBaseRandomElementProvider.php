<?php declare(strict_types=1);

/**
 * Class ilBaseRandomElementProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBaseRandomElementProvider implements ilRandomArrayElementProvider
{
    /** @var int */
    protected $seed;

    /**
     * ilArrayElementShuffler constructor.
     */
    public function __construct()
    {
        $this->setSeed($this->getInitialSeed());
    }

    /**
     * @return int
     */
    abstract protected function getInitialSeed() : int;

    /**
     * @return int
     */
    public function getSeed() : int
    {
        return $this->seed;
    }

    /**
     * @inheritDoc
     */
    public function setSeed(int $seed) : void
    {
        $this->seed = $seed;
    }

    /**
     * @inheritDoc
     */
    public function buildSeedFromString(string $string) : int
    {
        return (int) hexdec(substr(md5($string), 0, 10));
    }
}
