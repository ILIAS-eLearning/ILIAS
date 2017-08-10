package com.yahoo.astra.layout
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	import com.yahoo.astra.layout.modes.BoxLayout;
	import com.yahoo.astra.layout.modes.ILayoutMode;
	import com.yahoo.astra.utils.NumberUtil;
	
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.geom.Rectangle;

	//--------------------------------------
	//  Events
	//--------------------------------------
	
	/**
	 *  Dispatched when this container's layout changes.
	 *
	 *  @eventType com.yahoo.astra.layout.events.LayoutEvent.LAYOUT_CHANGE
	 */
	[Event(name="layoutChange", type="com.yahoo.astra.layout.events.LayoutEvent")]

	/**
	 * Children of this display object are subject to being positioned, and
	 * possibly resized, based on a specified layout algorithm. LayoutContainer
	 * integrates with LayoutManager to refresh its the layout of its children
	 * when properties on the container itself change or when one of its
	 * children dispatches a registered invalidating event. This is the default
	 * implementation of ILayoutContainer.
	 * 
	 * @example The following code demonstrates the usage of LayoutContainer:
	 * <listing version="3.0">
	 * // create an instance of a layout mode
	 * var mode:ILayoutMode = new BoxLayout();
	 * mode.direction = "horizontal";
	 * mode.horizontalGap = 10;
	 * 
	 * // one may pass the mode to the constructor or the layoutMode property.
	 * // note: by default, a LayoutContainer will automatically determine
	 * // its size based on its content.
	 * var container:LayoutContainer = new LayoutContainer( mode );
	 * this.addChild(container);
	 * 
	 * for( var i:int = 0; i < 5; i++ )
	 * {
	 *     var square:Shape = new Shape();
	 *     square.graphics.beginFill(0xcccccc);
	 *     square.graphics.drawRect(0, 0, 25, 25);
	 *     square.graphics.endFill();
	 *     container.addChild(square);
	 * }
	 * </listing>
	 * 
	 * <p><strong>Important Note:</strong> LayoutContainer leaves certain
	 * functionality to the implementor to complete. No scrollbars or other user
	 * interface controls will appear when the contents are larger than the
	 * LayoutContainer's dimensions.</p>
	 * 
	 * <p>This limitation is deliberate and by design. The philosophy behind
	 * this choice centers on allowing an ActionScript developer to use these
	 * classes as a basis for implementing layout controls for nearly any user
	 * interface library available for Flash Player.</p>
	 * 
	 * <p>For a reference implementation of full-featured UI controls that
	 * implement masking and scrolling, please take a look at the Layout
	 * Containers available in the <a href="http://developer.yahoo.com/flash/astra-flash/">Yahoo! Astra Components for Flash CS3</a>.</p> 
	 * 
	 * @see LayoutManager
	 * @see ILayoutContainer
	 * @see modes/package-detail.html Available Layout Modes (com.yahoo.astra.layout.modes)
	 * @author Josh Tynjala
	 */
	public class LayoutContainer extends Sprite implements ILayoutContainer
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Flag indicating whether we are in render mode.
		 */
		public static var isRendering:Boolean = false;
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 * 
		 * @param mode		The ILayoutMode implementation to use.
		 */
		public function LayoutContainer(mode:ILayoutMode = null)
		{
			super();
			this.scrollRect = new Rectangle();
			this.layoutMode = mode;
			this.addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Storage for the contentWidth property.
		 */
		protected var _contentWidth:Number = 0;
		
		/**
		 * @inheritDoc
		 */
		public function get contentWidth():Number
		{
			return this._contentWidth;
		}
		
		/**
		 * @private
		 * Storage for the contentHeight property.
		 */
		protected var _contentHeight:Number = 0;
		
		/**
		 * @inheritDoc
		 */
		public function get contentHeight():Number
		{
			return this._contentHeight;
		}
		
		/**
		 * @private
		 * Storage for width values explicitly set by a developer. 
		 */
		protected var explicitWidth:Number = NaN;
		
		/**
		 * @private
		 */
		override public function get width():Number
		{
			if(!isNaN(this.explicitWidth))
			{
				return this.explicitWidth;
			}
			return this.contentWidth;
		}
		
		/**
		 * @private
		 */
		override public function set width(value:Number):void
		{
			if(this.explicitWidth != value)
			{
				this.explicitWidth = value;
				this.invalidateLayout();
			}
		}
		
		/**
		 * @private
		 * Storage for height values explicitly set by a developer. 
		 */
		protected var explicitHeight:Number = NaN;
		
		/**
		 * @private
		 */
		override public function get height():Number
		{
			if(!isNaN(this.explicitHeight))
			{
				return this.explicitHeight;
			}
			return this.contentHeight;
		}
		
		/**
		 * @private
		 */
		override public function set height(value:Number):void
		{
			if(this.explicitHeight != value)
			{
				this.explicitHeight = value;
				this.invalidateLayout();
			}
		}
		
		/**
		 * @private
		 * Storage for the layoutMode property.
		 */
		private var _layoutMode:ILayoutMode = new BoxLayout();
		
		/**
		 * @inheritDoc
		 */
		public function get layoutMode():ILayoutMode
		{
			return this._layoutMode;
		}
		
		/**
		 * @private
		 */
		public function set layoutMode(value:ILayoutMode):void
		{
			if(this._layoutMode)
			{
				this._layoutMode.removeEventListener(LayoutEvent.LAYOUT_CHANGE, layoutModeChangeHandler);
			}
			this._layoutMode = value;
			if(this._layoutMode)
			{
				this._layoutMode.addEventListener(LayoutEvent.LAYOUT_CHANGE, layoutModeChangeHandler, false, 0, true);
			}
			this.invalidateLayout();
		}
		
		/**
		 * @private
		 * Storage for the autoMask property.
		 */
		private var _autoMask:Boolean = true;
		
		/**
		 * If true, the conent will automatically update the scrollRect to fit
		 * the dimensions. Uses explicit dimensions if width or height is set by
		 * the developer. Otherwise, uses the content dimensions. If false, it
		 * is up to the implementor to set the mask or scrollRect.
		 */
		public function get autoMask():Boolean
		{
			return this._autoMask;
		}
		
		/**
		 * @private
		 */
		public function set autoMask(value:Boolean):void
		{
			this._autoMask = value;
			if(this._autoMask)
			{
				this.scrollRect = new Rectangle(0, 0, this.width, this.height);
			}
			else
			{
				this.scrollRect = null;
			}
		}
		
		/**
		 * @private
		 * Flag indicating if the layout is invalid.
		 */
		protected var invalid:Boolean = false;
		
		/**
		 * @private
		 * Flag indicating if the LayoutContainer is currently validating.
		 */
		protected var isValidating:Boolean = false;
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override public function addChild(child:DisplayObject):DisplayObject
		{
			child = super.addChild(child);
			if(child)
			{
				LayoutManager.registerContainerChild(child);
				this.invalidateLayout();
			}
			return child;
		}
		
		/**
		 * @private
		 */
		override public function addChildAt(child:DisplayObject, index:int):DisplayObject
		{
			child = super.addChildAt(child, index);
			if(child)
			{
				LayoutManager.registerContainerChild(child);
				this.invalidateLayout();
			}
			return child;
		}
		
		/**
		 * @private
		 */
		override public function removeChild(child:DisplayObject):DisplayObject
		{
			child = super.removeChild(child);
			if(child)
			{
				LayoutManager.unregisterContainerChild(child);
				this.invalidateLayout();
			}
			return child;
		}
		
		/**
		 * @private
		 */
		override public function removeChildAt(index:int):DisplayObject
		{
			var child:DisplayObject = super.removeChildAt(index);
			if(child)
			{
				LayoutManager.unregisterContainerChild(child);
				this.invalidateLayout();
			}
			return child;
		}
		
		/**
		 * @inheritDoc
		 */
		public function invalidateLayout():void
		{
			//if we're validating, then this change is caused
			//by an invalidating event from a child, and we can safely ignore it
			if(this.isValidating)
			{
				return;
			}
			
			if(isRendering)
			{
				//force validation during the render phase. performance hit should be minimal.
				this.validateLayout();
			}
			if(!this.invalid && this.stage)
			{
				this.invalid = true;
				this.stage.addEventListener(Event.ENTER_FRAME, renderHandler);
			}
		}
		
		/**
		 * @inheritDoc
		 */
		public function validateLayout():void
		{
			this.isValidating = true;
			this.layout();
			this.isValidating = false;
			this.invalid = false;
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 * Updates the layout algorithm and recalculates the content dimensions.
		 */
		protected function layout():void
		{
			var oldWidth:Number = this.contentWidth;
			var oldHeight:Number = this.contentHeight;
			
			this._contentWidth = Number.POSITIVE_INFINITY;
			this._contentHeight = Number.POSITIVE_INFINITY;
			
			//let the layout mode do all the work (strategy pattern)
			var bounds:Rectangle = new Rectangle();
			if(this.layoutMode)
			{
				var children:Array = [];
				var childCount:int = this.numChildren;
				for(var i:int = 0; i < childCount; i++)
				{
					children.push(this.getChildAt(i));
				}
				//width and height return the explicit values if available
				//otherwise they return the content width and height values
				bounds = this.layoutMode.layoutObjects(children, new Rectangle(0, 0, this.width, this.height));
			}
			
			this._contentWidth = bounds.x + bounds.width;
			this._contentHeight = bounds.y + bounds.height;
			
			if(this.autoMask)
			{
				var scrollRect:Rectangle = this.scrollRect;
				scrollRect.width = this.width;
				scrollRect.height = this.height;
				this.scrollRect = scrollRect;
			}
			
			if(!NumberUtil.fuzzyEquals(this.contentWidth, oldWidth) || !NumberUtil.fuzzyEquals(this.contentHeight, oldHeight))
			{
				this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
			}
		}
		
	//--------------------------------------
	//  Private Event Handlers
	//--------------------------------------
	
		/**
		 * @private
		 * Invalidates when the container is added to the stage.
		 */
		private function addedToStageHandler(event:Event):void
		{
			this.invalidateLayout();
		}
		
		/**
		 * @private
		 * Invalidates the layout if the layoutMode says that it is invalid.
		 */
		private function layoutModeChangeHandler(event:LayoutEvent):void
		{
			this.invalidateLayout();
		}
		
		/**
		 * @private
		 * Validates the layout on the next frame after invalidation.
		 */
		private function renderHandler(event:Event):void
		{
			isRendering = true;
			event.target.removeEventListener(Event.ENTER_FRAME, renderHandler);
			this.validateLayout();
			isRendering = false;
		}
	}
}