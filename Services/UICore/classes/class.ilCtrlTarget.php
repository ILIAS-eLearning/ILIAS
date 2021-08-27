<?php

/**
 * Class ilCtrlTarget is a data transfer object of a
 * link target that has been generated with ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlTarget
{
    /**
     * @var string separator for CID traces.
     */
    public const CID_TRACE_SEPARATOR = ':';

    /**
     * Holds the current CSRF token.
     *
     * @var string|null
     */
    private ?string $token;

    /**
     * Holds the current baseclass.
     *
     * @var string|null
     */
    private ?string $base_class;

    /**
     * Holds the current command class.
     *
     * @var string|null
     */
    private ?string $cmd_class;

    /**
     * Holds the current CID trace.
     *
     * @var string|null
     */
    private ?string $cid_trace;

    /**
     * Holds the current command.
     *
     * @var string|null
     */
    private ?string $cmd;

    /**
     * @var ilCtrlTarget|null
     */
    private ?self $nested_target;

    /**
     * ilCtrlTarget constructor.
     * @param string|null $token
     * @param string|null $base_class
     * @param string|null $cmd_class
     * @param string|null $cid_trace
     * @param string|null $cmd
     */
    public function __construct(
        ?string $token = null,
        ?string $base_class = null,
        ?string $cmd_class = null,
        ?string $cid_trace = null,
        ?string $cmd = null
    ) {
        $this->token = $token;
        $this->base_class = $base_class;
        $this->cmd_class = $cmd_class;
        $this->cid_trace = $cid_trace;
        $this->cmd = $cmd;
    }

    /**
     * @return string|null
     */
    public function getToken() : ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     * @return ilCtrlTarget
     */
    public function setToken(?string $token) : ilCtrlTarget
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBaseClass() : ?string
    {
        return $this->base_class;
    }

    /**
     * @param string $base_class
     * @return ilCtrlTarget
     */
    public function setBaseClass(string $base_class) : ilCtrlTarget
    {
        $this->base_class = $base_class;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCmdClass() : ?string
    {
        return $this->cmd_class;
    }

    /**
     * @param string $cmd_class
     * @return ilCtrlTarget
     */
    public function setCmdClass(string $cmd_class) : ilCtrlTarget
    {
        $this->cmd_class = $cmd_class;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCidTrace() : ?string
    {
        return $this->cid_trace;
    }

    /**
     * Returns the current CID from trace (the last appended).
     *
     * @return string|null
     */
    public function getCurrentCid() : ?string
    {
        if (null === $this->cid_trace) {
            return null;
        }

        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);
        $key    = (count($pieces) - 1);

        return $pieces[$key];
    }

    /**
     * Returns the current CID from a given trace (the last appended).
     *
     * @param string $cid_trace
     * @return string|null
     */
    public function getCurrentCidFrom(string $cid_trace) : ?string
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $cid_trace);
        $key    = (count($pieces) - 1);

        return $pieces[$key];
    }

    /**
     * Returns all CIDs of the current trace in the given direction.
     *
     * Note that this method will yield NULL if the current trace
     * was not yet set.
     *
     * @param int $sort
     * @return Generator
     */
    public function getCidPieces(int $sort = SORT_ASC) : Generator
    {
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);

        if (SORT_ASC === $sort) {
            foreach ($pieces as $cid) {
                yield $cid;
            }
        } else {
            for ($i = count($pieces) - 1; 0 <= $i; $i--) {
                yield $pieces[$i];
            }
        }
    }

    /**
     * Returns all individual paths for each cid position for the
     * given direction.
     *
     * For example, trace 'cid1:cid2:cid3' it would return:
     *      array(
     *          'cid1',
     *          'cid1:cid2',
     *          'cid1:cid2:cid3',
     *          ...
     *      );
     *
     * @param int $sort
     * @return array|null
     */
    public function getCidPaths(int $sort = SORT_ASC) : ?array
    {
        if (null === $this->cid_trace) {
            return null;
        }

        $paths = [];
        $pieces = explode(self::CID_TRACE_SEPARATOR, $this->cid_trace);
        foreach ($pieces as $index => $cid) {
            if (0 === $index) {
                $paths[] = $cid;
            } else {
                $paths[] = $paths[$index - 1] . $cid;
            }
        }

        if (SORT_DESC === $sort) {
            rsort($paths);
        }

        return $paths;
    }

    /**
     * @param string $cid_trace
     * @return ilCtrlTarget
     */
    public function setCidTrace(string $cid_trace) : ilCtrlTarget
    {
        $this->cid_trace = $cid_trace;
        return $this;
    }

    /**
     * Appends a CID to the current trace.
     *
     * @param string $cid
     * @return $this
     */
    public function appendCid(string $cid) : ilCtrlTarget
    {
        if (null === $this->cid_trace) {
            $this->cid_trace = $cid;
        } else {
            $this->cid_trace .= self::CID_TRACE_SEPARATOR . $cid;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCmd() : ?string
    {
        return $this->cmd;
    }

    /**
     * @param string $cmd
     * @return ilCtrlTarget
     */
    public function setCmd(string $cmd) : ilCtrlTarget
    {
        $this->cmd = $cmd;
        return $this;
    }

    /**
     * @param ilCtrlTarget|null $target
     * @return $this
     */
    public function setNestedTarget(?self $target) : ilCtrlTarget
    {
        $this->nested_target = $target;
        return $this;
    }

    /**
     * @return ilCtrlTarget|null
     */
    public function getNestedTarget() : ?ilCtrlTarget
    {
        return $this->nested_target;
    }
}