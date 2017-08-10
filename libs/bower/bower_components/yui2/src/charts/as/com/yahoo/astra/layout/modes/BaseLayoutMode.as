package com.yahoo.astra.layout.modes
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	
	import flash.display.DisplayObject;
	import flash.events.EventDispatcher;
	import flash.geom.Rectangle;

	/**
	 * Some basic properties shared by multiple ILayoutMode implementations.
	 * Should be treated as an abstract class that is meant to be subclassed.
	 * 
	 * @author Josh Tynjala
	 * @see ILayoutMode
	 * @see com.yahoo.astra.layout.ILayoutContainer
	 */
	public class BaseLayoutMode extends EventDispatcher implements IAdvancedLayoutMode
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function BaseLayoutMode()
		{
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Storage for the paddingLeft property.
		 */
		private var _paddingLeft:Number = 0;
		
		/**
		 * The number of pixels displayed at the left of the target's children.
		 */
		public function get paddingLeft():Number
		{
			return this._paddingLeft;
		}
		
		/**
		 * @private
		 */
		public function set paddingLeft(value:Number):void
		{
			if(this._paddingLeft != value)
			{
				this._paddingLeft = value;
				this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
			}
		}
		
		/**
		 * @private
		 * Storage for the paddingRight property.
		 */
		private var _paddingRight:Number = 0;
		
		/**
		 * The number of pixels displayed at the right of the target's children.
		 */
		public function get paddingRight():Number
		{
			return this._paddingRight;
		}
		
		/**
		 * @private
		 */
		public function set paddingRight(value:Number):void
		{
			if(this._paddingRight != value)
			{
				this._paddingRight = value;
				this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
			}
		}
		
		/**
		 * @private
		 * Storage for the paddingTop property.
		 */
		private var _paddingTop:Number = 0;
		
		/**
		 * The number of pixels displayed at the top of the target's children.
		 */
		public function get paddingTop():Number
		{
			return this._paddingTop;
		}
		
		/**
		 * @private
		 */
		public function set paddingTop(value:Number):void
		{
			if(this._paddingTop != value)
			{
				this._paddingTop = value;
				this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
			}
		}
		
		/**
		 * @private
		 * Storage for the paddingBottom property.
		 */
		private var _paddingBottom:Number = 0;
		
		/**
		 * The number of pixels displayed at the bottom of the target's children.
		 */
		public function get paddingBottom():Number
		{
			return this._paddingBottom;
		}
		
		/**
		 * @private
		 */
		public function set paddingBottom(value:Number):void
		{
			if(this._paddingBottom != value)
			{
				this._paddingBottom = value;
				this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
			}
		}
		
		/**
		 * @private
		 * Storage for the clients with advanced configuration options.
		 */
		protected var clients:Array = [];
		
		/**
		 * @private
		 * Storage for the advanced configuration options. Indicies
		 * correspond to the indices in the clients Array.
		 */
		protected var configurations:Array = [];
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------

		/**
		 * @inheritDoc
		 */
		public function addClient(target:DisplayObject, configuration:Object = null):void
		{
			if(!target)
			{
				throw new ArgumentError("Target must be a DisplayObject. Received " + target + ".");
			}
			
			configuration = configuration ? configuration : {};
			var generatedConfig:Object = this.newConfiguration();
			for(var prop:String in configuration)
			{
				generatedConfig[prop] = configuration[prop];
			}
			
			//reuse an existing client if we're re-adding it
			var index:int = this.clients.indexOf(target);
			if(index < 0)
			{
				index = this.clients.length;
				this.clients[index] = target;
			}
			this.configurations[index] = generatedConfig;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @inheritDoc
		 */
		public function removeClient(target:DisplayObject):void
		{
			var index:int = this.clients.indexOf(target);
			if(index < 0)
			{
				throw new ArgumentError("Cannot call removeClient() with a DisplayObject that is not a client.");
			}
			
			this.clients.splice(index, 1);
			this.configurations.splice(index, 1);
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @inheritDoc
		 */
		public function hasClient(target:DisplayObject):Boolean
		{
			return this.clients.indexOf(target) >= 0;
		}
		
		/**
		 * @inheritDoc
		 */
		public function layoutObjects(displayObjects:Array, bounds:Rectangle):Rectangle
		{
			//to be overridden
			throw new Error("Method BaseLayoutMode.layoutChildren() must be overridden!");
			return new Rectangle();
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 * Ensures that every child of the target has a configuration and creates a list of children
		 * that are included in the layout.
		 */
		protected function configureChildren(targets:Array):Array
		{	
			var childrenInLayout:Array = [];
			var childCount:int = targets.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(targets[i]);
				
				var index:int = this.clients.indexOf(child);
				if(index < 0)
				{
					//set up a default configuration if the child hasn't been added as a client
					index = this.clients.length;
					this.clients.push(child);
					this.configurations.push(this.newConfiguration());
				}
				
				//only return children that have includeInLayout specified
				var config:Object = this.configurations[index];
				if(config.includeInLayout)
				{
					childrenInLayout.push(child);
				}
			}
			return childrenInLayout;
		}

		/**
		 * @private
		 * The default configuration properties for a client of the layout mode.
		 */
		protected function newConfiguration():Object
		{
			return { includeInLayout: true };
		}
	}
}