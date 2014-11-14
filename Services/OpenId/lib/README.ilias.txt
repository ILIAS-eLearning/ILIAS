// php 5.4
// lib/Auth/OpenId/Consumer.php
   /**
     * @access private
     */
    function _discoverAndVerify($claimed_id, $to_match_endpoints)
    {
        // oidutil.log('Performing discovery on %s' % (claimed_id,))
        list($unused, $services) = call_user_func($this->discoverMethod,
                                                  $claimed_id,
												  $this->fetcher); // fixed php 5.4 compatability (removed @)
	}
	
	
// lib/Auth/Yadis/Manager.php on line 416
    function getNextService($discover_cb, $fetcher)
    {
        $manager = $this->getManager();
        if (!$manager || (!$manager->services)) {
            $this->destroyManager();

            list($yadis_url, $services) = call_user_func($discover_cb,
                                                         $this->url,
                                                         $fetcher);


