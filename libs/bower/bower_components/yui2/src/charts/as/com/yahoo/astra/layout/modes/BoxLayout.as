package com.yahoo.astra.layout.modes
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	import com.yahoo.astra.utils.DisplayObjectUtil;
	
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.geom.Rectangle;

	/**
	 * Arranges a DisplayObjectContainer's children in a single column or row.
	 * 
	 * @example The following code configures a BoxLayout instance and passes it to a container:
	 * <listing version="3.0">
	 * var box:BoxLayout = new BoxLayout();
	 * box.direction = "vertical";
	 * box.verticalGap = 4;
	 * box.verticalAlign = VerticalAlignment.MIDDLE;
	 * 
	 * var container:LayoutContainer = new LayoutContainer();
	 * container.layoutMode = box;
	 * this.addChild( container );
	 * </listing>
	 * 
	 * <p><strong>Advanced Client Options:</strong></p>
	 * <p>Optional client configuration parameters allow a developer to specify
	 * behaviors for individual children of the target container. To set these
	 * advanced options, one must call <code>addClient()</code> on the BoxLayout
	 * instance and pass the child to configure along with an object specifying
	 * the configuration parameters. Several client parameters are available to
	 * the BoxLayout algorithm:</p>
	 * 
	 * <dl>
	 * 	<dt><strong><code>percentWidth</code></strong> : Number</dt>
	 * 		<dd>The target's width will be updated based on a percentage of the width specified in the layout bounds.</dd>
	 * 	<dt><strong><code>percentHeight</code></strong> : Number</dt>
	 * 		<dd>The target's width will be updated based on a percentage of the width specified in the layout bounds.</dd>
	 * 	<dt><strong><code>minWidth</code></strong> : Number</dt>
	 * 		<dd>The minimum width value to allow when resizing. The default value is <code>0</code>.</dd>
	 * 	<dt><strong><code>minHeight</code></strong> : Number</dt>
	 * 		<dd>The minimum height value to allow when resizing. The default value is <code>0</code>.</dd>
	 * 	<dt><strong><code>maxWidth</code></strong> : Number</dt>
	 * 		<dd>The maximum width value to allow when resizing. The default value is <code>10000</code>.</dd>
	 * 	<dt><strong><code>maxHeight</code></strong> : Number</dt>
	 * 		<dd>The maximum height value to allow when resizing. The default value is <code>10000</code>.</dd>
	 * 	<dt><strong><code>includeInLayout</code></strong> : Boolean</dt>
	 * 		<dd>If <code>false</code>, the target will not be included in layout calculations. The default value is <code>true</code>.</dd>
	 * </dl>
	 * 
	 * @example The following code adds multiple clients to a BoxLayout instance:
	 * <listing version="3.0">
	 * var box:BoxLayout = new BoxLayout();
	 * box.direction = "vertical";
	 * box.addClient( headerSprite, { percentWidth: 100 } );
	 * box.addClient( contentSprite,
	 * {
	 *     percentWidth: 100,
	 *     percentHeight: 100,
	 *     minWidth: 640,
	 *     minHeight: 480
	 * });
	 * box.addClient( footerSprite, { percentWidth: 100 } );
	 * 
	 * var container:LayoutContainer = new LayoutContainer( box );
	 * container.width = 1024;
	 * container.height = 768;
	 * container.addChild( headerSprite );
	 * container.addChild( contentSprite );
	 * container.addChild( footerSprite );
	 * this.addChild( container );
	 * </listing>
	 * 
	 * @author Josh Tynjala
	 * @see com.yahoo.astra.layout.LayoutContainer
	 */
	public class BoxLayout extends BaseLayoutMode implements IAdvancedLayoutMode
	{
		
	//--------------------------------------
	//  Static Properties
	//--------------------------------------
		
		/**
		 * @private
		 * The default maximum number of pixels to calculate width sizing.
		 */
		private static const DEFAULT_MAX_WIDTH:Number = 10000;
		
		/**
		 * @private
		 * The default maximum number of pixels to calculate height sizing.
		 */
		private static const DEFAULT_MAX_HEIGHT:Number = 10000;
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function BoxLayout()
		{
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Storage for the direction property.
		 */
		private var _direction:String = "horizontal";
		
		/**
		 * The direction in which children of the target are laid out. Valid
		 * direction values include <code>"vertical"</code> or <code>"horizontal"</code>.
		 */
		public function get direction():String
		{
			return this._direction;
		}
		
		/**
		 * @private
		 */
		public function set direction(value:String):void
		{
			this._direction = value;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @private
		 * Storage for the verticalGap property.
		 */
		private var _verticalGap:Number = 0;
		
		/**
		 * The number of pixels appearing between the target's children
		 * vertically.
		 */
		public function get verticalGap():Number
		{
			return this._verticalGap;
		}
		
		/**
		 * @private
		 */
		public function set verticalGap(value:Number):void
		{
			this._verticalGap = value;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @private
		 * Storage for the horizontalGap property.
		 */
		private var _horizontalGap:Number = 0;
		
		/**
		 * The number of pixels appearing between the target's children
		 * horizontally.
		 */
		public function get horizontalGap():Number
		{
			return this._horizontalGap;
		}
		
		/**
		 * @private
		 */
		public function set horizontalGap(value:Number):void
		{
			this._horizontalGap = value;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @private
		 * Storage for the verticalAlign property.
		 */
		private var _verticalAlign:String = "top";
		
		/**
		 * The vertical alignment of children displayed in the target.
		 * 
		 * @see VerticalAlignment
		 */
		public function get verticalAlign():String
		{
			return this._verticalAlign;
		}
		
		/**
		 * @private
		 */
		public function set verticalAlign(value:String):void
		{
			this._verticalAlign = value;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @private
		 * Storage for the horizontalAlign property.
		 */
		private var _horizontalAlign:String = "left";
		
		/**
		 * The horizontal alignment of children displayed in the target.
		 * 
		 * @see HorizontalAlignment
		 */
		public function get horizontalAlign():String
		{
			return this._horizontalAlign;
		}
		
		/**
		 * @private
		 */
		public function set horizontalAlign(value:String):void
		{
			this._horizontalAlign = value;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @private
		 * The maximum width value from among the current target's children.
		 */
		protected var maxChildWidth:Number;
		
		/**
		 * @private
		 * The maximum height value from among the current target's children.
		 */
		protected var maxChildHeight:Number;
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @inheritDoc
		 */
		override public function layoutObjects(displayObjects:Array, bounds:Rectangle):Rectangle
		{
			var childrenInLayout:Array = this.configureChildren(displayObjects);
			
			//determine the available horizontal space
			var hSpaceForChildren:Number = bounds.width - this.paddingLeft - this.paddingRight;
			if((hSpaceForChildren == Infinity)||(hSpaceForChildren >9000))
			{
				hSpaceForChildren = DEFAULT_MAX_WIDTH;
			}
			
			//determine the available vertical space
			var vSpaceForChildren:Number = bounds.height - this.paddingTop - this.paddingBottom;
			if((vSpaceForChildren == Infinity)||(vSpaceForChildren >9000))
			{
				vSpaceForChildren = DEFAULT_MAX_HEIGHT;
			}
			
			//resize the children based on the available space and the specified percentage width and height values.	
			if(this.direction == "vertical")
			{
				vSpaceForChildren -= (this.verticalGap * (childrenInLayout.length - 1));
				PercentageSizeUtil.flexChildHeightsProportionally(this.clients, this.configurations, hSpaceForChildren, vSpaceForChildren); 
			}
			else
			{
				hSpaceForChildren -= (this.horizontalGap * (childrenInLayout.length - 1));
				PercentageSizeUtil.flexChildWidthsProportionally(this.clients, this.configurations, hSpaceForChildren, vSpaceForChildren); 
			}
			
			this.maxChildWidth = 0;
			this.maxChildHeight = 0;
			var childCount:int = childrenInLayout.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(childrenInLayout[i]);
				
				//measure the child's width
				this.maxChildWidth = Math.max(this.maxChildWidth, child.width);
				this.maxChildHeight = Math.max(this.maxChildHeight, child.height);
			}
			
			if(this.direction == "vertical")
			{
				this.layoutChildrenVertically(childrenInLayout, bounds);
			}
			else
			{
				this.layoutChildrenHorizontally(childrenInLayout, bounds);
			}		
			
			bounds = LayoutModeUtil.calculateChildBounds(childrenInLayout);
			bounds.width += this.paddingRight;
			bounds.height += this.paddingBottom;
			return bounds;
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 * Positions the children when direction is vertical.
		 */
		protected function layoutChildrenVertically(children:Array, bounds:Rectangle):void
		{		
			var maxXPosition:Number = bounds.width;
			if(maxXPosition == Number.POSITIVE_INFINITY)
			{
				maxXPosition = this.maxChildWidth;
			}
			maxXPosition -= (this.paddingLeft + this.paddingRight);
			
			var xPosition:Number = bounds.x + this.paddingLeft; 
			var yPosition:Number = bounds.y + this.paddingTop;
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				child.x = xPosition;
				child.y = yPosition;
				
				DisplayObjectUtil.align(child, new Rectangle(child.x, child.y, maxXPosition, child.height), this.horizontalAlign, null);
				yPosition += child.height + this.verticalGap;
			}
			
			//special case: if the combined height of the children
			//is less than the total height specified in the bounds,
			//then we can align vertically as well!
			var totalHeight:Number = yPosition - this.verticalGap - bounds.y + this.paddingBottom;
			if(totalHeight < bounds.height)
			{
				var middleStart:Number = (bounds.height - totalHeight) / 2;
				var rightStart:Number = bounds.height - totalHeight - bounds.y;
				rightStart = (rightStart == Infinity)?DEFAULT_MAX_HEIGHT:rightStart;				
				
				for(i = 0; i < childCount; i++)
				{
					child = DisplayObject(children[i]);
					
					switch(this.verticalAlign)
					{
						case "middle":
							child.y += middleStart;
							break;
						case "bottom":
							child.y += rightStart;
							break;
					}
				}
			}
		}
		
		/**
		 * @private
		 * Positions the children when direction is horizontal.
		 */
		protected function layoutChildrenHorizontally(children:Array, bounds:Rectangle):void
		{	
			var maxYPosition:Number = bounds.height;
			if(maxYPosition == Number.POSITIVE_INFINITY)
			{
				maxYPosition = this.maxChildHeight;
			}
			maxYPosition -= (this.paddingBottom + this.paddingTop);
			
			var xPosition:Number = bounds.x + this.paddingLeft;
			var yPosition:Number = bounds.y + this.paddingTop
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				
				var child:DisplayObject = DisplayObject(children[i]);
				
				child.x = xPosition;
				child.y = yPosition;
				
				DisplayObjectUtil.align(child, new Rectangle(child.x, child.y, child.width, maxYPosition), null, this.verticalAlign);
				
				
				xPosition += child.width + this.horizontalGap;
			}
			
			//special case: if the combined width of the children
			//is less than the total width specified in the bounds,
			//then we can align horizontally as well!
			var totalWidth:Number = xPosition - this.horizontalGap - bounds.x + this.paddingRight;

			if(totalWidth < bounds.width)
			{
				var middleStart:Number = (bounds.width - totalWidth) / 2;
				var rightStart:Number = bounds.width - totalWidth;
				rightStart = (rightStart == Infinity)?DEFAULT_MAX_WIDTH:rightStart;
				
				for(i = 0; i < childCount; i++)
				{
					child = DisplayObject(children[i]);
					
					switch(this.horizontalAlign)
					{
						case "center":
							child.x += middleStart;
							break;
						case "right":
							child.x += rightStart;
							break;
					}
				}
			}
		}

		/**
		 * @private
		 * Creates the default configuration for this layout mode.
		 */
		override protected function newConfiguration():Object
		{
			return {
				includeInLayout: true,
				minWidth: 0,
				maxWidth: 10000,
				minHeight: 0,
				maxHeight: 10000,
				percentWidth: NaN,
				percentHeight: NaN
			};
		}
	}
}