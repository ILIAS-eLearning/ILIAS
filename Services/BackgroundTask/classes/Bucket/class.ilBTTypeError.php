<?php

class ilBTTypeError {

	/**
	 * @var string the input IO type.
	 */
	protected $givenInputType;

	/**
	 * @var string the expected IO type.
	 */
	protected $expectedInputType;

	/**
	 * @var string The job that receives the input
	 */
	protected $consumerJob;

	/**
	 * @var string the source of the input (a job or the user)
	 */
	protected $producerJob;

	public function __toString() {
		$source = $this->producerJob?$this->producerJob:'First input';
		return "{$this->consumerJob} expects an input of type {$this->expectedInputType} but {$this->givenInputType} is given (produced by: $source)";
	}


	/**
	 * @param \ilBTJob $job
	 * @param \ilBTIO $input
	 * @param \ilBTJob|null $producer
	 */
	public static function getInstanceByClasses(ilBTJob $job, ilBTIO $input, ilBTJob $producer = null) {
		$instance = new self();
		$instance->setExpectedInputType($job->getInputType());
		$instance->setGivenInputType($input->getType());
	}

	/**
	 * @return string
	 */
	public function getGivenInputType() {
		return $this->givenInputType;
	}

	/**
	 * @param string $givenInputType
	 */
	public function setGivenInputType($givenInputType) {
		$this->givenInputType = $givenInputType;
	}

	/**
	 * @return string
	 */
	public function getExpectedInputType() {
		return $this->expectedInputType;
	}

	/**
	 * @param string $expectedInputType
	 */
	public function setExpectedInputType($expectedInputType) {
		$this->expectedInputType = $expectedInputType;
	}

	/**
	 * @return string
	 */
	public function getConsumerJob() {
		return $this->consumerJob;
	}

	/**
	 * @param string $consumerJob
	 */
	public function setConsumerJob($consumerJob) {
		$this->consumerJob = $consumerJob;
	}

	/**
	 * @return string
	 */
	public function getProducerJob() {
		return $this->producerJob;
	}

	/**
	 * @param string $producerJob
	 */
	public function setProducerJob($producerJob) {
		$this->producerJob = $producerJob;
	}
}