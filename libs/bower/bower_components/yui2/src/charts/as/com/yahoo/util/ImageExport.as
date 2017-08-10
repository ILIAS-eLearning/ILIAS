package com.yahoo.util 
{
	import com.adobe.images.PNGEncoder;
	import com.adobe.images.JPGEncoder;
	
	import flash.display.Sprite;
	import flash.display.BitmapData;

	import flash.events.ContextMenuEvent;
	import flash.net.FileReference;
	import flash.ui.ContextMenu;
	import flash.ui.ContextMenuItem;
	import flash.utils.ByteArray;	
	import flash.utils.Dictionary;
	
	public class ImageExport
	{
		public var fr:FileReference = new FileReference();
		public var mainApp:Sprite;
		public var fileName:String;
		public var contextMenu:ContextMenu;
		public var imageType:Dictionary = new Dictionary();
		
		public function ImageExport(stagePointer:Sprite)
		{
			mainApp = stagePointer;
			this.contextMenu = new ContextMenu();
			mainApp.contextMenu = this.contextMenu;   
		}
		
		public function addImageType(exportType:String, defaultFilename:String = "image"):void	
		{
			var item:ContextMenuItem;
			fileName = defaultFilename;
			
			switch(exportType) 
			{
				case "jpg":
				   item = new ContextMenuItem("Export to JPG");
				   break;
				default:
				   item = new ContextMenuItem("Export to PNG");
				   break;
			}
			
			imageType[item] = exportType;
            this.contextMenu.customItems.push(item);
            item.addEventListener(ContextMenuEvent.MENU_ITEM_SELECT, this.menuItemSelectHandler);        
		}
		
		public function menuItemSelectHandler(event:Object):void 
		{
			var exportType:String = this.imageType[event.target as ContextMenuItem];
			var imgSource:BitmapData = new BitmapData (mainApp.stage.stageWidth, mainApp.stage.stageHeight);
			imgSource.draw(mainApp);
			switch(exportType) 
			{
				case "jpg":
					var jpgEncoder:JPGEncoder = new JPGEncoder(100);
					var jpgStream:ByteArray = jpgEncoder.encode(imgSource);
					fr.save(jpgStream, fileName + ".jpg");
					break;
				default:
				    var pngStream:ByteArray = PNGEncoder.encode(imgSource);
				    fr.save(pngStream, fileName + ".png");
				    break;	
			}
		}
	}
}
