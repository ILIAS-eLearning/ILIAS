package com.yahoo.astra.fl.charts
{
	import com.yahoo.astra.fl.charts.axes.AxisOrientation;
	import com.yahoo.astra.fl.charts.axes.AxisLabelData;
	import com.yahoo.astra.fl.charts.axes.CategoryAxis;
	import com.yahoo.astra.fl.charts.axes.DefaultAxisRenderer;
	import com.yahoo.astra.fl.charts.axes.HorizontalAxisRenderer;
	import com.yahoo.astra.fl.charts.axes.VerticalAxisRenderer;
	import com.yahoo.astra.fl.charts.axes.DefaultGridLinesRenderer;
	import com.yahoo.astra.fl.charts.axes.IAxis;
	import com.yahoo.astra.fl.charts.axes.ICartesianAxisRenderer;
	import com.yahoo.astra.fl.charts.axes.IGridLinesRenderer;
	import com.yahoo.astra.fl.charts.axes.IStackingAxis;
	import com.yahoo.astra.fl.charts.axes.NumericAxis;
	import com.yahoo.astra.fl.charts.axes.TimeAxis;
	import com.yahoo.astra.fl.charts.axes.IOriginAxis;
	import com.yahoo.astra.fl.charts.series.CartesianSeries;
	import com.yahoo.astra.fl.charts.series.ISeries;
	import com.yahoo.astra.fl.charts.series.IStackedSeries;
	import com.yahoo.astra.fl.charts.events.*;
	import com.yahoo.astra.fl.utils.UIComponentUtil;
	import com.yahoo.astra.utils.AxisLabelUtil;
	import com.yahoo.astra.display.BitmapText;
	
	import fl.core.InvalidationType;
	import fl.core.UIComponent;
	
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.geom.Point;
	import flash.geom.Rectangle;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
	import flash.text.TextFieldAutoSize;
	import flash.utils.Dictionary;
	
	//--------------------------------------
	//  Styles
	//--------------------------------------
	
	/**
	 * An object containing style values to be passed to the vertical axis
	 * renderer. The available styles are listed with the class that is used as the
	 * axis renderer.
	 * 
	 * @example
	 * <listing version="3.0">
	 * {
	 * 	showTicks: true,
	 * 	tickWeight: 1,
	 * 	tickColor: 0x999999,
	 * 	showMinorTicks: true,
	 * 	minorTickWeight: 1,
	 * 	minorTickColor: 0xcccccc
	 * }
	 * </listing>
	 * 
	 * <p><strong>Note:</strong> Previously, all styles for the axis renderers
	 * were listed as individual styles on the chart, but since it is possible
	 * to use a renderer class that has completely different styles than the
	 * default renderer, we need to deprecate the previous method to allow
	 * maximum flexibility when new or custom renderers are added.</p>
	 * 
	 * <p>The old styles still exist, and legacy code will continue to work
	 * for the time being. However, it is recommended that you begin porting
	 * code to the new system as soon as possible.</p>
	 * 
	 * <p>For the vertical axis, you should use the following method to set
	 * styles at runtime:</p>
	 * 
	 * @example
	 * <listing version="3.0">
	 * chart.setVerticalAxisStyle("showTicks", false);
	 * </listing>
	 * 
	 * @see setVerticalAxisStyle()
	 * @see com.yahoo.astra.fl.charts.axes.DefaultAxisRenderer
	 */
	[Style(name="verticalAxisStyles", type="Object")]
    
	/**
	 * The class used to instantiate the visual representation of the vertical
	 * axis.
	 * 
	 * @default VerticalAxisRenderer
	 * @see com.yahoo.astra.fl.charts.axes.VerticalAxisRenderer
	 */
	[Style(name="verticalAxisRenderer", type="Class")]
	
	/**
	 * An object containing style values to be passed to the horizontal axis
	 * renderer. The available styles are listed with the class that is used as the
	 * axis renderer.
	 * 
	 * @example
	 * <listing version="3.0">
	 * {
	 * 	showTicks: true,
	 * 	tickWeight: 1,
	 * 	tickColor: 0x999999,
	 * 	showMinorTicks: true,
	 * 	minorTickWeight: 1,
	 * 	minorTickColor: 0xcccccc
	 * }
	 * </listing>
	 * 
	 * <p><strong>Note:</strong> Previously, all styles for the grid lines
	 * renderer were listed as individual styles on the chart, but since it is
	 * possible to use a renderer class that has completely different styles
	 * than the default renderer, we need to deprecate the previous
	 * method to allow maximum flexibility when new or custom renderers are
	 * added.</p>
	 * 
	 * <p>The old styles still exist, and legacy code will continue to work
	 * for the time being. However, it is recommended that you begin porting
	 * code to the new system as soon as possible.</p>
	 * 
	 * <p>For the horizontal axis, you should use the following method to set
	 * styles at runtime:</p>
	 * 
	 * @example
	 * <listing version="3.0">
	 * chart.setHorizontalAxisStyle("showTicks", false);
	 * </listing>
	 * 
	 * @see setHorizontalAxisStyle()
	 * @see com.yahoo.astra.fl.charts.axes.DefaultAxisRenderer
	 */
	[Style(name="horizontalAxisStyles", type="Object")]
    
	/**
	 * The class used to instantiate the visual representation of the horizontal
	 * axis.
	 * 
	 * @default HorizontalAxisRenderer
	 * @see com.yahoo.astra.fl.charts.axes.HorizontalAxisRenderer
	 */
	[Style(name="horizontalAxisRenderer", type="Class")]
	
	/**
	 * An object containing style values to be passed to the vertical axis grid
	 * lines renderer. The available styles are listed with the class that is used as the
	 * grid lines renderer.
	 * 
	 * @example
	 * <listing version="3.0">
	 * {
	 * 	showLines: true,
	 * 	lineWeight: 1,
	 * 	lineColor: 0x999999,
	 * 	showMinorLines: false
	 * }
	 * </listing>
	 * 
	 * <p><strong>Note:</strong> Previously, all styles for the grid lines were listed as individual
	 * styles on the chart, but since it is possible to use a renderer class
	 * that has completely different styles, we need to deprecate the previous
	 * method to allow maximum flexibility when new or custom renderers are
	 * added.</p>
	 * 
	 * <p>The old styles still exist, and legacy code will continue to work
	 * for the time being. However, it is recommended that you begin porting
	 * code to the new system as soon as possible.</p>
	 * 
	 * <p>For the vertical axis grid lines, you should use the following method to set
	 * styles at runtime:</p>
	 * 
	 * @example
	 * <listing version="3.0">
	 * chart.setVerticalAxisGridLinesStyle("lineColor", 0x999999);
	 * </listing>
	 * 
	 * @see setVerticalAxisGridLinesStyle()
	 * @see com.yahoo.astra.fl.charts.axes.DefaultGridLinesRenderer
	 */
	[Style(name="verticalAxisGridLinesStyles", type="Object")]
    
	/**
	 * The class used to instantiate the vertical axis grid lines.
	 * 
	 * @default DefaultGridLinesRenderer
	 * @see com.yahoo.astra.fl.charts.axes.DefaultGridLinesRenderer
	 */
	[Style(name="verticalAxisGridLinesRenderer", type="Class")]
	
	/**
	 * An object containing style values to be passed to the horizontal axis grid
	 * lines renderer. The available styles are listed with the class that is used as the
	 * grid lines renderer.
	 * 
	 * @example
	 * <listing version="3.0">
	 * {
	 * 	showLines: true,
	 * 	lineWeight: 1,
	 * 	lineColor: 0x999999,
	 * 	showMinorLines: false
	 * }
	 * </listing>
	 * 
	 * <p><strong>Note:</strong> Previously, all styles for the grid lines were listed as individual
	 * styles on the chart, but since it is possible to use a renderer class
	 * that has completely different styles, we need to deprecate the previous
	 * method to allow maximum flexibility when new or custom renderers are
	 * added.</p>
	 * 
	 * <p>The old styles still exist, and legacy code will continue to work
	 * for the time being. However, it is recommended that you begin porting
	 * code to the new system as soon as possible.</p>
	 * 
	 * <p>For the horizontal axis grid lines, you should use the following method to set
	 * styles at runtime:</p>
	 * 
	 * @example
	 * <listing version="3.0">
	 * chart.setHorizontalAxisGridLinesStyle("lineColor", 0x999999);
	 * </listing>
	 * 
	 * @see setHorizontalAxisGridLinesStyle()
	 * @see com.yahoo.astra.fl.charts.axes.DefaultGridLinesRenderer
	 */
	[Style(name="horizontalAxisGridLinesStyles", type="Object")]
    
	/**
	 * The class used to instantiate the horizontal axis grid lines.
	 * 
	 * @default DefaultGridLinesRenderer
	 */
	[Style(name="horizontalAxisGridLinesRenderer", type="Class")]
	
	//-- DEPRECATED Vertical Axis styles
    
	/**
	 * If false, the vertical axis is not drawn. Titles, labels, ticks, and grid
	 * lines may still be drawn, however, so you must specifically hide each
	 * item if nothing should be drawn.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showVerticalAxis", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the vertical axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="verticalAxisWeight", type="int")]
    
	/**
	 * The line color for the vertical axis.
	 * 
	 * @default #888a85
	 * @deprecated
	 */
	[Style(name="verticalAxisColor", type="uint")]
    
    //-- Labels - Vertical Axis
    
	/**
	 * If true, labels will be displayed on the vertical axis.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showVerticalAxisLabels", type="Boolean")]
    
	/**
	 * The distance, in pixels, between a label and the vertical axis.
	 * 
	 * @default 2
	 * @deprecated
	 */
	[Style(name="verticalAxisLabelDistance", type="Number")]
    
	/**
	 * Defines the TextFormat used by labels on the vertical axis. If null,
	 * the <code>textFormat</code> style will be used.
	 * 
	 * @default null
	 * @deprecated
	 */
	[Style(name="verticalAxisTextFormat", type="TextFormat")]
    
	/** 
	 * If true, labels that overlap previously drawn labels on the axis will be
	 * hidden. The first and last labels on the axis will always be drawn.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="verticalAxisHideOverlappingLabels", type="Boolean")]
    
	/** 
	 * The angle, in degrees, of the labels on the vertical axis. May be a value
	 * between <code>-90</code> and <code>90</code>. The font must be embedded
	 * in the SWF and the <code>embedFonts</code> style on the chart must be set
	 * to <code>true</code> before labels may be rotated. If these conditions
	 * aren't met, the labels will not be rotated.
	 * 
	 * @default 0
	 * @deprecated
	 */
	[Style(name="verticalAxisLabelRotation", type="Number")]
    
    //-- Grid - Vertical Axis
    
	/**
	 * An Array of <code>uint</code> color values that is used to draw
	 * alternating fills between the vertical axis' grid lines.
	 * 
	 * @default []
	 * @deprecated
	 */
	[Style(name="verticalAxisGridFillColors", type="Array")]
    
	/**
	 * An Array of alpha values (in the range of 0 to 1) that is used to draw
	 * alternating fills between the vertical axis' grid lines.
	 * 
	 * @default []
	 * @deprecated
	 */
	[Style(name="verticalAxisGridFillAlphas", type="Array")]
    
    //-- DEPRECATED Grid Lines styles - Vertical Axis
    
	/**
	 * If true, grid lines will be displayed on the vertical axis.
	 * 
	 * @default false
	 * @deprecated
	 */
	[Style(name="showVerticalAxisGridLines", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the grid lines on the vertical axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="verticalAxisGridLineWeight", type="int")]
    
	/**
	 * The line color for the grid lines on the vertical axis.
	 * 
	 * @default #babdb6
	 * @deprecated
	 */
	[Style(name="verticalAxisGridLineColor", type="uint")]
    
    //-- Minor Grid Lines - Vertical Axis
    
	/**
	 * If true, minor grid lines will be displayed on the vertical axis.
	 * 
	 * @default false
	 * @deprecated
	 */
	[Style(name="showVerticalAxisMinorGridLines", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the minor grid lines on the vertical axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="verticalAxisMinorGridLineWeight", type="int")]
    
	/**
	 * The line color for the minor grid lines on the vertical axis.
	 * 
	 * @default #eeeeec
	 * @deprecated
	 */
	[Style(name="verticalAxisMinorGridLineColor", type="uint")]
    
	//-- Ticks - Vertical Axis
    
	/**
	 * If true, ticks will be displayed on the vertical axis.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showVerticalAxisTicks", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the ticks on the vertical axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="verticalAxisTickWeight", type="int")]
    
	/**
	 * The line color for the ticks on the vertical axis.
	 * 
	 * @default #888a85
	 * @deprecated
	 */
	[Style(name="verticalAxisTickColor", type="uint")]
    
	/**
	 * The length, in pixels, of the ticks on the vertical axis.
	 * 
	 * @default 4
	 * @deprecated
	 */
	[Style(name="verticalAxisTickLength", type="Number")]
	
	/**
	 * The position of the ticks on the vertical axis.
	 * 
	 * @default "cross"
	 * @see com.yahoo.astra.fl.charts.axes.TickPosition
	 * @deprecated
	 */
	[Style(name="verticalAxisTickPosition", type="String")]
    
    //-- Minor ticks - Vertical Axis
    
	/**
	 * If true, ticks will be displayed on the vertical axis at minor positions.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showVerticalAxisMinorTicks", type="Boolean")]
	
	/**
	 * The line weight, in pixels, for the minor ticks on the vertical axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="verticalAxisMinorTickWeight", type="int")]
    
	/**
	 * The line color for the minor ticks on the vertical axis.
	 * 
	 * @default #888a85
	 * @deprecated
	 */
	[Style(name="verticalAxisMinorTickColor", type="uint")]
    
	/**
	 * The length of the minor ticks on the vertical axis.
	 * 
	 * @default 3
	 * @deprecated
	 */
	[Style(name="verticalAxisMinorTickLength", type="Number")]
	
	/**
	 * The position of the minor ticks on the vertical axis.
	 * 
	 * @default "outside"
	 * @see com.yahoo.astra.fl.charts.axes.TickPosition
	 * @deprecated
	 */
	[Style(name="verticalAxisMinorTickPosition", type="String")]
	
	//-- Title - Vertical Axis
	
	/**
	 * If true, the vertical axis title will be displayed.
	 * 
	 * @default 2
	 * @deprecated
	 */
	[Style(name="showVerticalAxisTitle", type="Boolean")]
	
	/**
	 * The TextFormat object to use to render the vertical axis title label.
     *
     * @default TextFormat("_sans", 11, 0x000000, false, false, false, '', '', TextFormatAlign.LEFT, 0, 0, 0, 0)
	 * @deprecated
	 */
	[Style(name="verticalAxisTitleTextFormat", type="TextFormat")]
	
	//-- DEPRECATED Horizontal Axis styles
    
	/**
	 * If false, the horizontal axis is not drawn. Titles, labels, ticks, and grid
	 * lines may still be drawn, however, so you must specifically hide each
	 * item if nothing should be drawn.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showHorizontalAxis", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the horizontal axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="horizontalAxisWeight", type="int")]
    
	/**
	 * The line color for the horizontal axis.
	 * 
	 * @default #888a85
	 * @deprecated
	 */
	[Style(name="horizontalAxisColor", type="uint")]
    
    //-- Labels - Horizontal Axis
    
	/**
	 * If true, labels will be displayed on the horizontal axis.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showHorizontalAxisLabels", type="Boolean")]
    
	/**
	 * The distance, in pixels, between a label and the horizontal axis.
	 * 
	 * @default 2
	 * @deprecated
	 */
	[Style(name="horizontalAxisLabelDistance", type="Number")]
    
	/**
	 * Defines the TextFormat used by labels on the horizontal axis. If null,
	 * the <code>textFormat</code> style will be used.
	 * 
	 * @default null
	 * @deprecated
	 */
	[Style(name="horizontalAxisTextFormat", type="TextFormat")]
    
	/** 
	 * If true, labels that overlap previously drawn labels on the axis will be
	 * hidden. The first and last labels on the axis will always be drawn.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="horizontalAxisHideOverlappingLabels", type="Boolean")]
    
	/** 
	 * The angle, in degrees, of the labels on the horizontal axis. May be a value
	 * between <code>-90</code> and <code>90</code>. The font must be embedded
	 * in the SWF and the <code>embedFonts</code> style on the chart must be set
	 * to <code>true</code> before labels may be rotated. If these conditions
	 * aren't met, the labels will not be rotated.
	 * 
	 * @default 0
	 * @deprecated
	 */
	[Style(name="horizontalAxisLabelRotation", type="Number")]
    
    //-- Grid - Horizontal Axis
    
	/**
	 * An Array of <code>uint</code> color values that is used to draw
	 * alternating fills between the horizontal axis' grid lines.
	 * 
	 * @default []
	 * @deprecated
	 */
	[Style(name="horizontalAxisGridFillColors", type="Array")]
    
	/**
	 * An Array of alpha values (in the range of 0 to 1) that is used to draw
	 * alternating fills between the horizontal axis' grid lines.
	 * 
	 * @default []
	 * @deprecated
	 */
	[Style(name="horizontalAxisGridFillAlphas", type="Array")]
    
    //-- DEPRECATED Grid Lines - Horizontal Axis
    
	/**
	 * If true, grid lines will be displayed on the horizontal axis.
	 * 
	 * @default false
	 * @deprecated
	 */
	[Style(name="showHorizontalAxisGridLines", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the grid lines on the horizontal axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="horizontalAxisGridLineWeight", type="int")]
    
	/**
	 * The line color for the grid lines on the horizontal axis.
	 * 
	 * @default #babdb6
	 * @deprecated
	 */
	[Style(name="horizontalAxisGridLineColor", type="uint")]
    
    //-- Minor Grid Lines - Horizontal Axis
    
	/**
	 * If true, minor grid lines will be displayed on the horizontal axis.
	 * 
	 * @default false
	 * @deprecated
	 */
	[Style(name="showHorizontalAxisMinorGridLines", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the minor grid lines on the horizontal axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="horizontalAxisMinorGridLineWeight", type="int")]
    
	/**
	 * The line color for the minor grid lines on the horizontal axis.
	 * 
	 * @default #eeeeec
	 * @deprecated
	 */
	[Style(name="horizontalAxisMinorGridLineColor", type="uint")]
    
	//-- Ticks - Horizontal Axis
    
	/**
	 * If true, ticks will be displayed on the horizontal axis.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showHorizontalAxisTicks", type="Boolean")]
    
	/**
	 * The line weight, in pixels, for the ticks on the horizontal axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="horizontalAxisTickWeight", type="int")]
    
	/**
	 * The line color for the ticks on the horizontal axis.
	 * 
	 * @default #888a85
	 * @deprecated
	 */
	[Style(name="horizontalAxisTickColor", type="uint")]
    
	/**
	 * The length, in pixels, of the ticks on the horizontal axis.
	 * 
	 * @default 4
	 * @deprecated
	 */
	[Style(name="horizontalAxisTickLength", type="Number")]
	
	/**
	 * The position of the ticks on the horizontal axis.
	 * 
	 * @default "cross"
	 * @see com.yahoo.astra.fl.charts.axes.TickPosition
	 * @deprecated
	 */
	[Style(name="horizontalAxisTickPosition", type="String")]
    
    //-- Minor ticks - Horizontal Axis
    
	/**
	 * If true, ticks will be displayed on the horizontal axis at minor positions.
	 * 
	 * @default true
	 * @deprecated
	 */
	[Style(name="showHorizontalAxisMinorTicks", type="Boolean")]
	
	/**
	 * The line weight, in pixels, for the minor ticks on the horizontal axis.
	 * 
	 * @default 1
	 * @deprecated
	 */
	[Style(name="horizontalAxisMinorTickWeight", type="int")]
    
	/**
	 * The line color for the minor ticks on the horizontal axis.
	 * 
	 * @default #888a85
	 * @deprecated
	 */
	[Style(name="horizontalAxisMinorTickColor", type="uint")]
    
	/**
	 * The length of the minor ticks on the horizontal axis.
	 * 
	 * @default 3
	 * @deprecated
	 */
	[Style(name="horizontalAxisMinorTickLength", type="Number")]
	
	/**
	 * The position of the minor ticks on the horizontal axis.
	 * 
	 * @default "outside"
	 * @see com.yahoo.astra.fl.charts.axes.TickPosition
	 * @deprecated
	 */
	[Style(name="horizontalAxisMinorTickPosition", type="String")]
	
	//-- Title - Horizontal Axis
	
	/**
	 * If true, the horizontal axis title will be displayed.
	 * 
	 * @default 2
	 * @deprecated
	 */
	[Style(name="showHorizontalAxisTitle", type="Boolean")]
	
	/**
	 * The TextFormat object to use to render the horizontal axis title label.
     *
     * @default TextFormat("_sans", 11, 0x000000, false, false, false, '', '', TextFormatAlign.LEFT, 0, 0, 0, 0)
	 * @deprecated
	 */
	[Style(name="horizontalAxisTitleTextFormat", type="TextFormat")]
	
	/**
	 * The border color of the markers in a series. When not specified, the border color 
	 * is determined by the color style. 
	 * 
	 * @default []
	 */
	[Style(name="seriesBorderColors", type="Array")]
	
	/** 
	 * The border alpha of the markers in a series. 
	 * 
	 * @default [1]
	 */
	[Style(name="seriesBorderAlphas", type="Array")]
	
	/** 
	 * The fill color of the markers in a series. When not specified, the fill color
	 * is determined by the color style.
	 *
	 * @default []
	 */
	[Style(name="seriesFillColors", type="Array")]
	
	/** 
	 * The fill alpha of the markers in a series. 
	 *
	 * @default [1]
	 */
	[Style(name="seriesFillAlphas", type="Array")]
	
	/**
	 * A chart based on the cartesian coordinate system (x, y).
	 * 
	 * @author Josh Tynjala
	 */
	public class CartesianChart extends Chart implements IChart, ICategoryChart
	{
		
	//--------------------------------------
	//  Class Variables
	//--------------------------------------
	
		/**
		 * @private
		 * Exists simply to reference dependencies that aren't used
		 * anywhere else by this component.
		 */
		private static const DEPENDENCIES:Array = [TimeAxis];
	
		/**
		 * @private
		 */
		private static var defaultStyles:Object = 
		{
			horizontalAxisStyles: {},
			secondaryHorizontalAxisStyles: {},
			horizontalAxisGridLinesStyles: {},
			horizontalAxisRenderer: HorizontalAxisRenderer,
			secondaryHorizontalAxisRenderer: HorizontalAxisRenderer,
			secondaryVerticalAxisRenderer: VerticalAxisRenderer,
			horizontalAxisGridLinesRenderer: DefaultGridLinesRenderer,
			
			verticalAxisStyles: {},
			secondaryVerticalAxisStyles: {},
			verticalAxisGridLinesStyles: {},
			verticalAxisRenderer: VerticalAxisRenderer,
			verticalAxisGridLinesRenderer: DefaultGridLinesRenderer,
			
			//DEPRECATED BELOW THIS POINT!
			//(to be removed in a future version)
			
			//axis
			showHorizontalAxis: true,
			horizontalAxisWeight: 1,
			horizontalAxisColor: 0x888a85,
			
			//title
			showHorizontalAxisTitle: true,
			horizontalAxisTitleTextFormat: new TextFormat("_sans", 11, 0x000000, false, false, false, "", "", TextFormatAlign.LEFT, 0, 0, 0, 0),
			horizontalAxisTitleRotation: 0,
			horizontalAxisTitleDistance: 2,
			
			//labels
			showHorizontalAxisLabels: true,
			horizontalAxisTextFormat: null,
			horizontalAxisLabelDistance: 2,
			horizontalAxisHideOverlappingLabels: true,
			horizontalAxisLabelRotation: 0,
			horizontalAxisLabelSpacing: 2,
			
			//grid lines
			horizontalAxisGridLineWeight: 1,
			horizontalAxisGridLineColor: 0xbabdb6,
			showHorizontalAxisGridLines: false,
			horizontalAxisMinorGridLineWeight: 1,
			horizontalAxisMinorGridLineColor: 0xeeeeec,
			showHorizontalAxisMinorGridLines: false,
			horizontalAxisGridFillColors: [],
			horizontalAxisGridFillAlphas: [],
			showHorizontalZeroGridLine: false,
			horizontalZeroGridLineWeight: 2,
			horizontalZeroGridLineColor: 0xbabdb6,				
			
			//ticks
			showHorizontalAxisTicks: false,
			horizontalAxisTickWeight: 1,
			horizontalAxisTickColor: 0x888a85,
			horizontalAxisTickLength: 4,
			horizontalAxisTickPosition: "cross",
			showHorizontalAxisMinorTicks: false,
			horizontalAxisMinorTickWeight: 1,
			horizontalAxisMinorTickColor: 0x888a85,
			horizontalAxisMinorTickLength: 3,
			horizontalAxisMinorTickPosition: "outside",
			
			//axis
			showVerticalAxis: true,
			verticalAxisWeight: 1,
			verticalAxisColor: 0x888a85,
			
			//title
			showVerticalAxisTitle: true,
			verticalAxisTitleTextFormat: new TextFormat("_sans", 11, 0x000000, false, false, false, "", "", TextFormatAlign.LEFT, 0, 0, 0, 0),
			verticalAxisTitleRotation: 0,
			verticalAxisTitleDistance: 2,
			
			//labels
			showVerticalAxisLabels: true,
			verticalAxisTextFormat: null,
			verticalAxisLabelDistance: 2,
			verticalAxisHideOverlappingLabels: true,
			verticalAxisLabelRotation: 0,
			verticalAxisLabelSpacing: 2,
			
			//grid lines
			showVerticalAxisGridLines: true,
			verticalAxisGridLineWeight: 1,
			verticalAxisGridLineColor: 0xbabdb6,
			verticalAxisMinorGridLineWeight: 1,
			verticalAxisMinorGridLineColor: 0xeeeeec,
			showVerticalAxisMinorGridLines: false,
			verticalAxisGridFillColors: [],
			verticalAxisGridFillAlphas: [],
			showVerticalZeroGridLine: false,
			verticalZeroGridLineWeight: 2,
			verticalZeroGridLineColor: 0xbabdb6,			
			
			//ticks
			showVerticalAxisTicks: true,
			verticalAxisTickWeight: 1,
			verticalAxisTickColor: 0x888a85,
			verticalAxisTickLength: 4,
			verticalAxisTickPosition: "cross",
			showVerticalAxisMinorTicks: true,
			verticalAxisMinorTickWeight: 1,
			verticalAxisMinorTickColor: 0x888a85,
			verticalAxisMinorTickLength: 3,
			verticalAxisMinorTickPosition: "outside"
		};
		
		/**
		 * @private
		 * The chart styles that correspond to styles on the horizontal axis.
		 */
		private static const HORIZONTAL_AXIS_STYLES:Object = 
		{
			showAxis: "showHorizontalAxis",
			axisWeight: "horizontalAxisWeight",
			axisColor: "horizontalAxisColor",
			
			textFormat: "textFormat",
			embedFonts: "embedFonts",
			hideOverlappingLabels: "horizontalAxisHideOverlappingLabels",
			labelRotation: "horizontalAxisLabelRotation",
			labelDistance: "horizontalAxisLabelDistance",
			showLabels: "showHorizontalAxisLabels",
			labelSpacing: "horizontalAxisLabelSpacing",
			titleRotation: "horizontalAxisTitleRotation", 
			titleDistance: "horizontalAxisTitleDistance",
			
			showTitle: "showHorizontalAxisTitle",
			titleTextFormat: "horizontalAxisTitleTextFormat",
			
			tickWeight: "horizontalAxisTickWeight",
			tickColor: "horizontalAxisTickColor",
			tickLength: "horizontalAxisTickLength",
			tickPosition: "horizontalAxisTickPosition",
			showTicks: "showHorizontalAxisTicks",

			minorTickWeight: "horizontalAxisMinorTickWeight",
			minorTickColor: "horizontalAxisMinorTickColor",
			minorTickLength: "horizontalAxisMinorTickLength",
			minorTickPosition: "horizontalAxisMinorTickPosition",
			showMinorTicks: "showHorizontalAxisMinorTicks"
		};
		
		/**
		 * @private
		 * The chart styles that correspond to styles on the horizontal axis
		 * grid lines.
		 */
		private static const HORIZONTAL_GRID_LINES_STYLES:Object =
		{
			lineWeight: "horizontalAxisGridLineWeight",
			lineColor: "horizontalAxisGridLineColor",
			showLines: "showHorizontalAxisGridLines",
			
			minorLineWeight: "horizontalAxisMinorGridLineWeight",
			minorLineColor: "horizontalAxisMinorGridLineColor",
			showMinorLines: "showHorizontalAxisMinorGridLines",
			
			showZeroGridLine: "showHorizontalZeroGridLine",
			zeroGridLineWeight: "horizontalZeroGridLineWeight",
			zeroGridLineColor: "horizontalZeroGridLineColor", 
			
			fillColors: "horizontalAxisGridFillColors",
			fillAlphas: "horizontalAxisGridFillAlphas"
		}
		
		/**
		 * @private
		 * The chart styles that correspond to styles on the vertical axis.
		 */
		private static const VERTICAL_AXIS_STYLES:Object = 
		{
			showAxis: "showVerticalAxis",
			axisWeight: "verticalAxisWeight",
			axisColor: "verticalAxisColor",
			
			textFormat: "textFormat",
			embedFonts: "embedFonts",
			hideOverlappingLabels: "verticalAxisHideOverlappingLabels",
			labelRotation: "verticalAxisLabelRotation",
			labelDistance: "verticalAxisLabelDistance",
			showLabels: "showVerticalAxisLabels",
			labelSpacing: "verticalAxisLabelSpacing",
			titleRotation: "verticalAxisTitleRotation", 
			titleDistance: "verticalAxisTitleDistance",
			
			showTitle: "showVerticalAxisTitle",
			titleTextFormat: "verticalAxisTitleTextFormat",
			
			tickWeight: "verticalAxisTickWeight",
			tickColor: "verticalAxisTickColor",
			tickLength: "verticalAxisTickLength",
			tickPosition: "verticalAxisTickPosition",
			showTicks: "showVerticalAxisTicks",
			
			minorTickWeight: "verticalAxisMinorTickWeight",
			minorTickColor: "verticalAxisMinorTickColor",
			minorTickLength: "verticalAxisMinorTickLength",
			minorTickPosition: "verticalAxisMinorTickPosition",
			showMinorTicks: "showVerticalAxisMinorTicks"
		};
		
		/**
		 * @private
		 * The chart styles that correspond to styles on the vertical axis
		 * grid lines.
		 */
		private static const VERTICAL_GRID_LINES_STYLES:Object =
		{
			lineWeight: "verticalAxisGridLineWeight",
			lineColor: "verticalAxisGridLineColor",
			showLines: "showVerticalAxisGridLines",
			
			minorLineWeight: "verticalAxisMinorGridLineWeight",
			minorLineColor: "verticalAxisMinorGridLineColor",
			showMinorLines: "showVerticalAxisMinorGridLines",
			
			showZeroGridLine: "showVerticalZeroGridLine",
			zeroGridLineWeight: "verticalZeroGridLineWeight",
			zeroGridLineColor: "verticalZeroGridLineColor",			
			
			fillColors: "verticalAxisGridFillColors",
			fillAlphas: "verticalAxisGridFillAlphas"
		}
		
	//--------------------------------------
	//  Class Methods
	//--------------------------------------
	
		/**
		 * @private
		 * @copy fl.core.UIComponent#getStyleDefinition()
		 */
		public static function getStyleDefinition():Object
		{
			return mergeStyles(defaultStyles, Chart.getStyleDefinition());
		}
		
	//--------------------------------------
	//  Constructor
	//--------------------------------------
	
		/**
		 * Constructor.
		 */
		public function CartesianChart()
		{
			super();
		}
		
	//--------------------------------------
	//  Properties
	//--------------------------------------
		
		/**
		 * @private
		 * Storage for the contentBounds property.
		 */
		protected var _contentBounds:Rectangle = new Rectangle();
	
		/**
		 * The rectangular bounds where the cartesian chart's data is drawn.
		 */
		public function get contentBounds():Rectangle
		{
			return this._contentBounds;
		}
		
		/**
		 * @private (protected)
		 * Storage for the axisLayer property.
		 */
		protected var _axisLayer:Sprite = new Sprite();		
		
		/**
		 * Container for all axis elements of the chart.
		 */
		public function get axisLayer():Sprite
		{
			return this._axisLayer;
		}
		
		/**
		 * @private
		 */
		protected var horizontalGridLines:IGridLinesRenderer;
		
		/**
		 * @private
		 */
		protected var verticalGridLines:IGridLinesRenderer;
		
		/**
		 * @private
		 */
		protected var verticalMinorGridLines:Sprite;
		
		/**
		 * @private
		 * The visual representation of the horizontal axis.
		 */
		protected var horizontalAxisRenderer:ICartesianAxisRenderer;
		
		/**
		 * @private 
		 * The visual representation of the secondary horizontal axis.
		 */
		protected var secondaryHorizontalAxisRenderer:ICartesianAxisRenderer;
		
		/**
		 * @private
		 * Storage for the horizontalAxis property.
		 */
		private var _horizontalAxis:IAxis;
		
		/**
		 * The axis representing the horizontal range.
		 */
		public function get horizontalAxis():IAxis
		{
			return this._horizontalAxis;
		}
		
		/**
		 * @private
		 */
		public function set horizontalAxis(axis:IAxis):void
		{
			if(axis.position != "bottom" && axis.position != "top") axis.position = "bottom";
			axis.addEventListener(AxisEvent.AXIS_FAILED, recalculateChart);
			if(this._secondaryHorizontalAxis != axis && axis is NumericAxis && (axis as NumericAxis).order == "secondary")
			{
				this.secondaryHorizontalAxis = axis;
			}
			else if(this._horizontalAxis != axis)
			{
				this._horizontalAxis = axis;
				this._horizontalAxis.chart = this;
				if(this._horizontalAxis is NumericAxis) 
				{
					(this._horizontalAxis as NumericAxis).order = "primary";
				}
				this.invalidate("axes");
			}
		}
		
		/**
		 * @private
		 * Storage for the horizontalAxis property.
		 */
		private var _secondaryHorizontalAxis:IAxis;
		
		/**
		 * The axis representing the horizontal range.
		 */
		public function get secondaryHorizontalAxis():IAxis
		{
			return this._secondaryHorizontalAxis;
		}
		
		/**
		 * @private
		 */
		public function set secondaryHorizontalAxis(axis:IAxis):void
		{
			if(axis.position != "bottom" && axis.position != "top") axis.position = "bottom";
			axis.addEventListener(AxisEvent.AXIS_FAILED, recalculateChart);
			if(this._secondaryHorizontalAxis != axis)
			{
				this._secondaryHorizontalAxis = axis;
				this._secondaryHorizontalAxis.chart = this;
				if(this._secondaryHorizontalAxis is NumericAxis) 
				{
					(this._secondaryHorizontalAxis as NumericAxis).order = "secondary";		
				}
				this.invalidate("axes");
			}
		}		
		
		/**
		 * @private
		 * The visual representation of the vertical axis.
		 */
		protected var verticalAxisRenderer:ICartesianAxisRenderer;
		
		/** 
		 * @private 
		 * The visual representation of the secondary vertical axis.
		 */
		protected var secondaryVerticalAxisRenderer:ICartesianAxisRenderer;
		
		/**
		 * @private
		 * Storage for the verticalAxis property.
		 */
		private var _verticalAxis:IAxis;
		
		/**
		 * The axis representing the vertical range.
		 */
		public function get verticalAxis():IAxis
		{
			return this._verticalAxis;
		}
		
		/**
		 * @private
		 */
		public function set verticalAxis(axis:IAxis):void
		{
			if(axis.position != "left" && axis.position != "right") axis.position = "left";
			axis.addEventListener(AxisEvent.AXIS_FAILED, recalculateChart);
			if(this._verticalAxis != axis && axis is NumericAxis && (axis as NumericAxis).order == "secondary")
			{
				this.secondaryVerticalAxis = axis;
			}
			else if(this._verticalAxis != axis)
			{
				this._verticalAxis = axis;
				this._verticalAxis.chart = this;
				if(this._verticalAxis is NumericAxis)
				{
					(this._verticalAxis as NumericAxis).order = "primary";
				}
				this.invalidate("axes");
			}
		}
	
		/**
		 * @private
		 * Storage for the verticalAxis property.
		 */
		private var _secondaryVerticalAxis:IAxis;
		
		/**
		 * The axis representing the vertical range.
		 */
		public function get secondaryVerticalAxis():IAxis
		{
			return this._secondaryVerticalAxis;
		}
	
		/**
		 * @private
		 */
		public function set secondaryVerticalAxis(axis:IAxis):void
		{
			if(axis.position != "left" && axis.position != "right") axis.position = "left";
			axis.addEventListener(AxisEvent.AXIS_FAILED, recalculateChart);
			if(this._secondaryVerticalAxis != axis)
			{
				this._secondaryVerticalAxis = axis;
				this._secondaryVerticalAxis.chart = this;
				if(this._secondaryVerticalAxis is NumericAxis)
				{
					(this._secondaryVerticalAxis as NumericAxis).order = "secondary";
				}
				this.invalidate("axes");
			}
		}	
		
		/**
		 * @private (protected)
		 * Contains all horizontal axes used in chart
		 */
		protected var _horizontalAxes:Array = [];
		
		/**
		 * @private (protected)
		 * Contains all vertical axes used in the chart
		 */
		protected var _verticalAxes:Array = [];

	//-- Data
		
		/**
		 * @private
		 * Storage for the horizontalField property.
		 */
		private var _horizontalField:String = "category";
		
		[Inspectable(defaultValue="category",verbose=1)]
		/**
		 * If the items displayed on the chart are complex objects, the horizontalField string
		 * defines the property to access when determining the x value.
		 */
		public function get horizontalField():String
		{
			return this._horizontalField;
		}
		
		/**
		 * @private
		 */
		public function set horizontalField(value:String):void
		{
			if(this._horizontalField != value)
			{
				this._horizontalField = value;
				this.invalidate(InvalidationType.DATA);
			}
		}
		
		/**
		 * @private
		 * Storage for the verticalField property.
		 */
		private var _verticalField:String = "value";
		
		[Inspectable(defaultValue="value",verbose=1)]
		/**
		 * If the items displayed on the chart are complex objects, the verticalField string
		 * defines the property to access when determining the y value.
		 */
		public function get verticalField():String
		{
			return this._verticalField;
		}
		
		/**
		 * @private
		 */
		public function set verticalField(value:String):void
		{
			if(this._verticalField != value)
			{
				this._verticalField = value;
				this.invalidate(InvalidationType.DATA);
			}
		}
		
		
	//-- Titles
		
		/**
		 * @private
		 * Storage for the horizontalAxisTitle property.
		 */
		private var _horizontalAxisTitle:String = "";
		
		[Inspectable(defaultValue="")]
		/**
		 * The title text displayed on the horizontal axis.
		 */
		public function get horizontalAxisTitle():String
		{
			return this._horizontalAxisTitle;
		}
		
		/**
		 * @private
		 */
		public function set horizontalAxisTitle(value:String):void
		{
			if(this._horizontalAxisTitle != value)
			{
				this._horizontalAxisTitle = value;
				this.invalidate(InvalidationType.DATA);
				this.invalidate("axes");
			}
		}
		
		/**
		 * @private
		 * Storage for the verticalAxisTitle property.
		 */
		private var _verticalAxisTitle:String = "";
		
		[Inspectable(defaultValue="")]
		/**
		 * The title text displayed on the horizontal axis.
		 */
		public function get verticalAxisTitle():String
		{
			return this._verticalAxisTitle;
		}
		
		/**
		 * @private
		 */
		public function set verticalAxisTitle(value:String):void
		{
			if(this._verticalAxisTitle != value)
			{
				this._verticalAxisTitle = value;
				this.invalidate(InvalidationType.DATA);
				this.invalidate("axes");
			}
		}
		
	//-- Category names
		
		/**
		 * @private
		 * Storage for the categoryNames property.
		 */
		private var _explicitCategoryNames:Array;
		
		[Inspectable]
		/**
		 * The names of the categories displayed on the category axis. If the
		 * chart does not have a category axis, this value will be ignored.
		 */
		public function get categoryNames():Array
		{
			if(this._explicitCategoryNames && this._explicitCategoryNames.length > 0)
			{
				return this._explicitCategoryNames;
			}
			else if(this.horizontalAxis is CategoryAxis)
			{
				return CategoryAxis(this.horizontalAxis).categoryNames;
			}
			else if(this.verticalAxis is CategoryAxis)
			{
				return CategoryAxis(this.verticalAxis).categoryNames;
			}
			return null;
		}
		
		/**
		 * @private
		 */
		public function set categoryNames(value:Array):void
		{
			if(this._explicitCategoryNames != value)
			{
				this._explicitCategoryNames = value;
				this.invalidate(InvalidationType.DATA);
				this.invalidate("axes");
			}
		}
		
		/**
		 * @private
		 * Storage for the overflowEnabled property.
		 */
		private var _overflowEnabled:Boolean = false;
		
		[Inspectable(defaultValue=false,verbose=1)]
		/**
		 * If false, which is the default, the axes will be resized to fit within the defined
		 * bounds of the plot area. However, if set to true, the axes themselves will grow to
		 * fit the plot area bounds and the labels and other items that normally cause the
		 * resize will be drawn outside.
		 */
		public function get overflowEnabled():Boolean
		{
			return this._overflowEnabled;
		}
		
		/**
		 * @private
		 */
		public function set overflowEnabled(value:Boolean):void
		{
			if(this._overflowEnabled != value)
			{
				this._overflowEnabled = value;
				this.invalidate("axes");
			}
		}
		
		/**
		 * @private
		 * Storage for constrainViewport property.
		 */
		private var _constrainViewport:Boolean = true;
		
		/**
		 * Determines whether a scrollRect is set on a series to constrain the viewport
		 */
		public function get constrainViewport():Boolean
		{
			return this._constrainViewport;
		}
		
		/**
		 * @private (setter)
		 */
		public function set constrainViewport(value:Boolean):void
		{
			this._constrainViewport = value;
		}
		
		/**
		 * @private
		 * Storage for recalculations
		 */
		private var _recalculations:int = 0;
		
		/**
		 * Number of times label width is recalculated for all axes
		 */
		public function get recalculations():int
		{
			return _recalculations;
		}
		
		/**
		 * @private (setter)
		 */
		public function set recalculations(value:int):void
		{
			_recalculations = value;
		}

	//--------------------------------------
	//  Public Methods
	//--------------------------------------
	
		/**
		 * @inheritDoc
		 */
		public function itemToPosition(series:ISeries, itemIndex:int):Point
		{
			var hAxis:IAxis = this.horizontalAxis;
			var vAxis:IAxis = this.verticalAxis;
			if(series is CartesianSeries && CartesianSeries(series).axis == "secondary")
			{
				if(this.horizontalAxis is IOriginAxis && this.secondaryHorizontalAxis != null) hAxis = this.secondaryHorizontalAxis;
				if(this.verticalAxis is IOriginAxis && this.secondaryVerticalAxis != null) vAxis = this.secondaryVerticalAxis;
			}
			var horizontalValue:Object = this.itemToAxisValue(series, itemIndex, hAxis);
			var xPosition:Number = hAxis.valueToLocal(horizontalValue);
			
			var verticalValue:Object = this.itemToAxisValue(series, itemIndex, vAxis);
			var yPosition:Number = vAxis.valueToLocal(verticalValue);
			
			return new Point(xPosition, yPosition);
		}
		
		/**
		 * @private
		 */
		public function itemToAxisValue(series:ISeries, itemIndex:int, axis:IAxis, stack:Boolean = true):Object
		{
			if(!stack || !ChartUtil.isStackingAllowed(axis, series))
			{
				var item:Object = series.dataProvider[itemIndex];
				var valueField:String = this.axisAndSeriesToField(axis, series);
				return item[valueField];
			}
			
			var type:Class = UIComponentUtil.getClassDefinition(series);
			var stackAxis:IStackingAxis = IStackingAxis(axis);
			var stackValue:Object;
			var allSeriesOfType:Array = ChartUtil.findSeriesOfType(series, this);
			var seriesIndex:int = allSeriesOfType.indexOf(series);
			var values:Array = [];
			for(var i:int = 0; i <= seriesIndex; i++)
			{
				var stackedSeries:IStackedSeries = IStackedSeries(allSeriesOfType[i]);
				item = stackedSeries.dataProvider[itemIndex];
				valueField = this.axisAndSeriesToField(stackAxis, stackedSeries);
				values.unshift(item[valueField]);
			}
			
			if(values.length > 0) stackValue = stackAxis.stack.apply(stackAxis, values);
			return stackValue;
		}
		
		/**
		 * Used to retrieve an axis style
		 */
		public function getAxisStyle(axisName:String, name:String):Object
		{
			var obj:Object = this.getStyleValue(axisName + "AxisStyles");
			var style:Object;
			if(obj[name] != null)
			{
				style =  obj[name];
			}
			else
			{
				var defaultStyles:Object = (axisName).toLowerCase().indexOf("horizontal") > -1 ? HORIZONTAL_AXIS_STYLES : VERTICAL_AXIS_STYLES;
				style = this.getStyleValue(defaultStyles[name]);
			}
			return style;
		}
				
		/**
		 * Sets a style on the horizontal axis grid lines.
		 */
		public function setHorizontalAxisGridLinesStyle(name:String, value:Object):void
		{
			this.setComplexStyle("horizontalAxisGridLinesStyles", name, value);
		}
		
		/**
		 * Sets a style on the vertical axis grid lines.
		 */
		public function setVerticalAxisGridLinesStyle(name:String, value:Object):void
		{
			this.setComplexStyle("verticalAxisGridLinesStyles", name, value);
		}
		
	//--------------------------------------
	//  Protected Methods
	//--------------------------------------

		/**
		 * @private
		 * Redraws chart after an axis label overflows
		 */		
		private function recalculateChart(event:AxisEvent):void
		{
			this.recalculations++;
			if(this.recalculations < 8)
			{
				this.drawAxes();
				this.drawSeries();
				this.updateLegend();
			}
			else
			{
				this.dispatchEvent(new AxisEvent(AxisEvent.AXIS_READY));
			}
			
		}
		
		/**
		 * @private
		 */
		override protected function draw():void
		{
			var dataInvalid:Boolean = this.isInvalid(InvalidationType.DATA);
			var stylesInvalid:Boolean = this.isInvalid(InvalidationType.STYLES);
			var sizeInvalid:Boolean = this.isInvalid(InvalidationType.SIZE);
			var axesInvalid:Boolean = this.isInvalid("axes");
			
			super.draw();
			
			if(stylesInvalid || axesInvalid)
			{
				this.updateRenderers();
			}

			if((sizeInvalid || dataInvalid || stylesInvalid || axesInvalid) && this.width > 0 && this.height > 0)
			{
				this.recalculations = 0;
				var allAxes:Array = this._horizontalAxes.concat(this._verticalAxes);
				var len:int = allAxes.length;
				var i:int;
				for(i = 0; i < len; i++)
				{
					(allAxes[i] as IAxis).maxLabel = "";
				}
								
				this.drawAxes();
					
				//the series display objects are dependant on the axes, so all series redraws must
				//happen after the axes have redrawn
				this.drawSeries();	
			}
			
			this.updateLegend();
		}
		
		/**
		 * @private
		 * Make sure no numeric points exist. Convert to objects compatible with the axes.
		 */
		//Should be ok, only using horizontalAxis and verticalAxis to determine where the categoryAxis is
		override protected function refreshSeries():void
		{
			super.refreshSeries();
			var numericAxis:IAxis = this.horizontalAxis;
			var otherAxis:IAxis = this.verticalAxis;
			if(this.verticalAxis is NumericAxis)
			{
				numericAxis = this.verticalAxis;
				otherAxis = this.horizontalAxis;
			}
						
			var seriesCount:int = this.series.length;
			for(var i:int = 0; i < seriesCount; i++)
			{
				var currentSeries:ISeries = this.series[i] as ISeries;
				
				var numericField:String = this.axisAndSeriesToField(numericAxis, currentSeries);
				var otherField:String = this.axisAndSeriesToField(otherAxis, currentSeries);
				
				var seriesLength:int = currentSeries.length;
				for(var j:int = 0; j < seriesLength; j++)
				{
					var item:Object = currentSeries.dataProvider[j];
					if(item is Number || !isNaN(Number(item)))
					{
						//if we only have a number, then it is safe to convert
						//to a default type for a category chart.
						//if it's not a number, then the user is expected to update
						//the x and y fields so that the plot area knows how to handle it.
						var point:Object = {};
						point[numericField] = item;
						
						//we assume it's a category axis
						if(this._explicitCategoryNames && this._explicitCategoryNames.length > 0)
						{
							point[otherField] = this.categoryNames[j];
						}
						else point[otherField] = j;
						currentSeries.dataProvider[j] = point;
					}
				}                 	
				combineDuplicateCategoryNames(otherAxis);
			}						
		}
			
		/**
		 * @private
		 *
		 * Combines duplicate category labels
		 */
		private function combineDuplicateCategoryNames(categoryAxis:IAxis):void
		{
			if(!(categoryAxis is CategoryAxis)) return;
			var seriesCount:int = this.series.length;
			for(var i:int = 0; i < seriesCount; i++)
			{
				var currentSeries:ISeries = this.series[i] as ISeries;
				var categoryField:String = this.axisAndSeriesToField(categoryAxis, currentSeries);
				var dict:Dictionary = new Dictionary();
				var seriesLength:int = currentSeries.length;
				var newDataProvider:Array = [];
				for(var j:int = 0; j < seriesLength; j++)
				{	
					var item:Object = currentSeries.dataProvider[j];

					if(item.hasOwnProperty(categoryField)) 
					{
						//Combine items that share the same "categoryField" property
						if(!dict.hasOwnProperty(item[categoryField]))
						{
							dict[item[categoryField]] = item;
							newDataProvider.push(dict[item[categoryField]]);
						}
						else
						{
							for(var z:String in item)
							{
								if(z != categoryField && item[z] != null)
								{
									dict[item[categoryField]][z] = item[z];
								}
							}
						}
					}
					else
					{
						dict[item] = item;
						newDataProvider.push(dict[item]);
					}
				}	
				currentSeries.dataProvider = newDataProvider.concat();				
			}
		}
		
		/**
		 * @private
		 * Creates the default axes. Without user intervention, the x-axis is a category
		 * axis and the y-axis is a numeric axis.
		 */
		override protected function configUI():void
		{
			super.configUI();
			this.addChild(this.axisLayer);
			
			//by default, the x axis is for categories. other types of charts will need
			//to override this if they need a numeric or other type of axis
			if(!this.horizontalAxis)
			{
				var categoryAxis:CategoryAxis = new CategoryAxis();
				this.horizontalAxis = categoryAxis;
			}
			
			if(!this.horizontalAxisRenderer)
			{
				var RendererClass:Class = this.getStyleValue("horizontalAxisRenderer") as Class;
				this.horizontalAxisRenderer = new RendererClass();
				this.horizontalAxisRenderer.position = "bottom";
				this.axisLayer.addChild(DisplayObject(this.horizontalAxisRenderer));
				this.horizontalAxis.renderer = this.horizontalAxisRenderer;
			}
			
			if(!this.verticalAxis)
			{
				var numericAxis:NumericAxis = new NumericAxis();
				numericAxis.stackingEnabled = true;
				this.verticalAxis = numericAxis;
			}
			
			if(!this.verticalAxisRenderer)
			{
				RendererClass = this.getStyleValue("verticalAxisRenderer") as Class;
				this.verticalAxisRenderer = new RendererClass();
				this.verticalAxisRenderer.position = "left";
				this.axisLayer.addChild(DisplayObject(this.verticalAxisRenderer));
				this.verticalAxis.renderer = this.verticalAxisRenderer;
			}
			
			this._horizontalAxes.push(this.horizontalAxis);
			this._verticalAxes.push(this.verticalAxis);
		}
		
		/**
		 * @private
		 * Determines the text that will appear on the data tip.
		 */
		//Should be ok, only using horizontalAxis and verticalAxis to determine where the categoryAxis is
		override protected function defaultDataTipFunction(item:Object, index:int, series:ISeries):String
		{
			var text:String = super.defaultDataTipFunction(item, index, series);
			if(text.length > 0)
			{
				text += "\n";
			}
			
			var categoryAxis:CategoryAxis = this.verticalAxis as CategoryAxis;
			var otherAxis:IAxis = this.horizontalAxis;
			if(!categoryAxis)
			{
				categoryAxis = this.horizontalAxis as CategoryAxis;
				otherAxis = this.verticalAxis;
			}
			
			//if we have a category axis, the category is always displayed first
			if(categoryAxis)
			{
				var categoryValue:Object = this.itemToAxisValue(series, index, categoryAxis, false);
				text += categoryAxis.valueToLabel(categoryValue) + "\n";
				
				var otherValue:Object = this.itemToAxisValue(series, index, otherAxis, false);
				text += otherAxis.valueToLabel(otherValue) + "\n";
			}
			//otherwise, display the horizontal axis value first
			else
			{
				var horizontalValue:Object = this.itemToAxisValue(series, index, this.horizontalAxis, false);
				text += horizontalAxis.valueToLabel(horizontalValue) + "\n";
				
				var verticalValue:Object = this.itemToAxisValue(series, index, this.verticalAxis, false);
				text += verticalAxis.valueToLabel(verticalValue) + "\n";
			}
			return text;
		}
	
		/**
		 * @private
		 * Positions and updates the series objects.
		 */
		protected function drawSeries():void
		{
			var contentPadding:Number = this.getStyleValue("contentPadding") as Number;
			var seriesWidth:Number = this._contentBounds.width - (contentPadding * 2);
			var seriesHeight:Number = this._contentBounds.height - (contentPadding *2);
			
			if(this.constrainViewport)
			{
				var contentScrollRect:Rectangle = new Rectangle(0, 0, seriesWidth, seriesHeight);
				this.content.scrollRect = contentScrollRect;
			}
			else
			{
				this.content.scrollRect = null;
			}
			
			this.content.x = contentPadding + this._contentBounds.x;
			this.content.y = contentPadding + this._contentBounds.y;
			
			var seriesCount:int = this.series.length;
			for(var i:int = 0; i < seriesCount; i++)
			{
				var series:UIComponent = this.series[i] as UIComponent;
				series.width = seriesWidth;
				series.height = seriesHeight;
				series.drawNow();
			}
		}
		
		/**
		 * @private
		 * Removes the old axis renderers and create new instances.
		 */
		protected function updateRenderers():void
		{
			this._horizontalAxes = [];
			this._verticalAxes = [];
			
			//create axis renderers
			if(this.horizontalAxisRenderer)
			{
				this.axisLayer.removeChild(DisplayObject(this.horizontalAxisRenderer));
				this.horizontalAxisRenderer = null;
			}
			
			var RendererClass:Class = this.getStyleValue("horizontalAxisRenderer") as Class;
			this.horizontalAxisRenderer = new RendererClass();
			this.horizontalAxisRenderer.position = this.horizontalAxis.position;
			this.axisLayer.addChild(DisplayObject(this.horizontalAxisRenderer));
			this.copyStylesToChild(UIComponent(this.horizontalAxisRenderer), CartesianChart.HORIZONTAL_AXIS_STYLES);
			this.copyStyleObjectToChild(UIComponent(this.horizontalAxisRenderer), this.getStyleValue("horizontalAxisStyles"));
			var horizontalAxisTextFormat:TextFormat = this.getAxisStyle("horizontal", "textFormat") as TextFormat;
			
			if(horizontalAxisTextFormat)
			{
				UIComponent(this.horizontalAxisRenderer).setStyle("textFormat", horizontalAxisTextFormat);
			}
			
			this.horizontalAxis.renderer = this.horizontalAxisRenderer;
			this._horizontalAxes.push(this.horizontalAxis);
		
			if(this.verticalAxisRenderer)
			{
				this.axisLayer.removeChild(DisplayObject(this.verticalAxisRenderer));
				this.verticalAxisRenderer = null;
			}
			
			RendererClass = this.getStyleValue("verticalAxisRenderer") as Class;
			this.verticalAxisRenderer = new RendererClass();
			this.verticalAxisRenderer.position = this.verticalAxis.position;
			this.axisLayer.addChild(DisplayObject(this.verticalAxisRenderer));
			this.copyStylesToChild(UIComponent(verticalAxisRenderer), CartesianChart.VERTICAL_AXIS_STYLES);
			this.copyStyleObjectToChild(UIComponent(this.verticalAxisRenderer), this.getStyleValue("verticalAxisStyles"));
			var verticalAxisTextFormat:TextFormat = this.getAxisStyle("vertical", "textFormat") as TextFormat;
			if(verticalAxisTextFormat)
			{
				UIComponent(this.verticalAxisRenderer).setStyle("textFormat", verticalAxisTextFormat);
			}
			
			this.verticalAxis.renderer = this.verticalAxisRenderer;
			this._verticalAxes.push(this.verticalAxis);			
			
			if(this.secondaryHorizontalAxisRenderer)
			{
				this.axisLayer.removeChild(DisplayObject(this.secondaryHorizontalAxisRenderer));
				this.secondaryHorizontalAxisRenderer = null;
			}
			
			if(this.secondaryHorizontalAxis != null)
			{
				RendererClass = this.getStyleValue("secondaryHorizontalAxisRenderer") as Class;
				this.secondaryHorizontalAxisRenderer = new RendererClass();
	
				this.secondaryHorizontalAxisRenderer.position = this.secondaryHorizontalAxis.position;
				this.axisLayer.addChild(DisplayObject(this.secondaryHorizontalAxisRenderer));
				this.copyStylesToChild(UIComponent(this.secondaryHorizontalAxisRenderer), CartesianChart.HORIZONTAL_AXIS_STYLES);
				this.copyStyleObjectToChild(UIComponent(this.secondaryHorizontalAxisRenderer), this.getStyleValue("horizontalAxisStyles"));
				this.copyStyleObjectToChild(UIComponent(this.secondaryHorizontalAxisRenderer), this.getStyleValue("secondaryHorizontalAxisStyles"));
				var secondaryHorizontalAxisTextFormat:TextFormat = this.getAxisStyle("secondaryHorizontal", "textFormat") as TextFormat;
				if(!secondaryHorizontalAxisTextFormat) secondaryHorizontalAxisTextFormat = this.getAxisStyle("horizontal", "textFormat") as TextFormat;
				if(secondaryHorizontalAxisTextFormat)
				{
					UIComponent(this.secondaryHorizontalAxisRenderer).setStyle("textFormat", secondaryHorizontalAxisTextFormat);
				}
				this.secondaryHorizontalAxis.renderer = this.secondaryHorizontalAxisRenderer;
				this._horizontalAxes.push(this.secondaryHorizontalAxis);
			}			
			if(this.secondaryVerticalAxisRenderer)
			{
				this.axisLayer.removeChild(DisplayObject(this.secondaryVerticalAxisRenderer));
				this.secondaryVerticalAxisRenderer = null;
			}
			
			if(this.secondaryVerticalAxis != null)
			{
				RendererClass = this.getStyleValue("secondaryVerticalAxisRenderer") as Class;			
				this.secondaryVerticalAxisRenderer = new RendererClass();
				this.secondaryVerticalAxisRenderer.position = this.secondaryVerticalAxis.position;
				this.axisLayer.addChild(DisplayObject(this.secondaryVerticalAxisRenderer));
				this.copyStylesToChild(UIComponent(this.secondaryVerticalAxisRenderer), CartesianChart.VERTICAL_AXIS_STYLES);
				this.copyStyleObjectToChild(UIComponent(this.secondaryVerticalAxisRenderer), this.getStyleValue("verticalAxisStyles"));
				this.copyStyleObjectToChild(UIComponent(this.secondaryVerticalAxisRenderer), this.getStyleValue("secondaryVerticalAxisStyles"));
				var secondaryVerticalAxisTextFormat:TextFormat = this.getAxisStyle("secondaryVertical", "textFormat") as TextFormat;
				if(!secondaryVerticalAxisTextFormat) secondaryVerticalAxisTextFormat = this.getAxisStyle("vertical", "textFormat") as TextFormat;
				if(secondaryVerticalAxisTextFormat)
				{
					UIComponent(this.secondaryVerticalAxisRenderer).setStyle("textFormat", secondaryVerticalAxisTextFormat);
				}
				this.secondaryVerticalAxis.renderer = this.secondaryVerticalAxisRenderer;
				this._verticalAxes.push(this.secondaryVerticalAxis);
			}
			//create grid lines renderers
			
			if(this.horizontalGridLines)
			{
				this.removeChild(DisplayObject(this.horizontalGridLines));
			}
			RendererClass = this.getStyleValue("horizontalAxisGridLinesRenderer") as Class;
			this.horizontalGridLines = new RendererClass();
			this.horizontalGridLines.axisRenderer = this.horizontalAxisRenderer;
			this.addChild(DisplayObject(this.horizontalGridLines));
			this.copyStylesToChild(UIComponent(this.horizontalGridLines), CartesianChart.HORIZONTAL_GRID_LINES_STYLES);
			this.copyStyleObjectToChild(UIComponent(this.horizontalGridLines), this.getStyleValue("horizontalAxisGridLinesStyles")); 
			
			if(this.verticalGridLines)
			{
				this.removeChild(DisplayObject(this.verticalGridLines));
			}
			RendererClass = this.getStyleValue("verticalAxisGridLinesRenderer") as Class;
			this.verticalGridLines = new RendererClass();
			this.verticalGridLines.axisRenderer = this.verticalAxisRenderer;
			this.addChild(DisplayObject(this.verticalGridLines));
			this.copyStylesToChild(UIComponent(this.verticalGridLines), CartesianChart.VERTICAL_GRID_LINES_STYLES);
			this.copyStyleObjectToChild(UIComponent(this.verticalGridLines), this.getStyleValue("verticalAxisGridLinesStyles")); 
			
		}
		
		/**
		 * @private
		 * Positions and sizes the axes based on their edge metrics.
		 */
		protected function drawAxes():void
		{	
			var horizontalAxisLabelData:AxisLabelData = new AxisLabelData();
			var verticalAxisLabelData:AxisLabelData = new AxisLabelData();
						
			var contentPadding:Number = this.getStyleValue("contentPadding") as Number;
			var axisWidth:Number = this.width - (2 * contentPadding);
			var axisHeight:Number = this.height - (2 * contentPadding);
			
			var topBuffer:Number = 0;
			var rightBuffer:Number = 0;
			var bottomBuffer:Number = 0;
			var leftBuffer:Number = 0;
			
			var allAxes:Array = this._horizontalAxes.concat(this._verticalAxes);
			var len:int = allAxes.length;
			var i:int;
			for(i = 0; i < len; i++)
			{
				var axis:IAxis = allAxes[i] as IAxis;
				if(axis is CategoryAxis && this._explicitCategoryNames && this._explicitCategoryNames.length > 0)
				{
					CategoryAxis(axis).categoryNames = this._explicitCategoryNames;
				}
				var axisRenderer:UIComponent = UIComponent(axis.renderer);
				var cartesianAxisRenderer:ICartesianAxisRenderer = axis.renderer as ICartesianAxisRenderer;
				axisRenderer.setSize(axisWidth, axisHeight);
				cartesianAxisRenderer.title = axis.title;
				this.axisLayer.setChildIndex(axisRenderer, this.axisLayer.numChildren - 1);			
				cartesianAxisRenderer.ticks = [];
				cartesianAxisRenderer.minorTicks = [];
			
				cartesianAxisRenderer.outerTickOffset = this.getAxisTickOffset(cartesianAxisRenderer) as Number;
				
				axis.labelData = (cartesianAxisRenderer.orientation == AxisOrientation.VERTICAL) ? verticalAxisLabelData : horizontalAxisLabelData;	
				axis.dataProvider = this.series;
				
				switch(axis.position)
				{
					case "top" :
						topBuffer += axis.height;
					break;
					case "left" :
						leftBuffer += axis.width;
					break;
					case "right" :
						rightBuffer += axis.width;
					break;
					case "bottom" :
						bottomBuffer += axis.height;
					break;	
				}						
			}
			
			this._contentBounds = new Rectangle();			
			
			this.contentBounds.x = Math.ceil(Math.max(leftBuffer, horizontalAxisLabelData.leftLabelOffset));
			this.contentBounds.y = Math.ceil(verticalAxisLabelData.topLabelOffset);
			this.contentBounds.width = Math.floor(this.width - (this.contentBounds.x + Math.max(rightBuffer, horizontalAxisLabelData.rightLabelOffset)));
			this.contentBounds.y = Math.ceil(Math.max(topBuffer, verticalAxisLabelData.topLabelOffset));	
			this.contentBounds.height = Math.floor(this.height - (this.contentBounds.y + Math.max(bottomBuffer, verticalAxisLabelData.bottomLabelOffset)));			
			
			for(i = 0; i < len; i++)
			{
				axis = allAxes[i] as IAxis;
				cartesianAxisRenderer = axis.renderer as ICartesianAxisRenderer;
				cartesianAxisRenderer.contentBounds.width = this.contentBounds.width - (contentPadding * 2);
				cartesianAxisRenderer.contentBounds.height = this.contentBounds.height - (contentPadding * 2);
				cartesianAxisRenderer.contentBounds.x = contentPadding + this.contentBounds.x;
				cartesianAxisRenderer.contentBounds.y = contentPadding + this.contentBounds.y;				
				axis.updateScale();
				cartesianAxisRenderer.updateAxis();				
			}
			
			this.drawGridLines();
		}
		
		/**
		 * @private
		 * Returns the amount of distance ticks extend over the edge of the content bounds
		 */
		public function getAxisTickOffset(axis:ICartesianAxisRenderer):Number
		{
			var showTicks:Boolean = (axis as UIComponent).getStyle("showTicks") as Boolean;
			var showMinorTicks:Boolean = (axis as UIComponent).getStyle("showMinorTicks") as Boolean;
			var tickPosition:String = (axis as UIComponent).getStyle("tickPosition") as String;
			var minorTickPosition:String = (axis as UIComponent).getStyle("minorTickPosition") as String;
			var tickLength:Number = (axis as UIComponent).getStyle("tickLength") as Number;
			var minorTickLength:Number = (axis as UIComponent).getStyle("minorTickLength") as Number;
			var tickBuffer:Number = 0;
			var minorTickBuffer:Number = 0;
			
			if(showTicks)
			{
				if(tickPosition == "outside")
				{
					tickBuffer = tickLength;
				}
				else if(tickPosition == "cross")
				{
					tickBuffer = tickLength/2;
				}
			}
			
			if(showMinorTicks)
			{
				if(minorTickPosition == "outside")
				{
					minorTickBuffer = minorTickLength;
				}
				else if(minorTickPosition == "cross")
				{
					minorTickBuffer = minorTickLength/2;
				}								
			}			
			
			return Math.max(tickBuffer, minorTickBuffer);
		}
		
		/**
		 * @private
		 * Draws the axis grid lines, if they exist.
		 */
		protected function drawGridLines():void
		{
			var contentPadding:Number = this.getStyleValue("contentPadding") as Number;
			var horizontalAxisRenderer:UIComponent = this.horizontalAxisRenderer as UIComponent;
			var verticalAxisRenderer:UIComponent = this.verticalAxisRenderer as UIComponent;
			
			var index:int = 0;
			if(this.background)
			{
				index++;
			}
			
			if(this.horizontalGridLines)
			{
				var horizontalGridLines:UIComponent = this.horizontalGridLines as UIComponent;
				this.setChildIndex(horizontalGridLines, index++);
				horizontalGridLines.x = contentPadding + this.contentBounds.x;
				horizontalGridLines.y = contentPadding + this.contentBounds.y;
				horizontalGridLines.drawNow();
			}
			
			if(this.verticalGridLines)
			{
				var verticalGridLines:UIComponent = this.verticalGridLines as UIComponent;
				this.setChildIndex(verticalGridLines, index++);
				verticalGridLines.x = contentPadding + this.contentBounds.x;
				verticalGridLines.y = contentPadding + this.contentBounds.y;
				verticalGridLines.drawNow();
			}
		}
		 
		 /**
		  * @private
		  */
		 public function setComplexStyle(complexName:String, subStyleName:String, subStyleValue:Object):void
		 {
			var container:Object = this.getStyleValue(complexName);
			var copy:Object = {};
			for(var prop:String in container)
			{
				copy[prop] = container[prop];
			}
			copy[subStyleName] = subStyleValue;
			this.setStyle(complexName, copy);
		 } 
		
		/**
		 * @private
		 */
		protected function copyStyleObjectToChild(child:UIComponent, styles:Object):void
		{
			if(!child)
			{
				return;
			}
			
			for(var prop:String in styles)
			{
				child.setStyle(prop, styles[prop]);
			}
		}
		
		/**
		 * @private
		 */
		protected function axisAndSeriesToField(axis:IAxis, series:ISeries):String
		{
			var cartesianSeries:CartesianSeries = series as CartesianSeries;
			var field:String = this.axisToField(axis);
			var renderer:ICartesianAxisRenderer = this.axisToAxisRenderer(axis);
			if(renderer.orientation == AxisOrientation.VERTICAL && cartesianSeries.verticalField)
			{
				field = cartesianSeries.verticalField;
			}
			else if(renderer.orientation == AxisOrientation.HORIZONTAL && cartesianSeries.horizontalField)
			{
				field = cartesianSeries.horizontalField;
			}
			
			return field;
		}
	
		/**
		 * @private
		 */
		protected function axisToField(axis:IAxis):String
		{
			if(axis == this.horizontalAxis || axis == this.secondaryHorizontalAxis)
			{
				return this.horizontalField;
			}
			else if(axis == this.verticalAxis || axis == this.secondaryVerticalAxis)
			{
				return this.verticalField;
			}
			return null;
		}
		
		
		/**
		 * @private
		 * Finds the renderer for the specified axis.
		 */
		protected function axisToAxisRenderer(axis:IAxis):ICartesianAxisRenderer
		{
			if(axis == this.horizontalAxis)
			{
				return this.horizontalAxisRenderer;
			}
			else if(axis == this.verticalAxis)
			{
				return this.verticalAxisRenderer;
			}
			else if(axis == this.secondaryHorizontalAxis)
			{
				return this.secondaryHorizontalAxisRenderer;
			}
			else if(axis == this.secondaryVerticalAxis)
			{
				return this.secondaryVerticalAxisRenderer;
			}
			return null;
		}
	}
}
