package com.yahoo.astra.fl.charts.skins
{
	import fl.core.UIComponent;

	/**
	 * A skin shaped like a triangle with customizable color and alpha properties for its fill and border.
	 * 
	 * @author Josh Tynjala
	 */
	public class TriangleSkin extends UIComponent implements IProgrammaticSkin
	{
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function TriangleSkin()
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
			
			if(this.borderColor == this.fillColor)
			{
				this.graphics.lineStyle(0, 0, 0);
			}
			else
			{
				this.graphics.lineStyle(1, this.borderColor, this.borderAlpha);
			}
			this.graphics.beginFill(this.fillColor, this.fillAlpha);
			
			var w:Number = this.width * 1.25;
			var h:Number = w * Math.sqrt(3) / 2;

			this.graphics.moveTo(w / 2, 0);
			this.graphics.lineTo(w, h);
			this.graphics.lineTo(0, h);
			this.graphics.lineTo(w / 2, 0);
			this.graphics.endFill();
		}
		
	}
}