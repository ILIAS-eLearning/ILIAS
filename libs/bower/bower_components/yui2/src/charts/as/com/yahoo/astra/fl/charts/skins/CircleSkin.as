package com.yahoo.astra.fl.charts.skins
{
	import fl.core.UIComponent;
	import flash.display.Sprite;

	/**
	 * A skin shaped like a circle with customizable color and alpha properties for its fill and border.
	 * 
	 * @author Josh Tynjala
	 */
	public class CircleSkin extends UIComponent implements IProgrammaticSkin
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function CircleSkin()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 * Storage for the fillColor property.
		 */
		private var _fillColor:uint = 0x000000;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.skins.IProgrammaticSkin#fillColor
		 */
		public function get fillColor():uint
		{
			return this._fillColor;
		}
		
		/**
		 * @private
		 */
		public function set fillColor(value:uint):void
		{
			if(this._fillColor != value)
			{
				this._fillColor = value;
				this.invalidate();
			}
		}
		
		/**
		 * @private 
		 * Storage for outline color
		 */
		private var _borderColor:uint;
		
		/**
		 * @copy com.yahoo.astra.fl.charts.skins.IProgrammaticSkin#borderColor
		 */
		public function get borderColor():uint
		{
			return _borderColor;
		}
		
		/**
		 * @private (setter)
		 */
		public function set borderColor(value:uint):void
		{
			if(this._borderColor != value)
			{
				this._borderColor = value;
				this.invalidate();
			}
		}
		
		/**
		 * @private
		 * Storage for the fill alpha.
		 */
		private var _fillAlpha:Number = 1;
		
		/**
		 * The alpha value of the fill.
		 */
		public function get fillAlpha():Number
		{
			return _fillAlpha;
		}

		/**
		 * @private (setter)
		 */
		public function set fillAlpha(value:Number):void
		{
			if(this._fillAlpha != value)
			{
				this._fillAlpha = value;
				this.invalidate();
			}
		}
		
		/**
		 * @private
		 * Storage for the border alpha.
		 */
		private var _borderAlpha:Number = 1;
		
		/**
		 * The alpha value of the border.
		 */
		public function get borderAlpha():Number
		{
			return _borderAlpha;
		}
		
		/**
		 * @private (setter)
		 */
		public function set borderAlpha(value:Number):void
		{
			if(this._borderAlpha != value)
			{
				this._borderAlpha = value;
				this.invalidate();
			}
		}

	//--------------------------------------
	//  Protected Methods
	//--------------------------------------
	
		/**
		 * @private
		 */
		override protected function draw():void
		{
			super.draw();
			
			this.graphics.clear();
			if(this.width == 0 || this.height == 0 || isNaN(this.width) || isNaN(this.height))
			{
				return;
			}
			
			
			if(this.fillColor == this.borderColor)
			{
				this.graphics.lineStyle(0, 0, 0);
			}
			else
			{
				this.graphics.lineStyle(1, this.borderColor, this.borderAlpha);
			}
			this.graphics.beginFill(this.fillColor, this.fillAlpha);
			this.graphics.drawCircle((this.width / 2), (this.height / 2), Math.min(this.width, this.height) / 2);
			this.graphics.endFill();	
		}
		
	}
}