/**
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/.
 * 
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * 
 * The Original Code is part of the Open Source Flex 3 SDK Downloads
 * (http://opensource.adobe.com/wiki/display/flexsdk/Downloads)
 * 
 * The Initial Developer of the Original Code is Adobe Systems Incorporated
 * (see original files for appropriate copyright notices)
 * 
 * Contributor(s): Yahoo! Inc.
 * 
 * Copyright (c) 2008 Yahoo! Inc. All Rights Reserved.
 */
package com.yahoo.astra.layout.modes
{
	import flash.display.DisplayObject;
	
	/**
	 * Utility functions used for determining pixel-based sizes from percentage-based sizes.
	 * 
	 * @author Josh Tynjala and Adobe Systems Inc.
	 */
	public class PercentageSizeUtil
	{

	//--------------------------------------
	//  Static Methods
	//--------------------------------------
	
		/**
		 * This function sets the width of each child so that the widths add up
		 * to spaceForChildren. Each child is set to its preferred width if its
		 * percentWidth is zero. If it's percentWidth is a positive number the
		 * child grows depending on the size of its parent. The height of each
		 * child is set to its preferred height. The return value is any extra
		 * space that's left over after growing all children to their maxWidth.
		 */
		public static function flexChildWidthsProportionally(children:Array, configurations:Array, totalWidth:Number, totalHeight:Number):Number
		{
			var spaceToDistribute:Number = totalWidth;
			var totalPercentWidth:Number = 0;
			var childInfoArray:Array = [];
	
			// If the child is flexible, store information about it in the
			// childInfoArray. For non-flexible children, just set the child's
			// width and height immediately.
			//
			// Also calculate the sum of all widthFlexes, and calculate the 
			// sum of the width of all non-flexible children.
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				var config:Object = configurations[i];
				
				if(!config.includeInLayout)
				{
					//skip non-configuration children
					continue;
				}
				
				var percentWidth:Number = config.percentWidth;
				var percentHeight:Number = config.percentHeight;
				var height:Number = NaN;
				
				if(!isNaN(percentHeight))
				{
					height = Math.max(config.minHeight,
						Math.min(config.maxHeight, ((percentHeight >= 100) ? totalHeight : totalHeight * percentHeight / 100)));
				}
				
				if(!isNaN(percentWidth))
				{
					totalPercentWidth += percentWidth;
	
					var childInfo:ChildInfo = new ChildInfo();
					childInfo.percent = percentWidth;
					childInfo.min = config.minWidth;
					childInfo.max = config.maxWidth;
					childInfo.opposite = height;
					childInfo.child = child;
					
					childInfoArray.push(childInfo);
				}
				else
				{
					//var width:Number = child.width;
					// if scaled and zoom is playing, best to let the sizes be non-integer
					// otherwise the rounding creates an error that accumulates in some components like List
					if(child.scaleX == 1 && child.scaleY == 1)
					{
						if(!isNaN(height))
						{
							child.height = Math.floor(height);
						}
					}
					else
					{
						if(!isNaN(height))
						{
							child.height = height;
						}
					}
	
					// Need to account for the actual child width since 
					// setActualSize may trigger a Resize effect, which 
					// could change the size of the component.
					spaceToDistribute -= child.width;
				}
			}
	
			// Distribute the extra space among the children.
			if(totalPercentWidth)
			{
				spaceToDistribute = flexChildrenProportionally(totalWidth, spaceToDistribute, totalPercentWidth, childInfoArray);
	
				// Set the widths and heights of the flexible children
				childCount = childInfoArray.length;
				for(i = 0; i < childCount; i++)
				{
					childInfo = ChildInfo(childInfoArray[i]);
					child = childInfo.child;
	
					// if scaled and zoom is playing, best to let the sizes be non-integer
					// otherwise the rounding creates an error that accumulates in some components like List
					if(child.scaleX == 1 && child.scaleY == 1)
					{
						child.width = Math.floor(childInfo.size);
						if(!isNaN(childInfo.opposite))
						{
							child.height = Math.floor(childInfo.opposite);
						}
					}
					else
					{
						child.width = childInfo.size;
						if(!isNaN(childInfo.opposite))
						{
							child.height = childInfo.opposite;
						}
					}
				}
				
				distributeExtraWidth(children, configurations, totalWidth);
			}
	
			return spaceToDistribute;
		}
	
		/**
		 *  This function sets the height of each child
		 *  so that the heights add up to spaceForChildren. 
		 *  Each child is set to its preferred height
		 *  if its percentHeight is zero.
		 *  If its percentHeight is a positive number,
		 *  the child grows (or shrinks) to consume its share of extra space.
		 *  The width of each child is set to its preferred width.
		 *  The return value is any extra space that's left over
		 *  after growing all children to their maxHeight.
		 */
		public static function flexChildHeightsProportionally(children:Array, configurations:Array,
			totalWidth:Number, totalHeight:Number):Number
		{
			var spaceToDistribute:Number = totalHeight;
			var totalPercentHeight:Number = 0;
			var childInfoArray:Array = [];
	
			// If the child is flexible, store information about it in the
			// childInfoArray. For non-flexible children, just set the child's
			// width and height immediately.
			//
			// Also calculate the sum of all percentHeights, and calculate the 
			// sum of the height of all non-flexible children.
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				var config:Object = configurations[i];
	
				if(!config.includeInLayout)
				{
					//skip children that aren't in the layout
					continue;
				}
				
				var percentWidth:Number = config.percentWidth;
				var percentHeight:Number = config.percentHeight;
				var width:Number = NaN;
				
				if(!isNaN(percentWidth))
				{
					width = Math.max(config.minWidth, Math.min(config.maxWidth,
						((percentWidth >= 100) ? totalWidth : totalWidth * percentWidth / 100)));
				}
			
				if(!isNaN(percentHeight))
				{
					totalPercentHeight += percentHeight;
	
					var childInfo:ChildInfo = new ChildInfo();
					childInfo.percent = percentHeight;
					childInfo.min = config.minHeight;
					childInfo.max = config.maxHeight;
					childInfo.opposite = width;
					childInfo.child = child;
					
					childInfoArray.push(childInfo);
				}
				else
				{
					if(child.scaleX == 1 && child.scaleY == 1)
					{
						if(!isNaN(width))
						{
							child.width = Math.floor(width);
						}
					}
					else
					{
						if(!isNaN(width))
						{
							child.width = width;
						}
					}
	
					// Need to account for the actual child height since 
					// setActualSize may trigger a Resize effect, which 
					// could change the size of the component.
					spaceToDistribute -= child.height;
				}
			}
	
			// Distribute the extra space among the children.
			if(totalPercentHeight)
			{
				spaceToDistribute = flexChildrenProportionally(totalHeight, spaceToDistribute, totalPercentHeight, childInfoArray);
	
				// Set the widths and heights of the flexible children
				childCount = childInfoArray.length;
				for(i = 0; i < childCount; i++)
				{
					childInfo = ChildInfo(childInfoArray[i]);
					child = childInfo.child;			
	
					// if scaled and zoom is playing, best to let the sizes be non-integer
					// otherwise the rounding creates an error that accumulates in some components like List
					if(child.scaleX == 1 && child.scaleY == 1)
					{
						if(!isNaN(childInfo.opposite))
						{
							child.width = Math.floor(childInfo.opposite);
						}
						child.height = Math.floor(childInfo.size);
					}
					else
					{
						if(!isNaN(childInfo.opposite))
						{
							child.width = childInfo.opposite;
						}
						child.height = childInfo.size;
					}
				}
				
	            distributeExtraHeight(children, configurations, totalHeight);
			}
			
			return spaceToDistribute;
		}
	
		/**
		 *  This function distributes excess space among the flexible children.
		 *  It does so with a view to keep the children's overall size
		 *  close the ratios specified by their percent.
		 *
		 *  @param spaceForChildren The total space for all children
		 *
		 *  @param spaceToDistribute The space that needs to be distributed
		 *  among the flexible children.
		 *
		 *  @param childInfoArray An array of Objects. When this function
		 *  is called, each object should define the following properties:
		 *  - percent: the percentWidth or percentHeight of the child (depending
		 *  on whether we're growing in a horizontal or vertical direction)
		 *  - min: the minimum width (or height) for that child
		 *  - max: the maximum width (or height) for that child
		 *
		 *  @return When this function finishes executing, a "size" property
		 *  will be defined for each child object. The size property contains
		 *  the portion of the spaceToDistribute to be distributed to the child.
		 *  Ideally, the sum of all size properties is spaceToDistribute.
		 *  If all the children hit their minWidth/maxWidth/minHeight/maxHeight
		 *  before the space was distributed, then the remaining unused space
		 *  is returned. Otherwise, the return value is zero.
		 */
		public static function flexChildrenProportionally(spaceForChildren:Number, spaceToDistribute:Number,
									totalPercent:Number, childInfoArray:Array):Number
		{
			// The algorithm iterivately attempts to break down the space that 
			// is consumed by "flexible" containers into ratios that are related
			// to the percentWidth/percentHeight of the participating containers.
			
			var numChildren:int = childInfoArray.length;
			var flexConsumed:Number; // space consumed by flexible compontents
			var done:Boolean;
			
			// We now do something a little tricky so that we can 
			// support partial filling of the space. If our total
			// percent < 100% then we can trim off some space.
			var unused:Number = spaceToDistribute - (spaceForChildren * totalPercent / 100);
			if(unused > 0)
			{
				spaceToDistribute -= unused;
			}
	
			// Continue as long as there are some remaining flexible children.
			// The "done" flag isn't strictly necessary, except that it catches
			// cases where round-off error causes totalPercent to not exactly
			// equal zero.
			do
			{
				flexConsumed = 0; // space consumed by flexible compontents
				done = true; // we are optimistic
				
				// Space for flexible children is the total amount of space
				// available minus the amount of space consumed by non-flexible
				// components.Divide that space in proportion to the percent
				// of the child
				var spacePerPercent:Number = spaceToDistribute / totalPercent;
				
				// Attempt to divide out the space using our percent amounts,
				// if we hit its limit then that control becomes 'non-flexible'
				// and we run the whole space to distribute calculation again.
				for(var i:int = 0; i < numChildren; i++)
				{
					var childInfo:ChildInfo = ChildInfo(childInfoArray[i]);
	
					// Set its size in proportion to its percent.
					var size:Number = childInfo.percent * spacePerPercent;
	
					// If our flexiblity calc say grow/shrink more than we are
					// allowed, then we grow/shrink whatever we can, remove
					// ourselves from the array for the next pass, and start
					// the loop over again so that the space that we weren't
					// able to consume / release can be re-used by others.
					if(size < childInfo.min)
					{
						var min:Number = childInfo.min;
						childInfo.size = min;
						
						// Move this object to the end of the array
						// and decrement the length of the array. 
						// This is slightly expensive, but we don't expect
						// to hit these min/max limits very often.
						childInfoArray[i] = childInfoArray[--numChildren];
						childInfoArray[numChildren] = childInfo;
	
						totalPercent -= childInfo.percent;
						spaceToDistribute -= min;
						done = false;
						break;
					}
					else if(size > childInfo.max)
					{
						var max:Number = childInfo.max;
						childInfo.size = max;
	
						childInfoArray[i] = childInfoArray[--numChildren];
						childInfoArray[numChildren] = childInfo;
	
						totalPercent -= childInfo.percent;
						spaceToDistribute -= max;
						done = false;
						break;
					}
					else
					{
						// All is well, let's carry on...
						childInfo.size = size;
						flexConsumed += size;
					}
				}
			} 
			while(!done);
	
			return Math.max(0, Math.floor(spaceToDistribute - flexConsumed))
		}
		
		/**
		 *  This function distributes excess space among the flexible children
		 *  because of rounding errors where we want to keep children's dimensions 
		 *  full pixel amounts.  This only distributes the extra space 
		 *  if there was some rounding down and there are still 
		 *  flexible children.
		 *
		 *  @param parent The parent container of the children.
		 * 
		 *  @param spaceForChildren The total space for all children
		 */
		public static function distributeExtraHeight(children:Array, configurations:Array, spaceForChildren:Number):void
		{
			// We should only get here after distributing the majority of the 
			// space already.  This is done in flexChildHeightsProportionally.
			// Strategy here is to keep adding 1 pixel at a time to each 
			// component that's flexible (percentHeight defined and hasn't
			// reached maxHeight yet).  We could use another approach where
			// we add more than a pixel at a time, but we'd have to first 
			// calculate exactly how many flexible components we have first
			// and see how much space we can add to them without hitting
			// their maxHeight.  Since we're just dealing with rounding 
			// issues, we should only make one pass here (if we hit maxHeight
			// problems, we might make more than one, but not many more).
			
			// We just distribute from the top-down and don't care about 
			// who was "rounded down the most"
			
			// First check if we should distribute any extra space.  To do 
			// this, we check to see if someone suffers from rounding error.
			var wantToGrow:Boolean = false;
			var percentHeight:Number;
			var spaceToDistribute:Number = spaceForChildren;
			var spaceUsed:Number = 0;
			var childHeight:Number;
			var wantSpace:Number;
			
			var childCount:int = children.length;
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				var config:Object = configurations[i];
				
				if(!config.includeInLayout)
				{
					continue;
				}
					
				childHeight = child.height;
				percentHeight = config.percentHeight;
				
				spaceUsed += childHeight;
				
				if(!isNaN(percentHeight))
				{
					wantSpace = Math.ceil(percentHeight/100 * spaceForChildren);
					
					if(wantSpace > childHeight)
					{
						wantToGrow = true;
					}
				}
			}
			
			// No need to distribute extra size
			if(!wantToGrow)
			{
				return;
			}
	
			// Start distributing...
			spaceToDistribute -= spaceUsed;
			
			// If we still have components that will let us 
			// distribute to them
			var stillFlexibleComponents:Boolean = true;	
			
			while(stillFlexibleComponents && spaceToDistribute > 0)
			{
				// Start optimistically
				stillFlexibleComponents = false;
				
				for(i = 0; i < childCount; i++)
				{
					child = DisplayObject(children[i]);
					config = configurations[i];
					childHeight = child.height;
					percentHeight = config.percentHeight;
					
					// if they have a percentHeight, and we won't reach their
					// maxHeight by giving them one more pixel, then 
					// give them a pixel
					if(!isNaN(percentHeight) && 
							config.includeInLayout && 
							childHeight < config.maxHeight)
					{
						wantSpace = Math.ceil(percentHeight/100 * spaceForChildren);
					
						if(wantSpace > childHeight)
						{
							child.height = childHeight + 1;
							spaceToDistribute--;
							stillFlexibleComponents = true;
							
							if(spaceToDistribute == 0)
							{
								return;
							}
						}
					}
				}
			}
		}
		
		/**
		 *  This function distributes excess space among the flexible children
		 *  because of rounding errors where we want to keep children's dimensions 
		 *  full pixel amounts.  This only distributes the extra space 
		 *  if there was some rounding down and there are still 
		 *  flexible children.
		 *
		 *  @param parent The parent container of the children.
		 * 
		 *  @param spaceForChildren The total space for all children
		 */
		public static function distributeExtraWidth(children:Array, configurations:Array, spaceForChildren:Number):void
		{
			// We should only get here after distributing the majority of the 
			// space already.  This is done in flexChildWidthsProportionally.
			// Strategy here is to keep adding 1 pixel at a time to each 
			// component that's flexible (percentWidth defined and hasn't
			// reached maxWidth yet).  We could use another approach where
			// we add more than a pixel at a time, but we'd have to first 
			// calculate exactly how many flexible components we have first
			// and see how much space we can add to them without hitting
			// their maxWidth.  Since we're just dealing with rounding 
			// issues, we should only make one pass here (if we hit maxWidth
			// problems, we might make more than one, but not many more).
			
			// We just distribute from the top-down and don't care about 
			// who was "rounded down the most"
			
			// First check if we should distribute any extra space.  To do 
			// this, we check to see if someone suffers from rounding error.
			var childCount:int = children.length;
			var wantToGrow:Boolean = false;
			var percentWidth:Number;
			var spaceToDistribute:Number = spaceForChildren;
			var spaceUsed:Number = 0;
			var childWidth:Number;	
			var wantSpace:Number;
			
			for(var i:int = 0; i < childCount; i++)
			{
				var child:DisplayObject = DisplayObject(children[i]);
				var config:Object = configurations[i];
				
				if(!config.includeInLayout)
				{
					continue;
				}
					
				childWidth = child.width;
				percentWidth = config.percentWidth;
				
				spaceUsed += childWidth;
				
				if(!isNaN(percentWidth))
				{
					wantSpace = Math.ceil(percentWidth / 100 * spaceForChildren);
					
					if(wantSpace > childWidth)
					{
						wantToGrow = true;
					}
				}
			}
			
			// No need to distribute extra size
			if(!wantToGrow)
			{
				return;
			}
	
			// Start distributing...
			spaceToDistribute -= spaceUsed;
			
			// If we still have components that will let us 
			// distribute to them
			var stillFlexibleComponents:Boolean = true;	
			
			while(stillFlexibleComponents && spaceToDistribute > 0)
			{
				// Start optimistically
				stillFlexibleComponents = false;
				
				for(i = 0; i < childCount; i++)
				{
					child = DisplayObject(children[i]);
					config = configurations[i];
					
					childWidth = child.width;
					percentWidth = config.percentWidth;
					
					// if they have a percentWidth, and we won't reach their
					// maxWidth by giving them one more pixel, then 
					// give them a pixel
					if(!isNaN(percentWidth) && config.includeInLayout && childWidth < config.maxWidth)
					{
						wantSpace = Math.ceil(percentWidth / 100 * spaceForChildren);
					
						if(wantSpace > childWidth)
						{
							child.width = childWidth + 1;
							spaceToDistribute--;
							stillFlexibleComponents = true;
							
							if(spaceToDistribute == 0)
							{
								return;
							}
						}
					}
				}
			}
		}

	}
}
	import flash.display.DisplayObject;
	

class ChildInfo
{
	public var max:Number;
	public var min:Number;
	public var child:DisplayObject;
	public var percent:Number;
	public var size:Number;
	public var opposite:Number;
}