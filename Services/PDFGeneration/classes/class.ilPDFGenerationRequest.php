<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilPDFGenerationRequest
{
    private ILIAS\Refinery\Factory $refinery;
    private ILIAS\HTTP\GlobalHttpState $http;

    public function __construct(\ILIAS\Refinery\Factory $refinery, \ILIAS\HTTP\GlobalHttpState $http)
    {
        $this->refinery = $refinery;
        $this->http = $http;
    }

    public function securedString(string $parameter, bool $cast_null_to_string = true) : string
    {
        $as_sanizited_string = $this->refinery->custom()->transformation(static function (string $value) : string {
            return ilUtil::stripSlashes($value);
        });

        $null_to_empty_string = $this->refinery->custom()->transformation(static function ($value) : string {
            if ($value === null) {
                return '';
            }

            throw new ilException('Expected null in transformation');
        });

        $sanizite_as_string = $this->refinery->in()->series([
            $cast_null_to_string ?
                $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $null_to_empty_string]) :
                $this->refinery->kindlyTo()->string(),
            $as_sanizited_string
        ]);

        $string = '';
        if ($this->http->wrapper()->post()->has($parameter)) {
            $string = $this->http->wrapper()->post()->retrieve(
                $parameter,
                $sanizite_as_string
            );
        } elseif ($this->http->wrapper()->query()->has($parameter)) {
            $string = $this->http->wrapper()->query()->retrieve(
                $parameter,
                $sanizite_as_string
            );
        }

        return $string;
    }
    
    public function int(string $parameter, bool $cast_null_to_int = true) : int
    {
        $null_to_zero = $this->refinery->custom()->transformation(static function ($value) : int {
            if ($value === null) {
                return 0;
            }

            throw new ilException('Expected null in transformation');
        });

        $as_int = $cast_null_to_int ?
            $this->refinery->byTrying([$this->refinery->kindlyTo()->int(), $null_to_zero]) :
            $this->refinery->kindlyTo()->int();

        $int = 0;
        if ($this->http->wrapper()->post()->has($parameter)) {
            $int = $this->http->wrapper()->post()->retrieve(
                $parameter,
                $as_int
            );
        } elseif ($this->http->wrapper()->query()->has($parameter)) {
            $int = $this->http->wrapper()->query()->retrieve(
                $parameter,
                $as_int
            );
        }

        return $int;
    }

    public function bool(string $parameter, bool $cast_null_to_false = true) : bool
    {
        $null_to_false = $this->refinery->custom()->transformation(static function ($value) : bool {
            if ($value === null) {
                return false;
            }

            throw new ilException('Expected null in transformation');
        });

        $as_bool = $cast_null_to_false ?
            $this->refinery->byTrying([$this->refinery->kindlyTo()->bool(), $null_to_false]) :
            $this->refinery->kindlyTo()->bool();

        $bool = false;
        if ($this->http->wrapper()->post()->has($parameter)) {
            $bool = $this->http->wrapper()->post()->retrieve(
                $parameter,
                $as_bool
            );
        } elseif ($this->http->wrapper()->query()->has($parameter)) {
            $bool = $this->http->wrapper()->query()->retrieve(
                $parameter,
                $as_bool
            );
        }

        return $bool;
    }

    public function float(string $parameter, bool $cast_null_to_zero = true) : float
    {
        $null_to_zero = $this->refinery->custom()->transformation(static function ($value) : float {
            if ($value === null) {
                return 0.0;
            }

            throw new ilException('Expected null in transformation');
        });

        $as_float = $cast_null_to_zero ?
            $this->refinery->byTrying([$this->refinery->kindlyTo()->float(), $null_to_zero]) :
            $this->refinery->kindlyTo()->float();

        $float = 0.0;
        if ($this->http->wrapper()->post()->has($parameter)) {
            $float = $this->http->wrapper()->post()->retrieve(
                $parameter,
                $as_float
            );
        } elseif ($this->http->wrapper()->query()->has($parameter)) {
            $float = $this->http->wrapper()->query()->retrieve(
                $parameter,
                $as_float
            );
        }

        return $float;
    }
}
