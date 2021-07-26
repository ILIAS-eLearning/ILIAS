<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler\PathGenerator;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class MaxNestingPathGenerator
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class MaxNestingPathGenerator implements PathGenerator
{
    private const MAX_NESTING_256 = 256;
    private const MAX_NESTING_4096 = 4096;
    private const MAX_NESTING_65536 = 65536;

    protected $max_nesting = self::MAX_NESTING_4096;
    /**
     * @var int
     */
    protected $splitter = 3;
    /**
     * @var int
     */
    protected $limited_layers = 3;

    /**
     * MaxNestingPathGenerator constructor.
     */
    public function __construct()
    {
        switch ($this->max_nesting) {
            case self::MAX_NESTING_4096:
                $this->splitter = 3;
                break;
            case self::MAX_NESTING_65536:
                $this->splitter = 4;
                break;
            case self::MAX_NESTING_256:
            default:
                $this->splitter = 2;
                break;
        }
    }

    public function getPathFor(ResourceIdentification $i) : string
    {
        $splitted = str_split(str_replace("-", "", $i->serialize()), $this->splitter);

        $first_part = array_slice($splitted, 0, $this->limited_layers + 1);
        $second_part = array_slice($splitted, $this->limited_layers + 1);

        return implode("/", $first_part) . implode("", $second_part);
    }

    public function getIdentificationFor(string $path) : ResourceIdentification
    {
        $str = str_replace("/", "", $path);

        $p1 = substr($str, 0, 8);
        $p2 = substr($str, 8, 4);
        $p3 = substr($str, 12, 4);
        $p4 = substr($str, 16, 4);
        $p5 = substr($str, 20, 12);

        return new ResourceIdentification("$p1-$p2-$p3-$p4-$p5");
    }

}
