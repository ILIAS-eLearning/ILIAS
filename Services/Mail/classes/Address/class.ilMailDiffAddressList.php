<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailDiffAddressList
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailDiffAddressList implements \ilMailAddressList
{
    /** @var \ilMailAddressList */
    protected $left;

    /** @var \ilMailAddressList */
    protected $right;

    /**
     * ilMailDiffAddressList constructor.
     * @param \ilMailAddressList $left
     * @param \ilMailAddressList $right
     */
    public function __construct(\ilMailAddressList $left, \ilMailAddressList $right)
    {
        $this->left = $left;
        $this->right = $right;
    }


    /**
     * @inheritdoc
     */
    public function value() : array
    {
        $leftAddresses = $this->left->value();
        $rightAddresses = $this->right->value();

        return array_diff($leftAddresses, $rightAddresses);
    }
}
