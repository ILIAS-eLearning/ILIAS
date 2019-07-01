<?php

class ilGeoLocation {

	/** @var int */
	protected $id;

	/** @var string */
	protected $title;

	/** @var float */
	protected $latitude;

	/** @var float */
	protected $longitude;

	/** @var DateTimeImmutable */
	protected $expiration_timestamp;

	public function __construct(
		int $a_id,
		string $a_title,
		float $a_latitude,
		float $a_longitude,
		\DateTimeImmutable $a_expiration_timestamp
	) {
		$this->id = $a_id;
		$this->title = $a_title;
		// In a real world example we might want to conduct more checks on latitude
		// and longitude here.
		$this->latitude = $a_latitude;
		$this->longitude = $a_longitude;
		$this->expiration_timestamp = $a_expiration_timestamp;
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getTitle() : string
	{
		return $this->title;
	}

	public function getLatitude() : float
	{
		return $this->latitude;
	}

	public function getLongitude() : float
	{
		return $this->longitude;
	}

	public function getExpirationAsTimestamp() : float
	{
		return $this->expiration_timestamp->getTimestamp();
	}

	public function getExpirationAsImmutableDateTime() : \DateTimeImmutable
	{
		return $this->expiration_timestamp;
	}
}
