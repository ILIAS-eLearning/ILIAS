<?php



/**
 * QplQstImagemap
 */
class QplQstImagemap
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $imageFile;

    /**
     * @var bool
     */
    private $isMultipleChoice = '0';


    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set imageFile.
     *
     * @param string|null $imageFile
     *
     * @return QplQstImagemap
     */
    public function setImageFile($imageFile = null)
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    /**
     * Get imageFile.
     *
     * @return string|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set isMultipleChoice.
     *
     * @param bool $isMultipleChoice
     *
     * @return QplQstImagemap
     */
    public function setIsMultipleChoice($isMultipleChoice)
    {
        $this->isMultipleChoice = $isMultipleChoice;

        return $this;
    }

    /**
     * Get isMultipleChoice.
     *
     * @return bool
     */
    public function getIsMultipleChoice()
    {
        return $this->isMultipleChoice;
    }
}
