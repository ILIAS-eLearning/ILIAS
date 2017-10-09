package com.yahoo.astra.layout.modes
{
	import com.yahoo.astra.layout.events.LayoutEvent;
	import com.yahoo.astra.utils.DisplayObjectUtil;
	
	import flash.display.DisplayObject;
	import flash.geom.Rectangle;

	/**
	 * Arranges a DisplayObjectContainer's children using a page-like border
	 * algorithm. Children with TOP and BOTTOM constraints will be positioned
	 * and sized like page headers and footers. LEFT and RIGHT constrained
	 * children will be positioned and sized like sidebars and CENTER
	 * constrained children will be positioned and stretched to fill the
	 * remaining space.  
	 * 
	 * <p><strong>Advanced Client Options:</strong></p>
	 * <p>Client configuration parameters allow a developer to specify
	 * behaviors for individual children of the target container. To set these
	 * advanced options, one must call <code>addClient()</code> on the BorderLayout
	 * instance and pass the child to configure along with an object specifying
	 * the configuration parameters.</p>
	 * 
	 * @example The following code adds clients to a BorderLayout instance:
	 * <listing version="3.0">
	 * var border:BorderLayout = new BorderLayout();
	 * border.addClient( headerSprite, { constraint: BorderConstraints.TOP } );
	 * border.addClient( contentSprite,
	 * {
	 *     constraint: BorderConstraints.CENTER,
	 *     maintainAspectRatio: true,
	 *     horizontalAlign: HorizontalAlignment.CENTER,
	 *     verticalAlign: VerticalAlignment.MIDDLE
	 * });
	 * border.addClient( footerSprite, { constraint: BorderConstraints.BOTTOM } );
	 * 
	 * var container:LayoutContainer = new LayoutContainer();
	 * container.layoutMode = border;
	 * this.addChild( container );
	 * </listing>
	 * 
	 * <p>Several client parameters are available with the BorderLayout algorithm:</p>
	 * <dl>
	 * 	<dt><strong><code>constraint</code></strong> : String</dt>
	 * 		<dd>The BorderConstraints value to be used on the target by the layout algorithm. The default
	 * 		value is <code>BorderConstraints.CENTER</code>.</dd>
	 * 	<dt><strong><code>maintainAspectRatio</code></strong> : Boolean</dt>
	 * 		<dd>If true, the aspect ratio of the target will be maintained if it is resized.</dd>
	 * 	<dt><strong><code>horizontalAlign</code></strong> : String</dt>
	 * 		<dd>The horizontal alignment used when positioning the target. Used in combination with
	 * 		<code>maintainAspectRatio</code>.</dd>
	 * 	<dt><strong><code>verticalAlign</code></strong> : String</dt>
	 * 		<dd>The vertical alignment used when positioning the target. Used in combination with
	 * 		<code>maintainAspectRatio</code>.</dd>
	 * 	<dt><strong><code>aspectRatio</code></strong> : Number</dt>
	 * 		<dd>The desired aspect ratio to use with <code>maintainAspectRatio</code>. This value is optional.
	 * 		If no aspect ratio is provided, it will be determined based on the target's original width and height.</dd>
	 * 	<dt><strong><code>includeInLayout</code></strong> : Boolean</dt>
	 * 		<dd>If <code>false</code>, the target will not be included in layout calculations. The default value is <code>true</code>.</dd>
	 * </dl>
	 * 
	 * @see BorderConstraints
	 * @see HorizontalAlignment
	 * @see VerticalAlignment
	 * @see com.yahoo.astra.layout.LayoutContainer
	 * 
	 * @author Josh Tynjala
	 */
	public class BorderLayout extends BaseLayoutMode implements IAdvancedLayoutMode
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function BorderLayout()
		{
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
	
		/**
		 * @private
		 * Storage for the verticalGap property.
		 */
		private var _verticalGap:Number = 0;
		
		/**
		 * The number of vertical pixels between each item displayed by this
		 * container.
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
		 * The number of horizontal pixels between each item displayed by this
		 * container.
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
		
	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override public function addClient(target:DisplayObject, configuration:Object = null):void
		{
			//if horizontalAlign or verticalAlign is not specified, set some defaults
			configuration.horizontalAlign = configuration.horizontalAlign ? configuration.horizontalAlign : "left";
			configuration.verticalAlign = configuration.verticalAlign ? configuration.verticalAlign : "top";
			
			//if no aspectRatio has been specified, use the aspect ratio
			//calculated from the target's width and height
			if(configuration.maintainAspectRatio && !configuration.aspectRatio)
			{
				configuration.aspectRatio = target.width / target.height;
			}
			
			super.addClient(target, configuration);
		}

		/**
		 * @inheritDoc
		 */
		override public function layoutObjects(displayObjects:Array, bounds:Rectangle):Rectangle
		{
			const START_X:Number = bounds.x + this.paddingLeft;
			const START_Y:Number = bounds.y + this.paddingTop;
			
			var width:Number = bounds.width;
			if(bounds.width == Number.POSITIVE_INFINITY)
			{
				width = this.measureChildWidths();
			}
			width -= (this.paddingLeft + this.paddingRight);
			
			
			var height:Number = bounds.height;
			if(bounds.height == Number.POSITIVE_INFINITY)
			{
				height = this.measureChildHeights();
			}
			height -= (this.paddingTop + this.paddingBottom);
			
			var remainingWidth:Number = width;
			var remainingHeight:Number = height;
			
			//position the top children
			var topHeight:Number = 0;
			var topChildren:Array = this.getChildrenByConstraint(BorderConstraints.TOP, true);
			var topChildCount:int = topChildren.length;
			for(var i:int = 0; i < topChildCount; i++)
			{
				var topChild:DisplayObject = DisplayObject(topChildren[i]);
				var config:Object = this.configurations[this.clients.indexOf(topChild)];
				
				var x:Number = START_X;
				var y:Number = START_Y + topHeight;
				
				if(config.maintainAspectRatio)
				{
					DisplayObjectUtil.resizeAndMaintainAspectRatio(topChild, width, topChild.height, config.aspectRatio);
				}
				else
				{
					topChild.width = width;
				}
				DisplayObjectUtil.align(topChild, new Rectangle(x, y, width, topChild.height), config.horizontalAlign, config.verticalAlign);
				
				topHeight += topChild.height + this.verticalGap;
			}
			remainingHeight -= topHeight;
			
			//position the bottom children
			var bottomHeight:Number = 0;
			var bottomChildren:Array = this.getChildrenByConstraint(BorderConstraints.BOTTOM, true);
			var bottomChildCount:int = bottomChildren.length;
			for(i = 0; i < bottomChildCount; i++)
			{
				var bottomChild:DisplayObject = DisplayObject(bottomChildren[i]);
				config = this.configurations[this.clients.indexOf(bottomChild)];
				
				bottomHeight += bottomChild.height;
				
				x = START_X;
				y = START_Y + height - bottomHeight;
				
				if(config.maintainAspectRatio)
				{
					DisplayObjectUtil.resizeAndMaintainAspectRatio(bottomChild, width, bottomChild.height, config.aspectRatio);
				}
				else
				{
					bottomChild.width = width;
				}
				DisplayObjectUtil.align(bottomChild, new Rectangle(x, y, width, bottomChild.height), config.horizontalAlign, config.verticalAlign);
				
				bottomHeight += this.verticalGap;
			}
			
			//if topHeight + bottomHeight < the total height, fix the overlap
			var difference:Number = (START_Y + topHeight) - (START_Y + height - bottomHeight); 
			if(difference > 0)
			{
				for(i = 0; i < bottomChildCount; i++)
				{
					bottomChild = DisplayObject(bottomChildren[i]);
					bottomChild.y += difference;
				}
			}
			remainingHeight -= bottomHeight;
			
			//the height of the center area affects the height of the left and right areas
			var centerHeight:Number = Math.max(remainingHeight, 0);
			
			//position the left children
			var leftWidth:Number = 0;
			var leftChildren:Array = this.getChildrenByConstraint(BorderConstraints.LEFT, true);
			var leftChildCount:int = leftChildren.length;
			for(i = 0; i < leftChildCount; i++)
			{
				var leftChild:DisplayObject = DisplayObject(leftChildren[i]);
				config = this.configurations[this.clients.indexOf(leftChild)];
				
				x = START_X + leftWidth;
				y = START_Y + topHeight;
				
				if(config.maintainAspectRatio)
				{
					DisplayObjectUtil.resizeAndMaintainAspectRatio(leftChild, leftChild.width, centerHeight, config.aspectRatio);
				}
				else
				{
					leftChild.height = centerHeight;
				}
				DisplayObjectUtil.align(leftChild, new Rectangle(x, y, leftChild.width, centerHeight), config.horizontalAlign, config.verticalAlign);
				
				leftWidth += leftChild.width + this.horizontalGap;
			}
			remainingWidth -= leftWidth;
			
			//position the right children
			var rightWidth:Number = 0;
			var rightChildren:Array = this.getChildrenByConstraint(BorderConstraints.RIGHT, true);
			var rightChildCount:int = rightChildren.length;
			for(i = 0; i < rightChildCount; i++)
			{
				var rightChild:DisplayObject = DisplayObject(rightChildren[i]);
				config = this.configurations[this.clients.indexOf(rightChild)];
				
				rightWidth += rightChild.width;
				
				x = START_X + width - rightWidth;
				y = START_Y + topHeight;
				
				if(config.maintainAspectRatio)
				{
					DisplayObjectUtil.resizeAndMaintainAspectRatio(rightChild, rightChild.width, centerHeight, config.aspectRatio);
				}
				else
				{
					rightChild.height = centerHeight;
				}
				DisplayObjectUtil.align(rightChild, new Rectangle(x, y, rightChild.width, centerHeight), config.horizontalAlign, config.verticalAlign);
				
				rightWidth += this.horizontalGap;
			}
			
			//if leftWidth + rightWidth < the total width, fix the overlap
			difference = (START_X + leftWidth) - (START_X + width - rightWidth); 
			if(difference > 0)
			{
				for(i = 0; i < rightChildCount; i++)
				{
					rightChild = DisplayObject(rightChildren[i]);
					rightChild.x += difference;
				}
			}
			remainingWidth -= rightWidth;
			
			//position the center children in the remaining width
			var centerWidth:Number = Math.max(remainingWidth, 0);
			var centerChildren:Array = this.getChildrenByConstraint(BorderConstraints.CENTER, true);
			var centerChildCount:int = centerChildren.length;
			var centerChildHeight:Number = centerHeight / centerChildCount;
			for(i = 0; i < centerChildCount; i++)
			{
				var centerChild:DisplayObject = DisplayObject(centerChildren[i]);
				config = this.configurations[this.clients.indexOf(centerChild)];
				
				x = START_X + leftWidth;
				y = START_Y + topHeight + (i * centerChildHeight);
				
				if(config.maintainAspectRatio)
				{
					DisplayObjectUtil.resizeAndMaintainAspectRatio(centerChild, centerWidth, centerChildHeight, config.aspectRatio);
				}
				else
				{
					centerChild.width = centerWidth;
					centerChild.height = centerChildHeight;
				}
				DisplayObjectUtil.align(centerChild, new Rectangle(x, y, centerWidth, centerChildHeight), config.horizontalAlign, config.verticalAlign);
			}
			
			if(remainingWidth < 0)
			{
				width -= remainingWidth;
			}
			
			if(remainingHeight < 0)
			{
				height -= remainingHeight;
			}
			
			bounds.width = width + this.paddingLeft + this.paddingRight;
			bounds.height = height + this.paddingTop + this.paddingBottom;
			return bounds;
		}
		
		/**
		 * @private
		 * Creates the default configuration for this layout mode.
		 */
		override protected function newConfiguration():Object
		{
			return {
				includeInLayout: true,
				constraint: BorderConstraints.CENTER
			};
		}
		
		/**
		 * @private
		 * If no width is specified for the layout container, we need to
		 * measure the children to determine the best width.
		 */
		protected function measureChildWidths():Number
		{
			var totalWidth:Number = this.paddingLeft + this.paddingRight;
			
			var leftChildren:Array = this.getChildrenByConstraint(BorderConstraints.LEFT, true);
			var childCount:int = leftChildren.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(leftChildren[i]);
				totalWidth += child.width + this.horizontalGap;
			}
			
			var rightChildren:Array = this.getChildrenByConstraint(BorderConstraints.RIGHT, true);
			childCount = rightChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(rightChildren[i]);
				totalWidth += child.width + this.horizontalGap;
			}
			
			var maxWidth:Number = 0;
			var centerChildren:Array = this.getChildrenByConstraint(BorderConstraints.CENTER, true);
			childCount = centerChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(centerChildren[i]);
				maxWidth = Math.max(maxWidth, child.width);
			}
			totalWidth += maxWidth;
			
			maxWidth = 0;
			var topChildren:Array = this.getChildrenByConstraint(BorderConstraints.TOP, true);
			childCount = topChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(topChildren[i]);
				maxWidth = Math.max(maxWidth, child.width);
			}
			var bottomChildren:Array = this.getChildrenByConstraint(BorderConstraints.BOTTOM, true);
			childCount = bottomChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(bottomChildren[i]);
				maxWidth = Math.max(maxWidth, child.width);
			}
			totalWidth = Math.max(maxWidth, totalWidth);
			return totalWidth;
		}
		
		/**
		 * @private
		 * If no height is specified for the layout container, we need to
		 * measure the children to determine the best height.
		 */
		protected function measureChildHeights():Number
		{
			var totalHeight:Number = this.paddingTop + this.paddingBottom;
			
			var topChildren:Array = this.getChildrenByConstraint(BorderConstraints.TOP, true);
			var childCount:int = topChildren.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(topChildren[i]);
				totalHeight += child.height;
			}
			
			var bottomChildren:Array = this.getChildrenByConstraint(BorderConstraints.BOTTOM, true);
			childCount = bottomChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(bottomChildren[i]);
				totalHeight += child.height;
			}
			
			var centerTotalHeight:Number = 0;
			var centerChildren:Array = this.getChildrenByConstraint(BorderConstraints.CENTER, true);
			childCount = centerChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(centerChildren[i]);
				centerTotalHeight += child.height;
			}
			
			var maxHeight:Number = centerTotalHeight;
			var rightChildren:Array = this.getChildrenByConstraint(BorderConstraints.RIGHT, true);
			childCount = rightChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(rightChildren[i]);
				maxHeight = Math.max(maxHeight, child.height);
			}
			var leftChildren:Array = this.getChildrenByConstraint(BorderConstraints.LEFT, true);
			childCount = leftChildren.length;
			for(i = 0; i < childCount; i++)
			{
				child = DisplayObject(leftChildren[i]);
				maxHeight = Math.max(maxHeight, child.height);
			}
			totalHeight += maxHeight;
			
			return totalHeight;
		}
		
		/**
		 * @private
		 * A simple filter for getting all the clients with a specific constraint
		 * in their configuration.
		 */
		protected function getChildrenByConstraint(constraint:String, inLayoutOnly:Boolean = false):Array
		{
			return this.clients.filter(function(item:DisplayObject, index:int, source:Array):Boolean
			{
				var configuration:Object = this.configurations[index];
				return configuration.constraint == constraint && (inLayoutOnly ? configuration.includeInLayout : true);
			}, this);
		}
		
	}
}