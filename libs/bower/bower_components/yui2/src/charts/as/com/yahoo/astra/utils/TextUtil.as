package com.yahoo.astra.utils
{	
	import flash.text.*;

	/**
	 * Utility class for text fields
	 * 
	 * @author Tripp Bridges
	 */	
	public class TextUtil
	{		
		/**
		 * Returns the width of a text field based on a <code>TextFormat</code> object and a string to be displayed
		 *
		 * @param textValue The text 
		 * @param tf 
		 *
		 * @return Number
		 */
		public static function getTextWidth(textValue:String, tf:TextFormat):Number
		{
			var textField:TextField = new TextField();
			textField.selectable = false;
			textField.autoSize = TextFieldAutoSize.LEFT;			
			textField.text = textValue;
			textField.setTextFormat(tf);
			return Math.max(textField.textWidth, textField.width);
			
		}
		
		/**
		 * Returns the height of a text field based on a <code>TextFormat</code> object and a string to be displayed
		 *
		 * @param textValue The text 
		 * @param tf 
		 *
		 * @return Number
		 */
		public static function getTextHeight(textValue:String, tf:TextFormat):Number
		{
			var textField:TextField = new TextField();
			textField.selectable = false;
			textField.autoSize = TextFieldAutoSize.LEFT;			
			textField.text = textValue;
			textField.setTextFormat(tf);
			return textField.textHeight;
		}	
		
		/**
		 * Changes individual property of a <code>TextFormat</code> object
		 */
		public static function changeTextFormatProps(tf:TextFormat, tfProps:Object):TextFormat
		{
			for(var i:String in tfProps)
			{
				tf[i] = tfProps[i];
			}
			return tf;
		}	
		
		/**
		 * Creates a copy of a <code>TextFormat</code> object
		 */
		public static function cloneTextFormat(tf:TextFormat):TextFormat
		{
			return new TextFormat(tf.font, tf.size, tf.color, tf.bold, tf.italic, tf.underline, tf.url, tf.target, tf.align, tf.leftMargin, tf.rightMargin, tf.indent, tf.leading);
		}		
		
	}
}