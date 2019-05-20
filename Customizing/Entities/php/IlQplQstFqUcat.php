<?php



/**
 * IlQplQstFqUcat
 */
class IlQplQstFqUcat
{
    /**
     * @var int
     */
    private $categoryId = '0';

    /**
     * @var string|null
     */
    private $category;

    /**
     * @var int
     */
    private $questionFi = '0';


    /**
     * Get categoryId.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set category.
     *
     * @param string|null $category
     *
     * @return IlQplQstFqUcat
     */
    public function setCategory($category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return IlQplQstFqUcat
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }
}
