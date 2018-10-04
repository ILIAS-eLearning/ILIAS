<?php
class FeedAggregator
{
	public $directory;
	protected $feeds = array();

	public function __construct($directory = 'feeds')
	{
		$this->setDirectory($directory);
	}

	public function setDirectory($directory)
	{
		$this->directory = $directory;
		foreach (glob($this->directory .'/*.xml') as $feed) {
			$this->feeds[basename($feed, '.xml')] = filemtime($feed);
		}
	}

	public function url2name($url)
	{
		return preg_replace('/[^\w\.-]+/', '_', $url);
	}
	
	public function hasFeed($url)
	{
		return isset($this->feeds[$this->url2name($url)]);
	}

	public function addFeed($url)
	{
		$r = $this->setupRequest($url);
		$r->send();
		$this->handleResponse($r);
	}

	public function addFeeds($urls)
	{
		$pool = new HttpRequestPool;
		foreach ($urls as $url) {
			$pool->attach($r = $this->setupRequest($url));
		}
		$pool->send();

		foreach ($pool as $request) {
			$this->handleResponse($request);
		}
	}

	public function getFeed($url)
	{
		$this->addFeed($url);
		return $this->loadFeed($this->url2name($url));
	}

	public function getFeeds($urls)
	{
		$feeds = array();
		$this->addFeeds($urls);
		foreach ($urls as $url) {
			$feeds[] = $this->loadFeed($this->url2name($url));
		}
		return $feeds;
	}

	protected function saveFeed($file, $contents)
	{
		if (file_put_contents($this->directory .'/'. $file .'.xml', $contents)) {
			$this->feeds[$file] = time();
		} else {
			throw new Exception("Could not save feed contents to $file.xml");
		}
	}

	protected function loadFeed($file)
	{
		if (isset($this->feeds[$file])) {
			if ($data = file_get_contents($this->directory .'/'. $file .'.xml')) {
				return $data;
			} else {
				throw new Exception("Could not load feed contents from $file.xml");
			}
		} else {
			throw new Exception("Unknown feed/file $file.xml");
		}
	}

	protected function setupRequest($url)
	{
		$r = new HttpRequest($url);
		$r->setOptions(array('redirect' => true));

		$file = $this->url2name($url);

		if (isset($this->feeds[$file])) {
			$r->setOptions(array('lastmodified' => $this->feeds[$file]));
		}

		return $r;
	}

	protected function handleResponse(HttpRequest $r)
	{
		if ($r->getResponseCode() != 304) {
			if ($r->getResponseCode() != 200) {
				throw new Exception("Unexpected response code ". $r->getResponseCode());
			}
			if (!strlen($body = $r->getResponseBody())) {
				throw new Exception("Received empty feed from ". $r->getUrl());
			}
			$this->saveFeed($this->url2name($r->getUrl()), $body);
		}
	}
}
?>
