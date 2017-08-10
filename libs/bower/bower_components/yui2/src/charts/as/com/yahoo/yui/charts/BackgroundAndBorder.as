package com.yahoo.yui.charts
{
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	import fl.containers.UILoader;
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.CapsStyle;
	import flash.display.JointStyle;
	import flash.display.Shape;
	import flash.events.ErrorEvent;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.SecurityErrorEvent;
	import flash.geom.Matrix;
	import flash.net.URLRequest;
	import flash.system.LoaderContext;

	public class BackgroundAndBorder extends UIComponent
	{
		private static const GRAPHICS_INVALID:String = "graphics";
		private static const IMAGE_URL_INVALID:String = "imageURL";
		private static const IMAGE_MODE_INVALID:String = "imageMode";
		
		public function BackgroundAndBorder()
		{
			super();
		}
		
		/**
		 * @private
		 * We use a mask so that large images don't cause trouble.
		 */
		private var _mask:Shape;
		
		private var _fillColor:uint = 0xffffff;
		
		public function get fillColor():uint
		{
			return this._fillColor;
		}
		
		public function set fillColor(value:uint):void
		{
			if(this._fillColor != value)
			{
				this._fillColor = value;
				this.invalidate(GRAPHICS_INVALID);
			}
		}
		
		private var _fillAlpha:Number = 1;
		
		public function get fillAlpha():Number
		{
			return this._fillAlpha;
		}
		
		public function set fillAlpha(value:Number):void
		{
			if(this._fillAlpha != value)
			{
				this._fillAlpha = value;
				this.invalidate(GRAPHICS_INVALID);
			}
		}
		
		private var _borderColor:uint = 0x000000;
		
		public function get borderColor():uint
		{
			return this._borderColor;
		}
		
		public function set borderColor(value:uint):void
		{
			if(this._borderColor != value)
			{
				this._borderColor = value;
				this.invalidate(GRAPHICS_INVALID);
			}
		}
		
		private var _borderWeight:Number = 0;
		
		public function get borderWeight():Number
		{
			return this._borderWeight;
		}
		
		public function set borderWeight(value:Number):void
		{
			if(this._borderWeight != value)
			{
				this._borderWeight = value;
				this.invalidate(GRAPHICS_INVALID);
			}
		}
		
		protected var imageLoader:UILoader;
		
		private var _image:String;
		
		public function get image():String
		{
			return this._image;
		}
		
		public function set image(value:String):void
		{
			if(this._image != value)
			{
				this._image = value;
				this.invalidate(IMAGE_URL_INVALID);
			}
		}
		
		private var _imageMode:String = BackgroundImageMode.REPEAT;
		
		public function get imageMode():String
		{
			return this._imageMode;
		}
		
		public function set imageMode(value:String):void
		{
			if(this._imageMode != value)
			{
				this._imageMode = value;
				this.invalidate(IMAGE_MODE_INVALID);
			}
		}
		
		private var _imageDefaultWidth:Number = 0;
		private var _imageDefaultHeight:Number = 0;
		
		override protected function configUI():void
		{
			super.configUI();
			
			this._mask = new Shape();
			this.addChild(this._mask);
			this.mask = this._mask;
		}
		
		override protected function draw():void
		{
			var graphicsInvalid:Boolean = this.isInvalid(GRAPHICS_INVALID);
			var imageURLInvalid:Boolean = this.isInvalid(IMAGE_URL_INVALID);
			var imageModeInvalid:Boolean = this.isInvalid(IMAGE_MODE_INVALID);
			var sizeInvalid:Boolean = this.isInvalid(InvalidationType.SIZE);
			
			if(imageURLInvalid)
			{
				if(this.imageLoader)
				{
					this.imageLoader.unload();
					this.removeChild(this.imageLoader);
					this.imageLoader = null;
				}
				
				if(this._image)
				{
					this.imageLoader = new UILoader();
					this.imageLoader.addEventListener(Event.COMPLETE, loaderCompleteHandler);
					this.imageLoader.addEventListener(IOErrorEvent.IO_ERROR, loaderErrorHandler);
					this.imageLoader.addEventListener(SecurityErrorEvent.SECURITY_ERROR, loaderErrorHandler);
					this.imageLoader.load(new URLRequest(this.image), new LoaderContext(true));
					this.addChild(this.imageLoader);
					this.imageLoader.visible = false;
				}
			}
			
			if(sizeInvalid || imageModeInvalid || graphicsInvalid)
			{
				this.graphics.clear();
				if(this._borderWeight == 0)
				{
					//if border is zero, we need to do some special stuff
					this.graphics.lineStyle(0, 0, 0);
				}
				else this.graphics.lineStyle(this._borderWeight, this._borderColor, 1, true, "normal", CapsStyle.SQUARE, JointStyle.MITER);
				this.graphics.beginFill(this._fillColor, this._fillAlpha);
				this.graphics.drawRect(this._borderWeight / 2, this._borderWeight / 2, this.width - this._borderWeight, this.height - this._borderWeight);
				this.graphics.endFill();
				
				if(this.imageLoader && this.imageLoader.content)
				{
					if(this.imageLoader.content is Bitmap)
					{
						Bitmap(this.imageLoader.content).smoothing = true;
					}
					this.imageLoader.maintainAspectRatio = true;
					switch(this.imageMode)
					{
						case BackgroundImageMode.STRETCH:
							this.imageLoader.maintainAspectRatio = false;
						case BackgroundImageMode.STRETCH_AND_MAINTAIN_ASPECT_RATIO:
							this.imageLoader.visible = true;
							this.imageLoader.x = this._borderWeight;
							this.imageLoader.y = this._borderWeight;
							this.imageLoader.width = this.width - this._borderWeight * 2;
							this.imageLoader.height = this.height - this._borderWeight * 2;
							this.imageLoader.drawNow();
							break;
						default:
							//default: repeat x and/or y
							var rectWidth:Number = this.width - this._borderWeight * 2;
							var rectHeight:Number = this.height - this._borderWeight * 2;
							switch(this.imageMode)
							{
								case BackgroundImageMode.REPEAT_X:
									rectWidth = this._imageDefaultWidth;
									rectHeight = this.height - this._borderWeight * 2
									break;
								case BackgroundImageMode.REPEAT_Y:
									rectWidth = this.width - this._borderWeight * 2;
									rectHeight = this._imageDefaultHeight;
									break;
									
								//for some reason, positioning is off if we don't draw
								//to bitmapdata, so no-repeat is accepted here too
								case BackgroundImageMode.NO_REPEAT:
									rectWidth = this._imageDefaultWidth;
									rectHeight = this._imageDefaultHeight;
									break;
							}
							this.imageLoader.width = this._imageDefaultWidth;
							this.imageLoader.height = this._imageDefaultHeight;
							this.imageLoader.drawNow();
							this.imageLoader.visible = false;
							if(this._imageDefaultWidth > 0 && this._imageDefaultHeight > 0)
							{
								var bitmapData:BitmapData = new BitmapData(int(this._imageDefaultWidth), int(this._imageDefaultHeight), true, 0x00000000);
								bitmapData.draw(this.imageLoader);
								this.graphics.lineStyle(0, 0, 0);
								this.graphics.beginBitmapFill(bitmapData, new Matrix(1, 0, 0, 1, this._borderWeight, this._borderWeight));
								this.graphics.drawRect(this._borderWeight, this._borderWeight, rectWidth, rectHeight);
								this.graphics.endFill();
							}
							break;
					}
				}
			}
		
			if(sizeInvalid)
			{
				this.drawMask();
			}
			
			super.draw();
		}
		
		protected function drawMask():void
		{
			this._mask.graphics.clear();
			this._mask.graphics.beginFill(0xff00ff, 1);
			this._mask.graphics.drawRect(0, 0, this.width, this.height);
			this._mask.graphics.endFill();
		}
		
		/**
		 * @private
		 * Once the loader has finished loading, it's time for a redraw so that
		 * it apppears at the correct size.
		 */
		private function loaderCompleteHandler(event:Event):void
		{
			try
			{
				this._imageDefaultWidth = this.imageLoader.content.width;
				this._imageDefaultHeight = this.imageLoader.content.height;
			}
			catch(error:SecurityError)
			{
				//we don't have crossdomain permission
				//unload the image because it is useless to us at this point anyway
				this.imageLoader.unload();
				this.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, error.message));
			}
			this.invalidate(InvalidationType.SIZE);
		}
		
		/**
		 * @private
		 * If we encounter an error loading the data, notify as needed.
		 */
		private function loaderErrorHandler(event:Event):void
		{
			var errorText:String = Object(event).text;
			if(this.hasEventListener(ErrorEvent.ERROR))
			{
				this.dispatchEvent(new ErrorEvent(ErrorEvent.ERROR, false, false, errorText));
			}
			else
			{
				throw new Error(errorText);
			}
		}
		
	}
	
}
