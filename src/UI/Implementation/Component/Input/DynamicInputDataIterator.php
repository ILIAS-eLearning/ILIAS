<?php declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use Iterator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DynamicInputDataIterator implements Iterator
{
    protected string $parent_input_name;
    protected array $post_data;
    protected int $index = 0;

    public function __construct(InputData $data, string $parent_input_name)
    {
        $this->post_data = $data->getOr($parent_input_name, []);
        $this->parent_input_name = $parent_input_name;
    }

    public function current() : ?InputData
    {
        if ($this->valid()) {
            $entry = [];
            // For the dynamic input, the values for a field "foo" a dynamically
            // created input are all listed in one field "foo" in the input. For
            // every subsequent input, we need to pick the next value from that
            // list and map it to "foo" for further processing.
            foreach ($this->post_data as $key => $data) {
                $entry[$key] = $data[$this->index];
            }

            return new ArrayInputData($entry);
        }

        return null;
    }

    public function next() : void
    {
        $this->index++;
    }

    public function key() : ?int
    {
        if ($this->valid()) {
            return $this->index;
        }

        return null;
    }

    public function valid() : bool
    {
        if (empty($this->post_data)) {
            return false;
        }

        foreach ($this->post_data as $input_data) {
            if (!isset($input_data[$this->index])) {
                return false;
            }
        }

        return true;
    }

    public function rewind() : void
    {
        $this->index = 0;
    }
}
