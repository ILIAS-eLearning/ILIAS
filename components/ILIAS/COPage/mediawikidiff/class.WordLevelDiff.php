<?php

// Copyright holded by MediaWiki contributers, Licensed under GPL version 2 or later


/**
 * See diff.doc
 * @todo indicate where diff.doc can be found.
 * @addtogroup DifferenceEngine
 */


// A PHP diff engine for phpwiki. (Taken from phpwiki-1.3.3)
//
// Copyright (C) 2000, 2001 Geoffrey T. Dairiki <dairiki@dairiki.org>
// You may copy this code freely under the conditions of the GPL.
//

define('USE_ASSERTS', function_exists('assert'));

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
abstract class _DiffOp
{
    public $type;
    public $orig;
    public $closing;
}

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class _DiffOp_Copy extends _DiffOp
{
    public $type = 'copy';

    public function __construct($orig, $closing = false)
    {
        if (!is_array($closing)) {
            $closing = $orig;
        }
        $this->orig = $orig;
        $this->closing = $closing;
    }
}

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class _DiffOp_Delete extends _DiffOp
{
    public $type = 'delete';

    public function __construct($lines)
    {
        $this->orig = $lines;
        $this->closing = false;
    }
}

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class _DiffOp_Add extends _DiffOp
{
    public $type = 'add';

    public function __construct($lines)
    {
        $this->closing = $lines;
        $this->orig = false;
    }
}

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class _DiffOp_Change extends _DiffOp
{
    public $type = 'change';

    public function __construct($orig, $closing)
    {
        $this->orig = $orig;
        $this->closing = $closing;
    }
}


/**
 * Class used internally by Diff to actually compute the diffs.
 *
 * The algorithm used here is mostly lifted from the perl module
 * Algorithm::Diff (version 1.06) by Ned Konz, which is available at:
 *	 http://www.perl.com/CPAN/authors/id/N/NE/NEDKONZ/Algorithm-Diff-1.06.zip
 *
 * More ideas are taken from:
 *	 http://www.ics.uci.edu/~eppstein/161/960229.html
 *
 * Some ideas are (and a bit of code) are from from analyze.c, from GNU
 * diffutils-2.7, which can be found at:
 *	 ftp://gnudist.gnu.org/pub/gnu/diffutils/diffutils-2.7.tar.gz
 *
 * closingly, some ideas (subdivision by NCHUNKS > 2, and some optimizations)
 * are my own.
 *
 * Line length limits for robustness added by Tim Starling, 2005-08-31
 *
 * @author Geoffrey T. Dairiki, Tim Starling
 * @private
 * @addtogroup DifferenceEngine
 */
class _DiffEngine
{
    public const MAX_XREF_LENGTH = 10000;
    private array $xchanged;
    private array $xv;
    private array $xind;
    private array $ychanged;
    private array $yv;
    private array $yind;
    private int $lcs;
    private array $in_seq;
    private array $seq = [];

    public function diff($from_lines, $to_lines)
    {

        $n_from = sizeof($from_lines);
        $n_to = sizeof($to_lines);

        $this->xchanged = $this->ychanged = array();
        $this->xv = $this->yv = array();
        $this->xind = $this->yind = array();
        unset($this->seq);
        unset($this->in_seq);
        unset($this->lcs);

        // Skip leading common lines.
        for ($skip = 0; $skip < $n_from && $skip < $n_to; $skip++) {
            if ($from_lines[$skip] !== $to_lines[$skip]) {
                break;
            }
            $this->xchanged[$skip] = $this->ychanged[$skip] = false;
        }
        // Skip trailing common lines.
        $xi = $n_from;
        $yi = $n_to;
        for ($endskip = 0; --$xi > $skip && --$yi > $skip; $endskip++) {
            if ($from_lines[$xi] !== $to_lines[$yi]) {
                break;
            }
            $this->xchanged[$xi] = $this->ychanged[$yi] = false;
        }

        // Ignore lines which do not exist in both files.
        for ($xi = $skip; $xi < $n_from - $endskip; $xi++) {
            $xhash[$this->_line_hash($from_lines[$xi])] = 1;
        }

        for ($yi = $skip; $yi < $n_to - $endskip; $yi++) {
            $line = $to_lines[$yi];
            if (($this->ychanged[$yi] = empty($xhash[$this->_line_hash($line)]))) {
                continue;
            }
            $yhash[$this->_line_hash($line)] = 1;
            $this->yv[] = $line;
            $this->yind[] = $yi;
        }
        for ($xi = $skip; $xi < $n_from - $endskip; $xi++) {
            $line = $from_lines[$xi];
            if (($this->xchanged[$xi] = empty($yhash[$this->_line_hash($line)]))) {
                continue;
            }
            $this->xv[] = $line;
            $this->xind[] = $xi;
        }

        // Find the LCS.
        $this->_compareseq(0, sizeof($this->xv), 0, sizeof($this->yv));

        // Merge edits when possible
        $this->_shift_boundaries($from_lines, $this->xchanged, $this->ychanged);
        $this->_shift_boundaries($to_lines, $this->ychanged, $this->xchanged);

        // Compute the edit operations.
        $edits = array();
        $xi = $yi = 0;
        while ($xi < $n_from || $yi < $n_to) {
            USE_ASSERTS && assert($yi < $n_to || $this->xchanged[$xi]);
            USE_ASSERTS && assert($xi < $n_from || $this->ychanged[$yi]);

            // Skip matching "snake".
            $copy = array();
            while ($xi < $n_from && $yi < $n_to
                    && !$this->xchanged[$xi] && !$this->ychanged[$yi]) {
                $copy[] = $from_lines[$xi++];
                ++$yi;
            }
            if ($copy) {
                $edits[] = new _DiffOp_Copy($copy);
            }

            // Find deletes & adds.
            $delete = array();
            while ($xi < $n_from && $this->xchanged[$xi]) {
                $delete[] = $from_lines[$xi++];
            }

            $add = array();
            while ($yi < $n_to && $this->ychanged[$yi]) {
                $add[] = $to_lines[$yi++];
            }

            if ($delete && $add) {
                $edits[] = new _DiffOp_Change($delete, $add);
            } elseif ($delete) {
                $edits[] = new _DiffOp_Delete($delete);
            } elseif ($add) {
                $edits[] = new _DiffOp_Add($add);
            }
        }
        return $edits;
    }

    /**
     * Returns the whole line if it's small enough, or the MD5 hash otherwise
     */
    public function _line_hash($line)
    {
        if (strlen($line) > self::MAX_XREF_LENGTH) {
            return md5($line);
        } else {
            return $line;
        }
    }


    /* Divide the Largest Common Subsequence (LCS) of the sequences
     * [XOFF, XLIM) and [YOFF, YLIM) into NCHUNKS approximately equally
     * sized segments.
     *
     * Returns (LCS, PTS).	LCS is the length of the LCS. PTS is an
     * array of NCHUNKS+1 (X, Y) indexes giving the diving points between
     * sub sequences.  The first sub-sequence is contained in [X0, X1),
     * [Y0, Y1), the second in [X1, X2), [Y1, Y2) and so on.  Note
     * that (X0, Y0) == (XOFF, YOFF) and
     * (X[NCHUNKS], Y[NCHUNKS]) == (XLIM, YLIM).
     *
     * This function assumes that the first lines of the specified portions
     * of the two files do not match, and likewise that the last lines do not
     * match.  The caller must trim matching lines from the beginning and end
     * of the portions it is going to specify.
     */
    public function _diag($xoff, $xlim, $yoff, $ylim, $nchunks)
    {
        $flip = false;

        if ($xlim - $xoff > $ylim - $yoff) {
            // Things seems faster (I'm not sure I understand why)
            // when the shortest sequence in X.
            $flip = true;
            list($xoff, $xlim, $yoff, $ylim)
            = array( $yoff, $ylim, $xoff, $xlim);
        }

        if ($flip) {
            for ($i = $ylim - 1; $i >= $yoff; $i--) {
                $ymatches[$this->xv[$i]][] = $i;
            }
        } else {
            for ($i = $ylim - 1; $i >= $yoff; $i--) {
                $ymatches[$this->yv[$i]][] = $i;
            }
        }

        $this->lcs = 0;
        $this->seq[0] = $yoff - 1;
        $this->in_seq = array();
        $ymids[0] = array();

        $numer = $xlim - $xoff + $nchunks - 1;
        $x = $xoff;
        for ($chunk = 0; $chunk < $nchunks; $chunk++) {
            if ($chunk > 0) {
                for ($i = 0; $i <= $this->lcs; $i++) {
                    $ymids[$i][$chunk - 1] = $this->seq[$i];
                }
            }

            $x1 = $xoff + (int) (($numer + ($xlim - $xoff) * $chunk) / $nchunks);
            for (; $x < $x1; $x++) {
                $line = $flip ? $this->yv[$x] : $this->xv[$x];
                if (empty($ymatches[$line])) {
                    continue;
                }
                $matches = $ymatches[$line];
                reset($matches);
                foreach ($matches as $junk => $y) {
                    if (empty($this->in_seq[$y])) {
                        $k = $this->_lcs_pos($y);
                        USE_ASSERTS && assert($k > 0);
                        $ymids[$k] = $ymids[$k - 1];
                        break;
                    }
                }
                foreach ($matches as $y) {
                    if ($y > $this->seq[$k - 1]) {
                        USE_ASSERTS && assert($y < $this->seq[$k]);
                        // Optimization: this is a common case:
                        //	next match is just replacing previous match.
                        $this->in_seq[$this->seq[$k]] = false;
                        $this->seq[$k] = $y;
                        $this->in_seq[$y] = 1;
                    } elseif (empty($this->in_seq[$y])) {
                        $k = $this->_lcs_pos($y);
                        USE_ASSERTS && assert($k > 0);
                        $ymids[$k] = $ymids[$k - 1];
                    }
                }
            }
        }

        $seps[] = $flip ? array($yoff, $xoff) : array($xoff, $yoff);
        $ymid = $ymids[$this->lcs];
        for ($n = 0; $n < $nchunks - 1; $n++) {
            $x1 = $xoff + (int) (($numer + ($xlim - $xoff) * $n) / $nchunks);
            $y1 = $ymid[$n] + 1;
            $seps[] = $flip ? array($y1, $x1) : array($x1, $y1);
        }
        $seps[] = $flip ? array($ylim, $xlim) : array($xlim, $ylim);

        return array($this->lcs, $seps);
    }

    public function _lcs_pos($ypos)
    {

        $end = $this->lcs;
        if ($end == 0 || $ypos > $this->seq[$end]) {
            $this->seq[++$this->lcs] = $ypos;
            $this->in_seq[$ypos] = 1;
            return $this->lcs;
        }

        $beg = 1;
        while ($beg < $end) {
            $mid = (int) (($beg + $end) / 2);
            if ($ypos > $this->seq[$mid]) {
                $beg = $mid + 1;
            } else {
                $end = $mid;
            }
        }

        USE_ASSERTS && assert($ypos != $this->seq[$end]);

        $this->in_seq[$this->seq[$end]] = false;
        $this->seq[$end] = $ypos;
        $this->in_seq[$ypos] = 1;
        return $end;
    }

    /* Find LCS of two sequences.
     *
     * The results are recorded in the vectors $this->{x,y}changed[], by
     * storing a 1 in the element for each line that is an insertion
     * or deletion (ie. is not in the LCS).
     *
     * The subsequence of file 0 is [XOFF, XLIM) and likewise for file 1.
     *
     * Note that XLIM, YLIM are exclusive bounds.
     * All line numbers are origin-0 and discarded lines are not counted.
     */
    public function _compareseq($xoff, $xlim, $yoff, $ylim)
    {

        // Slide down the bottom initial diagonal.
        while ($xoff < $xlim && $yoff < $ylim
               && $this->xv[$xoff] == $this->yv[$yoff]) {
            ++$xoff;
            ++$yoff;
        }

        // Slide up the top initial diagonal.
        while ($xlim > $xoff && $ylim > $yoff
               && $this->xv[$xlim - 1] == $this->yv[$ylim - 1]) {
            --$xlim;
            --$ylim;
        }

        if ($xoff == $xlim || $yoff == $ylim) {
            $lcs = 0;
        } else {
            // This is ad hoc but seems to work well.
            //$nchunks = sqrt(min($xlim - $xoff, $ylim - $yoff) / 2.5);
            //$nchunks = max(2,min(8,(int)$nchunks));
            $nchunks = min(7, $xlim - $xoff, $ylim - $yoff) + 1;
            list($lcs, $seps)
            = $this->_diag($xoff, $xlim, $yoff, $ylim, $nchunks);
        }

        if ($lcs == 0) {
            // X and Y sequences have no common subsequence:
            // mark all changed.
            while ($yoff < $ylim) {
                $this->ychanged[$this->yind[$yoff++]] = 1;
            }
            while ($xoff < $xlim) {
                $this->xchanged[$this->xind[$xoff++]] = 1;
            }
        } else {
            // Use the partitions to split this problem into subproblems.
            reset($seps);
            $pt1 = $seps[0];
            while ($pt2 = next($seps)) {
                $this->_compareseq($pt1[0], $pt2[0], $pt1[1], $pt2[1]);
                $pt1 = $pt2;
            }
        }
    }

    /* Adjust inserts/deletes of identical lines to join changes
     * as much as possible.
     *
     * We do something when a run of changed lines include a
     * line at one end and has an excluded, identical line at the other.
     * We are free to choose which identical line is included.
     * `compareseq' usually chooses the one at the beginning,
     * but usually it is cleaner to consider the following identical line
     * to be the "change".
     *
     * This is extracted verbatim from analyze.c (GNU diffutils-2.7).
     */
    public function _shift_boundaries($lines, &$changed, $other_changed)
    {
        $i = 0;
        $j = 0;

        USE_ASSERTS && assert(sizeof($lines) == sizeof($changed));
        $len = sizeof($lines);
        $other_len = sizeof($other_changed);

        while (1) {
            /*
             * Scan forwards to find beginning of another run of changes.
             * Also keep track of the corresponding point in the other file.
             *
             * Throughout this code, $i and $j are adjusted together so that
             * the first $i elements of $changed and the first $j elements
             * of $other_changed both contain the same number of zeros
             * (unchanged lines).
             * Furthermore, $j is always kept so that $j == $other_len or
             * $other_changed[$j] == false.
             */
            while ($j < $other_len && $other_changed[$j]) {
                $j++;
            }

            while ($i < $len && !$changed[$i]) {
                USE_ASSERTS && assert($j < $other_len && !$other_changed[$j]);
                $i++;
                $j++;
                while ($j < $other_len && $other_changed[$j]) {
                    $j++;
                }
            }

            if ($i == $len) {
                break;
            }

            $start = $i;

            // Find the end of this run of changes.
            while (++$i < $len && $changed[$i]) {
            }

            do {
                /*
                 * Record the length of this run of changes, so that
                 * we can later determine whether the run has grown.
                 */
                $runlength = $i - $start;

                /*
                 * Move the changed region back, so long as the
                 * previous unchanged line matches the last changed one.
                 * This merges with previous changed regions.
                 */
                while ($start > 0 && $lines[$start - 1] == $lines[$i - 1]) {
                    $changed[--$start] = 1;
                    $changed[--$i] = false;
                    while ($start > 0 && $changed[$start - 1]) {
                        $start--;
                    }
                    USE_ASSERTS && assert($j > 0);
                    while ($other_changed[--$j]) {
                    }
                    USE_ASSERTS && assert($j >= 0 && !$other_changed[$j]);
                }

                /*
                 * Set CORRESPONDING to the end of the changed run, at the last
                 * point where it corresponds to a changed run in the other file.
                 * CORRESPONDING == LEN means no such point has been found.
                 */
                $corresponding = $j < $other_len ? $i : $len;

                /*
                 * Move the changed region forward, so long as the
                 * first changed line matches the following unchanged one.
                 * This merges with following changed regions.
                 * Do this second, so that if there are no merges,
                 * the changed region is moved forward as far as possible.
                 */
                while ($i < $len && $lines[$start] == $lines[$i]) {
                    $changed[$start++] = false;
                    $changed[$i++] = 1;
                    while ($i < $len && $changed[$i]) {
                        $i++;
                    }

                    USE_ASSERTS && assert($j < $other_len && !$other_changed[$j]);
                    $j++;
                    if ($j < $other_len && $other_changed[$j]) {
                        $corresponding = $i;
                        while ($j < $other_len && $other_changed[$j]) {
                            $j++;
                        }
                    }
                }
            } while ($runlength != $i - $start);

            /*
             * If possible, move the fully-merged run of changes
             * back to a corresponding run in the other file.
             */
            while ($corresponding < $i) {
                $changed[--$start] = 1;
                $changed[--$i] = 0;
                USE_ASSERTS && assert($j > 0);
                while ($other_changed[--$j]) {
                }
                USE_ASSERTS && assert($j >= 0 && !$other_changed[$j]);
            }
        }
    }
}

/**
 * Class representing a 'diff' between two sequences of strings.
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class Diff
{
    public $edits;

    /**
     * Constructor.
     * Computes diff between sequences of strings.
     *
     * @param $from_lines array An array of strings.
     *		  (Typically these are lines from a file.)
     * @param $to_lines array An array of strings.
     */
    public function __construct($from_lines, $to_lines)
    {
        $eng = new _DiffEngine();
        $this->edits = $eng->diff($from_lines, $to_lines);
    }
}

/**
 * @todo document, bad name.
 * @private
 * @addtogroup DifferenceEngine
 */
class MappedDiff extends Diff
{
    /**
     * Constructor.
     *
     * Computes diff between sequences of strings.
     *
     * This can be used to compute things like
     * case-insensitve diffs, or diffs which ignore
     * changes in white-space.
     *
     * @param $from_lines array An array of strings.
     *	(Typically these are lines from a file.)
     *
     * @param $to_lines array An array of strings.
     *
     * @param $mapped_from_lines array This array should
     *	have the same size number of elements as $from_lines.
     *	The elements in $mapped_from_lines and
     *	$mapped_to_lines are what is actually compared
     *	when computing the diff.
     *
     * @param $mapped_to_lines array This array should
     *	have the same number of elements as $to_lines.
     */
    public function __construct(
        $from_lines,
        $to_lines,
        $mapped_from_lines,
        $mapped_to_lines
    ) {
        assert(sizeof($from_lines) == sizeof($mapped_from_lines));
        assert(sizeof($to_lines) == sizeof($mapped_to_lines));

        parent::__construct($mapped_from_lines, $mapped_to_lines);

        $xi = $yi = 0;
        for ($i = 0; $i < sizeof($this->edits); $i++) {
            $orig = &$this->edits[$i]->orig;
            if (is_array($orig)) {
                $orig = array_slice($from_lines, $xi, sizeof($orig));
                $xi += sizeof($orig);
            }

            $closing = &$this->edits[$i]->closing;
            if (is_array($closing)) {
                $closing = array_slice($to_lines, $yi, sizeof($closing));
                $yi += sizeof($closing);
            }
        }
    }
}

/**
 *	Additions by Axel Boldt follow, partly taken from diff.php, phpwiki-1.3.3
 *
 */

define('NBSP', '&#160;');			// iso-8859-x non-breaking space.

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class _HWLDF_WordAccumulator
{
    private array $_lines;
    private string $_line;
    private string $_group;
    private string $_tag;

    public function __construct()
    {
        $this->_lines = array();
        $this->_line = '';
        $this->_group = '';
        $this->_tag = '';
    }

    public function _flushGroup($new_tag)
    {
        if ($this->_group !== '') {
            if ($this->_tag == 'ins') {
                $this->_line .= '[ilDiffInsStart]' .
                    htmlspecialchars($this->_group) . '[ilDiffInsEnd]';
            } elseif ($this->_tag == 'del') {
                $this->_line .= '[ilDiffDelStart]' .
                    htmlspecialchars($this->_group) . '[ilDiffDelEnd]';
            } else {
                $this->_line .= htmlspecialchars($this->_group);
            }
        }
        $this->_group = '';
        $this->_tag = $new_tag;
    }

    public function _flushLine($new_tag)
    {
        $this->_flushGroup($new_tag);
        if ($this->_line != '') {
            array_push($this->_lines, $this->_line);
        } else {
            # make empty lines visible by inserting an NBSP
            array_push($this->_lines, NBSP);
        }
        $this->_line = '';
    }

    public function addWords($words, $tag = '')
    {
        if ($tag != $this->_tag) {
            $this->_flushGroup($tag);
        }

        foreach ($words as $word) {
            // new-line should only come as first char of word.
            if ($word == '') {
                continue;
            }
            if ($word[0] == "\n") {
                $this->_flushLine($tag);
                $word = substr($word, 1);
            }
            assert(!strstr($word, "\n"));
            $this->_group .= $word;
        }
    }

    public function getLines()
    {
        $this->_flushLine('~done');
        return $this->_lines;
    }
}

/**
 * @todo document
 * @private
 * @addtogroup DifferenceEngine
 */
class WordLevelDiff extends MappedDiff
{
    public const MAX_LINE_LENGTH = 10000;

    public function __construct($orig_lines, $closing_lines)
    {
        list($orig_words, $orig_stripped) = $this->_split($orig_lines);
        list($closing_words, $closing_stripped) = $this->_split($closing_lines);

        parent::__construct(
            $orig_words,
            $closing_words,
            $orig_stripped,
            $closing_stripped
        );
    }

    public function _split($lines)
    {
        $words = array();
        $stripped = array();
        $first = true;
        foreach ($lines as $line) {
            # If the line is too long, just pretend the entire line is one big word
            # This prevents resource exhaustion problems
            if ($first) {
                $first = false;
            } else {
                $words[] = "\n";
                $stripped[] = "\n";
            }
            if (strlen($line) > self::MAX_LINE_LENGTH) {
                $words[] = $line;
                $stripped[] = $line;
            } else {
                $m = array();
                if (preg_match_all(
                    '/ ( [^\S\n]+ | [0-9_A-Za-z\x80-\xff]+ | . ) (?: (?!< \n) [^\S\n])? /xs',
                    $line,
                    $m
                )) {
                    $words = array_merge($words, $m[0]);
                    $stripped = array_merge($stripped, $m[1]);
                }
            }
        }
        return array($words, $stripped);
    }

    public function orig()
    {
        $orig = new _HWLDF_WordAccumulator();

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy') {
                $orig->addWords($edit->orig);
            } elseif ($edit->orig) {
                $orig->addWords($edit->orig, 'del');
            }
        }
        $lines = $orig->getLines();
        return $lines;
    }

    public function closing()
    {
        $closing = new _HWLDF_WordAccumulator();

        foreach ($this->edits as $edit) {
            if ($edit->type == 'copy') {
                $closing->addWords($edit->closing);
            } elseif ($edit->closing) {
                $closing->addWords($edit->closing, 'ins');
            }
        }
        $lines = $closing->getLines();
        return $lines;
    }
}
