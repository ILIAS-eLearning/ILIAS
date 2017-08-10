package com.yahoo.astra.layout.modes
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	import com.yahoo.astra.utils.DisplayObjectUtil;
	
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.geom.Rectangle;

	/**
	 * Arranges a DisplayObjectContainer's children using a flow algorithm. When
	 * a child is too large for a row or column, a new row or column is created.
	 * Similar to the flow of text in a document.
	 * 
	 * @example The following code configures a FlowLayout instance and passes it to a container:
	 * <listing version="3.0">
	 * var flow:FlowLayout = new FlowLayout();
	 * flow.direction = "horizontal";
	 * flow.horizontalGap = 1;
	 * flow.verticalGap = 4;
	 * flow.verticalAlign = VerticalAlignment.BOTTOM;
	 * 
	 * var container:LayoutContainer = new LayoutContainer();
	 * container.layoutMode = flow;
	 * this.addChild( container );
	 * </listing>
	 * 
	 * <p><strong>Advanced Client Options:</strong></p>
	 * <p>Optional client configuration parameters allow a developer to specify
	 * behaviors for individual children of the target container. To set these
	 * advanced options, one must call <code>addClient()</code> on the FlowLayout
	 * instance and pass the child to configure along with an object specifying
	 * the configuration parameters. The following client parameters are available to
	 * the FlowLayout algorithm:</p>
	 * 
	 * <dl>
	 * 	<dt><strong><code>includeInLayout</code></strong> : Boolean</dt>
	 * 		<dd>If <code>false</code>, the target will not be included in layout calculations. The default value is <code>true</code>.</dd>
	 * </dl>
	 * 
	 * @author Josh Tynjala
	 * @see com.yahoo.astra.layout.LayoutContainer
	 */
	public class FlowLayout extends BaseLayoutMode implements IAdvancedLayoutMode
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function FlowLayout()
		{
			super();
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
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------

		/**
		 * @inheritDoc
		 */
		override public function layoutObjects(displayObjects:Array, bounds:Rectangle):Rectangle
		{
			var childrenInLayout:Array = this.configureChildren(displayObjects);
			
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
			const START_Y:Number = bounds.y + this.paddingTop;
			var xPosition:Number = bounds.x + this.paddingLeft;
			var yPosition:Number = START_Y;
			var maxChildWidth:Number = 0;
			var column:Array = [];;
			
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				
				//next column if we're over the height, but not if we're at yposition == bounds.y
				var endOfColumn:Number = yPosition + child.height + this.paddingBottom;				
				if(endOfColumn - bounds.y >= bounds.height && yPosition != START_Y)
				{
					//update alignment
					this.alignColumn(column, maxChildWidth, bounds);
					
					xPosition += maxChildWidth + this.horizontalGap;
					yPosition = START_Y;
					maxChildWidth = 0;
					column = [];
				}

				child.x = xPosition;
				child.y = yPosition;
				column.push(child);
				maxChildWidth = Math.max(maxChildWidth, child.width);
				yPosition += child.height + this.verticalGap;
			}
			if(column.length < childCount) this.alignColumn(column, maxChildWidth, bounds);
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
			var maxChildHeight:Number = 0;
			var row:Array = [];
			
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				var childWidth:Number = child.width;
				var childHeight:Number = child.height;
				
				//next row if we're over the width, but not if we're at xposition == bounds.x
				var endOfRow:Number = xPosition + child.width + this.paddingRight;
				if(endOfRow - bounds.x >= bounds.width && xPosition != START_X)
				{
					//update alignment
					this.alignRow(row, maxChildHeight, bounds);
					
					xPosition = START_X;
					yPosition += maxChildHeight + this.verticalGap;
					maxChildHeight = 0;
					row = [];
				}
				child.x = xPosition;
				child.y = yPosition;
				row.push(child);
				maxChildHeight = Math.max(maxChildHeight, childHeight);
				xPosition += child.width + this.horizontalGap;
			}
			if(row.length < childCount) this.alignRow(row, maxChildHeight, bounds);
		}
		
		/**
		 * @private
		 * Repositions a column of children based on the alignment values.
		 */
		protected function alignColumn(column:Array, maxChildWidth:Number, bounds:Rectangle):void
		{
			if(column.length == 0)
			{
				return;
			}

			var lastChild:DisplayObject = DisplayObject(column[column.length - 1]);
			var columnHeight:Number = (lastChild.y + lastChild.height) - bounds.y + this.paddingBottom;
			var difference:Number = bounds.height - columnHeight;
			
			var columnCount:int = column.length;
			for(var i:int = 0; i < columnCount; i++)
			{
				var child:DisplayObject = DisplayObject(column[i]);
				DisplayObjectUtil.align(child, new Rectangle(child.x, child.y, maxChildWidth, child.height), this.horizontalAlign, null);
				
				switch(this.verticalAlign)
				{
					case "middle":
						child.y += difference / 2;
						break;
					case "bottom":
						child.y += difference;
						break;
				}
			}
		}
		
		/**
		 * @private
		 * Repositions a row of children based on the alignment values.
		 */
		protected function alignRow(row:Array, maxChildHeight:Number, bounds:Rectangle):void
		{
			if(row.length == 0)
			{
				return;
			}
			
			var lastChild:DisplayObject = DisplayObject(row[row.length - 1]);
			var rowWidth:Number = (lastChild.x + lastChild.width) - bounds.x + this.paddingRight;
			var difference:Number = bounds.width - rowWidth;
			
			var rowCount:int = row.length;
			for(var i:int = 0; i < rowCount; i++)
			{
				var child:DisplayObject = DisplayObject(row[i]);
				DisplayObjectUtil.align(child, new Rectangle(child.x, child.y, child.width, maxChildHeight), null, this.verticalAlign);
			
				switch(this.horizontalAlign)
				{
					case "center":
						child.x += difference / 2;
						break;
					case "right":
						child.x += difference;
						break;
				}
			}
		}
	}
}