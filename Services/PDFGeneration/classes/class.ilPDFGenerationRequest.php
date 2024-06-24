<?php

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

declare(strict_types=1);

/**
 * @deprecated
 */
class ilPDFGenerationRequest
{
    private ILIAS\Refinery\Factory $refinery;
    private ILIAS\HTTP\GlobalHttpState $http;

    public function __construct(\ILIAS\Refinery\Factory $refinery, \ILIAS\HTTP\GlobalHttpState $http)
    {
        $this->refinery = $refinery;
        $this->http = $http;
    }

    public function securedString(string $parameter, bool $cast_null_to_string = true): string
    {
        $as_sanizited_string = $this->refinery->custom()->transformation(static function (string $value): string {
            return ilUtil::stripSlashes($value);
        });

        $null_to_empty_string = $this->refinery->custom()->transformation(static function ($value): string {
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

    protected function isPathOrUrlValid(string $url): bool
    {
        $is_valid = false;
        $is_url = filter_var($url, FILTER_VALIDATE_URL);

        if ($is_url) {
            try {
                $c = new ilCurlConnection($url);
                $c->init();
                $c->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
                $c->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
                $c->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
                $c->setOpt(CURLOPT_RETURNTRANSFER, 1);
                $c->setOpt(CURLOPT_FOLLOWLOCATION, 0);
                $c->setOpt(CURLOPT_MAXREDIRS, 0);
                $c->setOpt(CURLOPT_URL, $url);
                $c->setOpt(CURLOPT_HEADER, true);
                $c->setOpt(CURLOPT_NOBODY, true);

                $result = $c->exec();
                $is_valid = $c->getInfo(CURLINFO_HTTP_CODE) === 200;

            } catch (ilCurlConnectionException $e) {
                return false;
            }
        } elseif (is_file($url)) {
            $is_valid = true;
        }

        return $is_valid;
    }

    public function isNotValidSize(array $sizes): bool
    {
        foreach ($sizes as $size) {
            if (is_int($size)) {
                continue;
            }
            if (!preg_match('/(\d)+?(\W)*(cm|mm)$/', $size)) {
                if ($size !== 0 && $size !== "0" && $size !== null && $size !== "") {
                    return true;
                }
            }
        }

        return false;
    }

    public function isNotValidText(array $texts): bool
    {
        foreach ($texts as $text) {
            if (!preg_match('/[a-zA-Z\d ]+$/', $text)) {
                if ($text !== '' && $text !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $parameters
     * @return bool
     */
    public function validatePathOrUrl(array $parameters): bool
    {
        $valid = false;
        foreach ($parameters as $parameter) {
            $value = $this->securedString($parameter);
            if ($value === '') {
                $valid = true;
            } else {
                $valid = $this->isPathOrUrlValid($value);
            }
            if ($valid === false) {
                return false;
            }
        }
        return true;
    }

    public function int(string $parameter, bool $cast_null_to_int = true): int
    {
        $null_to_zero = $this->refinery->custom()->transformation(static function ($value): int {
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

    public function bool(string $parameter, bool $cast_null_to_false = true): bool
    {
        $null_to_false = $this->refinery->custom()->transformation(static function ($value): bool {
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

    public function float(string $parameter, bool $cast_null_to_zero = true): float
    {
        $null_to_zero = $this->refinery->custom()->transformation(static function ($value): float {
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
