package com.yahoo.astra.layout.modes
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	import com.yahoo.astra.utils.DisplayObjectUtil;
	
	import flash.display.DisplayObject;
	import flash.geom.Rectangle;

	/**
	 * Arranges a DisplayObjectContainer's children using a tiling algorithm.
	 * All tiles are the same size and tile dimensions are determined from the
	 * maximum width or height values of the available children.
	 * 
	 * @example The following code configures a TileLayout instance and passes it to a container:
	 * <listing version="3.0">
	 * var tile:TileLayout = new TileLayout();
	 * tile.direction = "horizontal";
	 * tile.horizontalGap = 1;
	 * tile.verticalGap = 4;
	 * tile.horizontalAlign = HorizontalAlignment.CENTER;
	 * tile.verticalAlign = VerticalAlignment.MIDDLE;
	 * 
	 * var container:LayoutContainer = new LayoutContainer();
	 * container.layoutMode = tile;
	 * this.addChild( container );
	 * </listing>
	 * 
	 * <p><strong>Advanced Client Options:</strong></p>
	 * <p>Optional client configuration parameters allow a developer to specify
	 * behaviors for individual children of the target container. To set these
	 * advanced options, one must call <code>addClient()</code> on the TileLayout
	 * instance and pass the child to configure along with an object specifying
	 * the configuration parameters. The following client parameters are available to
	 * the TileLayout algorithm:</p>
	 * 
	 * <dl>
	 * 	<dt><strong><code>includeInLayout</code></strong> : Boolean</dt>
	 * 		<dd>If <code>false</code>, the target will not be included in layout calculations. The default value is <code>true</code>.</dd>
	 * </dl>
	 * 
	 * @author Josh Tynjala
	 * @see com.yahoo.astra.layout.LayoutContainer
	 */
	public class TileLayout extends BaseLayoutMode implements IAdvancedLayoutMode
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function TileLayout()
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
		 * The direction in which children of the target are laid out. Once
		 * the edge of the container is reached, the children will begin
		 * appearing on the next row or column. Valid direction values include
		 * <code>"vertical"</code> or <code>"horizontal"</code>.
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
		 * The children of the target may be aligned vertically within their
		 * respective tiles.
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
		 * The children of the target may be aligned horizontally within their
		 * respective tiles.
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
		 * Storage for the tileWidth property.
		 */
		private var _tileWidth:Number = NaN;
		
		/**
		 * The width of tiles displayed in the target. If NaN, the tile width
		 * will be calculated based on the maximum width among the target's children.
		 */
		public function get tileWidth():Number
		{
			return this._tileWidth;
		}
		
		/**
		 * @private
		 */
		public function set tileWidth(value:Number):void
		{
			this._tileWidth = value;
			this.dispatchEvent(new LayoutEvent(LayoutEvent.LAYOUT_CHANGE));
		}
		
		/**
		 * @private
		 * Storage for the tileHeight property.
		 */
		private var _tileHeight:Number = NaN;
		
		/**
		 * The height of tiles displayed in the target. If NaN, the tile height
		 * will be calculated based on the maximum height among the target's children.
		 */
		public function get tileHeight():Number
		{
			return this._tileHeight;
		}
		
		/**
		 * @private
		 */
		public function set tileHeight(value:Number):void
		{
			this._tileHeight = value;
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
			
			this.maxChildWidth = this.maxChildHeight = 0;
			var childCount:int = displayObjects.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(displayObjects[i]);
				this.maxChildWidth = Math.max(this.maxChildWidth, child.width);
				this.maxChildHeight = Math.max(this.maxChildHeight, child.height);
			}
			
			if(!isNaN(this.tileWidth))
			{
				this.maxChildWidth = this.tileWidth;
			}
			
			if(!isNaN(this.tileHeight))
			{
				this.maxChildHeight = this.tileHeight;
			}
			
			if(this.direction == "vertical")
			{
				this.layoutChildrenVertically(childrenInLayout, bounds);
			}
			else
			{
				this.layoutChildrenHorizontally(childrenInLayout, bounds);
			}
			
			var bounds:Rectangle = LayoutModeUtil.calculateChildBounds(childrenInLayout);
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
			const START_Y:Number = bounds.y + this.paddingTop;
			var xPosition:Number = bounds.x + this.paddingLeft;
			var yPosition:Number = START_Y;
			
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				
				var endOfColumn:Number = yPosition + this.maxChildHeight + this.paddingBottom;
				if(endOfColumn - bounds.y >= bounds.height && yPosition != START_Y)
				{
					//next column if we're over the height,
					//but not if we're at yposition == START_Y
					yPosition = START_Y;
					xPosition += this.maxChildWidth + this.horizontalGap;
				}
				
				DisplayObjectUtil.align(child, new Rectangle(xPosition, yPosition, this.maxChildWidth, this.maxChildHeight), this.horizontalAlign, this.verticalAlign);
				
				yPosition += this.maxChildHeight + this.verticalGap;
			}
		}
		
		/**
		 * @private
		 * Positions the children when direction is horizontal.
		 */
		protected function layoutChildrenHorizontally(children:Array, bounds:Rectangle):void
		{
			const START_X:Number = bounds.x + this.paddingLeft;
			var xPosition:Number = START_X;
			var yPosition:Number = bounds.y + this.paddingTop;
			
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				
				var endOfRow:Number = xPosition + this.maxChildWidth + this.paddingRight;
				if(endOfRow - bounds.x >= bounds.width && xPosition != START_X)
				{
					//next row if we're over the width,
					//but not if we're at xposition == START_X
					xPosition = START_X;
					yPosition += this.maxChildHeight + this.verticalGap;
				}
				
				DisplayObjectUtil.align(child, new Rectangle(xPosition, yPosition, this.maxChildWidth, this.maxChildHeight), this.horizontalAlign, this.verticalAlign);
				
				xPosition += this.maxChildWidth + this.horizontalGap;
			}
		}
		
	}
}