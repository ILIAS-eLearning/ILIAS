<?php

/**
 * This file is part of SebastianFeldmann\Git.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianFeldmann\Git\Command\Log;

use SebastianFeldmann\Git\Command\Base;

/**
 * Class Log
 *
 * @package SebastianFeldmann\Git
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/git
 * @since   Class available since Release 0.9.0
 */
abstract class Log extends Base
{
    /**
     * Pretty log format.
     * --pretty
     *
     * @var string
     */
    protected $format = '%h -%d %s (%ci) <%an>';

    /**
     * Include or hide merge commits.
     * --no-merges
     *
     * @var string
     */
    protected $merges = ' --no-merges';

    /**
     * Shorten commit hashes.
     * --abbrev-commit
     *
     * @var string
     */
    protected $abbrev = ' --abbrev-commit';

    /**
     * Can be revision or date query.
     *  1.0.0..
     *  0.9.0..1.2.0
     *  --after='2016-12-31'
     *  --after='2016-12-31' --before='2017-01-31'
     *
     * @var string
     */
    protected $since;

    /**
     * Filter log by author.
     * --author
     *
     * @var string
     */
    protected $author;

    /**
     * Define the pretty log format.
     *
     * @param  string $format
     * @return \SebastianFeldmann\Git\Command\Log\Log
     */
    public function prettyFormat(string $format): Log
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Define merge commit behaviour.
     *
     * @param  bool $bool
     * @return \SebastianFeldmann\Git\Command\Log\Log
     */
    public function withMerges(bool $bool = true): Log
    {
        $this->merges = ($bool ? '' : ' --no-merges');
        return $this;
    }

    /**
     * Define commit hash behaviour.
     *
     * @param  bool $bool
     * @return \SebastianFeldmann\Git\Command\Log\Log
     */
    public function abbrevCommit(bool $bool = true): Log
    {
        $this->abbrev = ($bool ? ' --abbrev--commit' : '');
        return $this;
    }

    /**
     * Set revision range.
     *
     * @param  string $from
     * @param  string $to
     * @return \SebastianFeldmann\Git\Command\Log\Log
     */
    public function byRevision(string $from, string $to = ''): Log
    {
        $this->since = ' ' . escapeshellarg($from) . '..'
                     . (empty($to) ? '' : escapeshellarg($to));
        return $this;
    }

    /**
     * Set author filter.
     *
     * @param  string $author
     * @return \SebastianFeldmann\Git\Command\Log\Log
     */
    public function authoredBy(string $author): Log
    {
        $this->author = ' --author=' . escapeshellarg($author);
        return $this;
    }

    /**
     * Set date range.
     *
     * @param  string $from
     * @param  string $to
     * @return \SebastianFeldmann\Git\Command\Log\Log
     */
    public function byDate(string $from, string $to = ''): Log
    {
        $this->since = ' --after=' . escapeshellarg($from)
                     . (empty($to) ? '' : ' --before=' . escapeshellarg($to));
        return $this;
    }
}
