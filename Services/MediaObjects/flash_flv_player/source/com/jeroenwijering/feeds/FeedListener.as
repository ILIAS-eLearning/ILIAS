/**
* Interface for all objects that need real-time feed updates.
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.feeds.*;


interface com.jeroenwijering.feeds.FeedListener {


	/** invoked when the feed object has updated **/
	function onFeedUpdate();


}