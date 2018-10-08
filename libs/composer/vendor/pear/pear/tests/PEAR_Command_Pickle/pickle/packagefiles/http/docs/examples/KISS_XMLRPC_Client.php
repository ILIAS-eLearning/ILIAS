<?php
class XmlRpcClient
{
	public $namespace;
	protected $request;

	public function __construct($url, $namespace = '')
	{
		$this->namespace = $namespace;
		$this->request = new HttpRequest($url, HTTP_METH_POST);
		$this->request->setContentType('text/xml');
	}

	public function setOptions($options = array())
	{
		return $this->request->setOptions($options);
	}

	public function addOptions($options)
	{
		return $this->request->addOptions($options);
	}

	public function __call($method, $params)
	{
		if ($this->namespace) {
			$method = $this->namespace .'.'. $method;
		}
		$this->request->setRawPostData(xmlrpc_encode_request($method, $params));
		$response = $this->request->send();
		if ($response->getResponseCode() != 200) {
			throw new Exception($response->getBody(), $response->getResponseCode());
		}
		return xmlrpc_decode($response->getBody(), 'utf-8');
	}
	
	public function getHistory()
	{
		return $this->request->getHistory();
	}
}

?>
