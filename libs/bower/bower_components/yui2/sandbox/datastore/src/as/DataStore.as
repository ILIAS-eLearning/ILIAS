package  
{
	import com.yahoo.yui.YUIAdapter;
	
	import flash.events.NetStatusEvent;
	import flash.external.ExternalInterface;
	import flash.net.SharedObject;
	import flash.net.SharedObjectFlushStatus;
	import flash.system.Security;
	import flash.system.SecurityPanel;

	//We set width/height is set here to be large enough to display the settings panel in Flash Player
	[SWF(width=215, height=138)]
	
	/**
	 * A wrapper for Flash SharedObjects to allow them to be used in YUI / JavaScript.
	 * 
	 * @author Alaric Cole
	 */
	public class DataStore extends YUIAdapter
	{
	    
	    //--------------------------------------------------------------------------
	    //
	    //  Properties
	    //
	    //--------------------------------------------------------------------------
	    
	    
		/**
	     * The Shared Object instance in which to store entries.
	     * @private
	     */
		private var _sharedObject:SharedObject;

		
		//--------------------------------------
		//  Constructor
		//--------------------------------------
		
		/**
		 * Creates a store, which can be used to set and get information on a
		 * user's local machine. This is similar to a browser cookie, except the 
		 * allowwed store is larger and can be shared across browsers.
		 * 
		 * @param localPath The path to append to the current domain, to help
		 * organize the location of the store on the local machine.  
		 * If the store is specific to one SWF file and this file
		 * will not be moved to another location, you can omit this parameter. 
		 * Omitting this parameter sets it to the default value, which is the 
		 * full path to the particular SWF.
		 * <p>If multiple SWF files need access to the same store, 
		 * or if the SWF file that creates a store will later be moved, 
		 * the value of this parameter affects how accessible the store will be.</p> 
		 * <p>For example, if you create a store with localPath set to the default value
		 * (the full path to the SWF file), no other SWF file can access that shared object. 
		 * If you later move the original SWF file to another location, 
		 * not even that SWF file can access the data already stored.</p>
		 * <p>To avoid inadvertently restricting access to a store, set this parameter. 
		 * The most permissive approach is to set localPath to <code>/ </code> (forward slash), 
		 * which makes the store available to all SWF files in the domain, 
		 * but increases the likelihood of name conflicts with other stores in the domain. 
		 * A more restrictive approach is to append localPath with folder names 
		 * that are in the full path to the SWF file. Note that not just any folder path
		 * can be placed here, but only those that are in the path of the SWF. 
		 * For instance, if the SWF is located at company.com/products/mail/mail.swf,
		 * the available options for localPath would be "/products/mail/", 
		 * "/products/", or "/".</p>
		 * 
		 */
		public function DataStore(localPath:String = null)
		{
			super();
			
			try
			{
				//check that page url is the same as the swf's url
				
				var currentURL:String = ExternalInterface.call("function(){return window.location.href;}");
				if(currentURL.indexOf("?") > -1)
				{
					currentURL = currentURL.slice(0,currentURL.indexOf("?"));
				

				}
			    
			    currentURL = currentURL.slice(0,currentURL.lastIndexOf("/"));
			    					
				var loadedURL:String = loaderInfo.loaderURL;
				if(loadedURL.indexOf("?") > -1)
				{
					loadedURL = loadedURL.slice(0,loadedURL.indexOf("?"));
				
					
				}
				loadedURL = loadedURL.slice(0,loadedURL.lastIndexOf("/"));
				
				var currentURL_ESC:String = unescape(currentURL) ;
				var loadedURL_ESC:String = unescape(loadedURL) 
				
				if(currentURL_ESC == loadedURL_ESC )
				{
					//valid
					//later on we may add the ability to set the localPath, but for now we're
					//going to use the path of the swf
 					_sharedObject = SharedObject.getLocal("DataStore", localPath);
					_sharedObject.addEventListener(NetStatusEvent.NET_STATUS, onNetStatus);
				}
				else 
				{	
					var evt:Object = {type: "error", message: "The domain of the page must match the SWF's domain.\nPage's URL: " +
						currentURL + "\n" + "SWF's URL: " + loadedURL};
						
					dispatchEventToJavaScript(evt);
				}
			}
			
			catch(e:Error)
			{
				dispatchEventToJavaScript(e);
			}
			
			
				
			//initializeComponent() will be called by yuiadapter
		}

		//--------------------------------------------------------------------------
		// 
		// Methods
		//
		//--------------------------------------------------------------------------

	   /**
	    * Saves data to local storage. It returns a String that can
		* be one of three values: "true" if the storage succeeded; "false" if the user
		* has denied storage on their machine.
		* <p>The size limit for the passed parameters is ~40Kb.</p>
		*  
	    * @param item The data to store
	    * @param location The name of the "cookie" or store 
		* @return Boolean Whether or not the save was successful
	    * 
	    */
	    public function setItem(item:Object, location:String):Boolean
	    {
	    	_sharedObject.data[location] = item;
	    	
	    	//write it immediately
	    	var result:Boolean = save();

	    	return result;
	    }

	    
	    /**
	    * Returns the data in local storage, if any.
	    * 
	    * @param location The name of the "cookie" or store
		* @return The data
	    * 
	    */
	    public function getItem(location:String):Object
	    {
	    	return _sharedObject.data[location];
	    }
	    
	   /**
	    * Removes the data in local storage, if any.
	    * 
	    * @param location The name of the "cookie" or store
		* @return Whether the remove was successful
	    * 
	    */
	    public function removeItem(location:String):Boolean
	    {
	    	_sharedObject.data[location] = null;
	    	
	    	var result:Boolean = save();
	    	
	    	return result;
	    }
	    
	   /**
	    * Removes all data in local storage for this domain.
	    * <p>Be careful when using this method, as it may 
	    * remove stored information that is used by other applications
	    * in this domain </p>
	    * 
		* @return Whether the clear was successful
	    * 
	    */
	    public function clear():Boolean
	    {
	    	_sharedObject.clear();
	    	var evt:Object = {type: "success"};
			dispatchEventToJavaScript(evt);
	    	return true;
	    }
	    
	    
	    /**
	     *  Gets the current size, in KB, of the amount of space taken by the current store.
	     * 
	     */
		public function calculateSize():uint
		{
			var sz:uint = _sharedObject.size;
			return sz;
		}
		
		/**
		* This method requests more storage if the amount is above 100KB. (e.g.,
		* if the <code>store()</code> method returns "pending".
		* The request dialog has to be displayed within the Flash player itself
		* so the SWF it is called from must be visible and at least 215px x 138px in size.
		* 
		* Since this is a "per domain" setting, you can
		* use this method on a SWF in a separate page, such as a settings page, 
		* if the width/height of the compiled SWF is not large enough 
		* to fit this dialog. 
		* 
		* @param value The size, in KB
		*/
		public function setSize(value:int):void
		{
			var status:String;
			
			status = _sharedObject.flush(value * 1024);
			//on error, attempt to resize div
		
		}

		/**
		 * Displays the settings dialog to allow the user to configure
		 * storage settings manually. If the SWF height and width are smaller than
		 * what is allowable to display the local settings panel,
		 * a message will be sent to JavaScript.
		 */
		public function displaySettings():void
		{
			var evt:Object;
			if( (stage.stageHeight >= 138) && (stage.stageWidth >= 215) )
			{
				evt = {type: "openDialog"};
				dispatchEventToJavaScript(evt);

				Security.showSettings(SecurityPanel.LOCAL_STORAGE);
			}
			else
			{
				
				evt = {type: "openExternalDialog", message: "The current size of the SWF is too small to display " + 
						"the settings panel."};
				dispatchEventToJavaScript(evt);
			}

		}
		
	
	    /**
	     * Gets the timestamp of the last store. This value is automatically set when 
	     * data is stored.
	     * @return A Date object
	     */
		public function getLastModified():Date
		{
			var lastDate:Date =  new Date(_sharedObject.data.lastModified);
			
			return lastDate;
			
		}
		
		
		//--------------------------------------------------------------------------
		// 
		// Overridden Methods
		//
		//--------------------------------------------------------------------------
	    /**
		 *  Initializes the component and enables communication with JavaScript
		 *  @private
	     *
	     */
		//
		override protected function initializeComponent():void 
		{

			super.initializeComponent();

			ExternalInterface.addCallback("setItem", setItem);
			ExternalInterface.addCallback("removeItem", removeItem);
			ExternalInterface.addCallback("clear", clear);
			ExternalInterface.addCallback("getItem", getItem);
			ExternalInterface.addCallback("setSize", setSize);
			ExternalInterface.addCallback("calculateSize", calculateSize);
			ExternalInterface.addCallback("getLastModified", getLastModified);
			ExternalInterface.addCallback("displaySettings", displaySettings);
		}
	
		//--------------------------------------
		//  Private Methods
		//--------------------------------------
		
		/**
		* Writes the store to disk. While this will be called by Flash
		* whenever the application is closed, calling it immediately after
		* new information allows that info to be instantly available.
		*
	    * @private
		*/
		protected function save():Boolean
	    {
	        //set the time modified
	        setTime(new Date().getTime());
	        var evt:Object;
	    	var result:String = _sharedObject.flush();
	    	
	    	//return status
	    	if(result == SharedObjectFlushStatus.FLUSHED)
	    	{
	    		evt = {type: "success"};
				dispatchEventToJavaScript(evt);
	    		return true;
	    	}
			if(result == SharedObjectFlushStatus.PENDING)
	    	{
	    		//let JS know theres going to be a dialog
	    		//trace("pending")
	    		evt = {type: "pending"};
				dispatchEventToJavaScript(evt);
	    		return false;
	    	} 
	    	
	    	else
	    	{
	    		evt = {type: "error"};
				dispatchEventToJavaScript(evt);
	    		return false;
	    		
	    	} 
	    	return false;
	    }
	    
	    /**
		* Sets the date modified for the store.
		* 
		* @param value The time to set.
	    * 
		*/
		protected function setTime(value:Number):void
		{
			_sharedObject.data.lastModified = value;
		}
	    
		/**
		* Called when a larger shared object size is requested
	    * 
		*/
		protected function onNetStatus(event:NetStatusEvent):void
		{
			
			var evt:Object;
			if(event.info.level =="error")
			{
				//user said "heck no" to the request for more storage
				evt = {type: "error", message:"User refused the request for more storage"};
				dispatchEventToJavaScript(evt);
			}
			else
			{
				//flush() is called again and resolved successfully
				evt = {type: "success"};
				dispatchEventToJavaScript(evt);
			}
			
		}

	}
}
