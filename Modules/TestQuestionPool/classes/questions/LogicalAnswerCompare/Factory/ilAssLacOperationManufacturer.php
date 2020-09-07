<?php

require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Factory/ilAssLacAbstractManufacturer.php";

/**
 * Class OperationManufacturer
 *
 * Date: 25.03.13
 * Time: 15:12
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacOperationManufacturer extends ilAssLacAbstractManufacturer
{

    /**
     * A Singleton Instance of the OperationManufacturer
     *
     * @see OperatoinManufacturer::_getInstance()
     * @see OperationManufacturer::__construct()
     *
     * @var null|ilAssLacOperationManufacturer
     */
    protected static $instance = null;

    /**
     * Get an Instance of OperationManufacturer
     *
     * @return ilAssLacOperationManufacturer
     */
    public static function _getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new ilAssLacOperationManufacturer();
        }
        return self::$instance;
    }

    /**
     * Create a new specific Composite object which is representing the delivered Attribute
     *
     * @param string $attribute
     *
     * @return ilAssLacAbstractComposite|ilAssLacAndOperation|ilAssLacEqualsOperation|ilAssLacGreaterOperation|ilAssLacGreaterOrEqualsOperation|ilAssLacLesserOperation|ilAssLacLesserOrEqualsOperation|ilAssLacNotEqualsOperation|ilAssLacOrOperation
     * @throws ilAssLacUnsupportedOperation
     */
    public function manufacture($attribute)
    {
        $operation = "";
        switch ($attribute) {
            case ilAssLacLesserOperation::$pattern:
                $operation = new ilAssLacLesserOperation();
                break;
            case ilAssLacLesserOrEqualsOperation::$pattern:
                $operation = new ilAssLacLesserOrEqualsOperation();
                break;
            case ilAssLacEqualsOperation::$pattern:
                $operation = new ilAssLacEqualsOperation();
                break;
            case ilAssLacGreaterOrEqualsOperation::$pattern:
                $operation = new ilAssLacGreaterOrEqualsOperation();
                break;
            case ilAssLacGreaterOperation::$pattern:
                $operation = new ilAssLacGreaterOperation();
                break;
            case ilAssLacNotEqualsOperation::$pattern:
                $operation = new ilAssLacNotEqualsOperation();
                break;
            case ilAssLacAndOperation::$pattern:
                $operation = new ilAssLacAndOperation();
                break;
            case ilAssLacOrOperation::$pattern:
                $operation = new ilAssLacOrOperation();
                break;
            default:
                throw new ilAssLacUnsupportedOperation($attribute);
                break;
        }
        return $operation;
    }

    /**
     * This function create a regular expression to match all operators in a condition. <br />
     * The following string is created by this function <b>'/[\!&\|<>=]+/'</b><br />
     * It matches all operators in a condition and is divided into the following parts:
     *
     * <pre>
     * NEGATION:           !
     * AND:                &
     * OR:                 |
     * LESSER:             <
     * LESSER OR EQUALS:   <=
     * EQUALS              =
     * GREATER OR EQUALS   >=
     * GREATER             >
     * NOT EQUALS          <>
     * </pre>
     *
     * @return string
     */
    public function getPattern()
    {
        //		return '/[&\|<>=]+/';
        return '/&|\||(?<!<|>)=|<(?!=|>)|>(?!=)|<=|>=|<>/';
    }

    /**
     * Private construtor to prevent creating an object of OperationManufacturer
     */
    private function __construct()
    {
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacLesserOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacLesserOrEqualsOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacEqualsOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacGreaterOrEqualsOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacGreaterOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacNotEqualsOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacAndOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Operations/ilAssLacOrOperation.php';
        require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacUnsupportedOperation.php';
    }

    /**
     * Private clone to prevent cloning an object of OperationManufacturer
     */
    private function __clone()
    {
    }
}
