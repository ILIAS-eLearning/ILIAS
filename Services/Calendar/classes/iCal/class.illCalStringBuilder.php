<?php

class illCalStringBuilder
{
    /**
     * @var string[]
     */
    protected $lines;

    public function __construct()
    {
        $this->lines = [];
    }

    public function addLine(string $line) : void
    {
        $this->lines[] = $line;
    }

    public function append(illCalStringBuilder $str_builder) : void
    {
        foreach ($str_builder->lines as $line) {
            $this->lines[] = $line;
        }
    }

    public function asWriter() : ilICalWriter
    {
        $writer = new ilICalWriter();
        foreach ($this->lines as $line) {
            $writer->addLine($line);
        }
        return $writer;
    }

    public function clear() : void
    {
        $this->lines = [];
    }

    public function byteCount() : int
    {
        $buffer = '';

        foreach ($this->lines as $line) {
            $buffer .= $line . "\n";
        }

        return strlen($buffer);
    }
}
