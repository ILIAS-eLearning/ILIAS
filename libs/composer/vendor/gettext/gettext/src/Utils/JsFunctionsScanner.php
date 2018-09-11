<?php

namespace Gettext\Utils;

class JsFunctionsScanner extends FunctionsScanner
{
    protected $code;
    protected $status = array();

    /**
     * Constructor.
     *
     * @param string $code The php code to scan
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $length = strlen($this->code);
        $line = 1;
        $buffer = '';
        $functions = array();
        $bufferFunctions = array();
        $char = null;

        for ($pos = 0; $pos < $length; ++$pos) {
            $prev = $char;
            $char = $this->code[$pos];
            $next = isset($this->code[$pos]) ? $this->code[$pos] : null;

            switch ($char) {
                case "\n":
                    ++$line;

                    if ($this->status('line-comment')) {
                        $this->upStatus();
                    }
                    break;

                case '/':
                    switch ($this->status()) {
                        case 'simple-quote':
                        case 'double-quote':
                        case 'line-comment':
                            break;

                        case 'block-comment':
                            if ($prev === '*') {
                                $this->upStatus();
                            }
                            break;

                        default:
                            if ($next === '/') {
                                $this->downStatus('line-comment');
                            } elseif ($next === '*') {
                                $this->downStatus('block-comment');
                            }
                            break;
                    }
                    break;

                case "'":
                    switch ($this->status()) {
                        case 'simple-quote':
                            $this->upStatus();
                            break;

                        case 'line-comment':
                        case 'block-comment':
                        case 'double-quote':
                            break;

                        default:
                            $this->downStatus('simple-quote');
                            break;
                    }
                    break;

                case '"':
                    switch ($this->status()) {
                        case 'double-quote':
                            $this->upStatus();
                            break;

                        case 'line-comment':
                        case 'block-comment':
                        case 'simple-quote':
                            break;

                        default:
                            $this->downStatus('double-quote');
                            break;
                    }
                    break;

                case '(':
                    switch ($this->status()) {
                        case 'double-quote':
                        case 'line-comment':
                        case 'block-comment':
                        case 'line-comment':
                            break;

                        default:
                            if ($buffer && preg_match('/(\w+)$/', $buffer, $matches)) {
                                $this->downStatus('function');
                                array_unshift($bufferFunctions, array($matches[1], $line, array()));
                                $buffer = '';
                                continue 3;
                            }
                            break;
                    }
                    break;

                case ')':
                    switch ($this->status()) {
                        case 'function':
                            if (($argument = self::prepareArgument($buffer))) {
                                $bufferFunctions[0][2][] = $argument;
                            }

                            if (!empty($bufferFunctions)) {
                                $functions[] = array_shift($bufferFunctions);
                            }

                            $buffer = '';
                            continue 3;
                    }

                case ',':
                    switch ($this->status()) {
                        case 'function':
                            if (($argument = self::prepareArgument($buffer))) {
                                $bufferFunctions[0][2][] = $argument;
                            }

                            $buffer = '';
                            continue 3;
                    }
            }

            switch ($this->status()) {
                case 'line-comment':
                case 'block-comment':
                    break;

                default:
                    $buffer .= $char;
                    break;
            }
        }

        return $functions;
    }

    /**
     * Get the current context of the scan.
     *
     * @param null|string $match To check whether the current status is this value
     *
     * @return string|bool
     */
    protected function status($match = null)
    {
        $status = isset($this->status[0]) ? $this->status[0] : null;

        if ($match !== null) {
            return $status === $match;
        }

        return $status;
    }

    /**
     * Add a new status to the stack.
     *
     * @param string $status
     */
    protected function downStatus($status)
    {
        array_unshift($this->status, $status);
    }

    /**
     * Removes and return the current status.
     *
     * @return string|null
     */
    protected function upStatus()
    {
        return array_shift($this->status);
    }

    /**
     * Prepares the arguments found in functions.
     *
     * @param string $argument
     *
     * @return string
     */
    protected static function prepareArgument($argument)
    {
        if ($argument && ($argument[0] === '"' || $argument[0] === "'")) {
            if ($argument[0] === '"') {
                $argument = str_replace('\\"', '"', $argument);
            } else {
                $argument = str_replace("\\'", "'", $argument);
            }

            return substr($argument, 1, -1);
        }
    }
}
