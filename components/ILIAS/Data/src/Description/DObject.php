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

namespace ILIAS\Data\Description;

use ILIAS\Data\Text;

class DObject extends Description
{
    use ErrorHandling;

    protected array $fields;

    public function __construct(
        Text\SimpleDocumentMarkdown $description,
        Field ...$fields
    ) {
        parent::__construct($description);
        $this->fields = $fields;
    }

    public function getFields(): \Generator
    {
        foreach ($this->fields as $field) {
            yield $field;
        }
    }

    public function getPrimitiveRepresentation(mixed $data): mixed
    {
        if (!is_object($data)) {
            return fn() => yield "Expected an object.";
        }

        $repr = new \StdClass();
        $errors = [];

        foreach ($this->fields as $field) {
            $name = $field->getName();
            $found = false;
            $value = null;
            foreach ($this->possibleMethodNames($name) as $method) {
                if (method_exists($data, $method)) {
                    $found = true;
                    $value = $data->$method();
                    break;
                }
            }
            if (!$found) {
                foreach ($this->possiblePropertyNames($name) as $property) {
                    if (property_exists($data, $property)) {
                        $found = true;
                        $value = $data->$property;
                        break;
                    }
                }
            }
            if (!$found) {
                $errors[] = fn() => yield "Object does not have property \"$name\".";
                continue;
            }

            $value = $field->getType()->getPrimitiveRepresentation($value);
            if ($value instanceof \Closure) {
                $errors[] = $value;
            } else {
                $repr->$name = $value;
            }

        }

        if ($errors) {
            return $this->mergeErrors($errors);
        }

        return $repr;
    }

    protected function possibleMethodNames(string $name): \Generator
    {
        $camel_cased = $this->camelCased($name);
        $snake_cased = $this->snakeCased($name);

        yield "get" . ucfirst($name);
        yield "get_" . $name;
        yield "get" . $camel_cased;
        yield "get_" . $snake_cased;
        yield "is" . ucfirst($name);
        yield "is_" . $name;
        yield "is" . $camel_cased;
        yield "is_" . $snake_cased;
    }

    protected function possiblePropertyNames(string $name): \Generator
    {
        yield $name;
        yield ucfirst($name);
        yield $this->camelCased($name);
        yield $this->snakeCased($name);
    }


    protected function camelCased(string $name): string
    {
        return preg_replace_callback("/_(\w)/", fn($v) => strtoupper($v[1]), $name);
    }

    protected function snakeCased(string $name): string
    {
        return preg_replace_callback("/[A-Z]/", fn($v) => "_" . strtolower($v[0]), $name);
    }
}
