<?php

/* $Id$ */

/*
 * PHPLOT Version 5.0.5
 * Copyright (C) 1998-2008 Afan Ottenheimer.  Released under
 * the GPL and PHP licenses as stated in the the README file which should
 * have been included with this document.
 *
 * Co-author and maintainer (2003-2005)
 * Miguel de Benito Delgado <nonick AT vodafone DOT es>
 *
 * Maintainer (2006-present)
 * <lbayuk AT users DOT sourceforge DOT net>
 *
 * Visit http://sourceforge.net/projects/phplot/
 * for PHPlot documentation, downloads, and discussions.
 *
 * Requires PHP 5.2.x or later. (PHP 4 is unsupported as of Jan 2008)
 */

class PHPlot {

    /* I have removed internal variable declarations, some isset() checking was required,
     * but now the variables left are those which can be tweaked by the user. This is intended to
     * be the first step towards moving most of the Set...() methods into a subclass which will be
     * used only when strictly necessary. Many users will be able to put default values here in the
     * class and thus avoid memory overhead and reduce parsing times.
     */
    //////////////// CONFIG PARAMETERS //////////////////////

    var $is_inline = FALSE;             // FALSE = Sends headers, TRUE = sends just raw image data
    var $browser_cache = FALSE;         // FALSE = Sends headers for browser to not cache the image,
                                        // (only if is_inline = FALSE also)

    var $safe_margin = 5;               // Extra margin used in several places. In pixels

    var $x_axis_position = '';          // Where to draw both axis (world coordinates),
    var $y_axis_position = '';          // leave blank for X axis at 0 and Y axis at left of plot.

    var $xscale_type = 'linear';        // linear, log
    var $yscale_type = 'linear';

//Fonts
    var $use_ttf  = FALSE;                  // Use True Type Fonts?
    var $ttf_path = '.';                    // Default path to look in for TT Fonts.
    var $default_ttfont = 'benjamingothic.ttf';
    var $line_spacing = 4;                  // Pixels between lines.

    // Font angles: 0 or 90 degrees for fixed fonts, any for TTF
    var $x_label_angle = 0;                 // For labels on X axis (tick and data)
    var $y_label_angle = 0;                 // For labels on Y axis (tick and data)

//Formats
    var $file_format = 'png';
    var $output_file = '';                  // For output to a file instead of stdout

//Data
    var $data_type = 'text-data';           // text-data, data-data-error, data-data, text-data-single
    var $plot_type= 'linepoints';           // bars, lines, linepoints, area, points, pie, thinbarline, squared

    var $label_scale_position = 0.5;        // Shifts data labes in pie charts. 1 = top, 0 = bottom
    var $group_frac_width = 0.7;            // Bars use this fraction (0 to 1) of a group's space
    var $bar_extra_space = 0.5;             // Number of extra bar's worth of space in a group
    var $bar_width_adjust = 1;              // 1 = bars of normal width, must be > 0

    var $y_precision = 1;
    var $x_precision = 1;

    var $data_units_text = '';              // Units text for 'data' labels (i.e: '¤', '$', etc.)

// Titles
    var $title_txt = '';

    var $x_title_txt = '';
    var $x_title_pos = 'plotdown';          // plotdown, plotup, both, none

    var $y_title_txt = '';
    var $y_title_pos = 'plotleft';          // plotleft, plotright, both, none


//Labels
    // There are two types of labels in PHPlot:
    //    Tick labels: they follow the grid, next to ticks in axis.   (DONE)
    //                 they are drawn at grid drawing time, by DrawXTicks() and DrawYTicks()
    //    Data labels: they follow the data points, and can be placed on the axis or the plot (x/y)  (TODO)
    //                 they are drawn at graph plotting time, by Draw*DataLabel(), called by DrawLines(), etc.
    //                 Draw*DataLabel() also draws H/V lines to datapoints depending on draw_*_data_label_lines

    // Tick Labels
    var $x_tick_label_pos = 'plotdown';     // plotdown, plotup, both, xaxis, none
    var $y_tick_label_pos = 'plotleft';     // plotleft, plotright, both, yaxis, none

    // Data Labels:
    var $x_data_label_pos = 'plotdown';     // plotdown, plotup, both, plot, all, none
    var $y_data_label_pos = 'plotleft';     // plotleft, plotright, both, plot, all, plotin, none

    var $draw_x_data_label_lines = FALSE;   // Draw a line from the data point to the axis?
    var $draw_y_data_label_lines = FALSE;   // TODO

    // Label types: (for tick, data and plot labels)
    var $x_label_type = '';                 // data, time. Leave blank for no formatting.
    var $y_label_type = '';                 // data, time. Leave blank for no formatting.
    var $x_time_format = '%H:%M:%S';        // See http://www.php.net/manual/html/function.strftime.html
    var $y_time_format = '%H:%M:%S';        // SetYTimeFormat() too...

    // Skipping labels
    // var $x_label_inc = 1;                   // Draw a label every this many (1 = all) (TODO)
    // var $y_label_inc = 1;
    // var $_x_label_cnt = 0;                  // internal count FIXME: work in progress

// Legend
    var $legend = '';                       // An array with legend titles
    // These variables are unset to take default values:
    // var $legend_x_pos;                   // User-specified upper left coordinates of legend box
    // var $legend_y_pos;
    // var $legend_xy_world;                // If set, legend_x/y_pos are world coords, else pixel coords
    // var $legend_text_align;              // left or right, Unset means right
    // var $legend_colorbox_align;          // left, right, or none; Unset means same as text_align

//Ticks
    var $x_tick_length = 5;                 // tick length in pixels for upper/lower axis
    var $y_tick_length = 5;                 // tick length in pixels for left/right axis

    var $x_tick_cross = 3;                  // ticks cross x axis this many pixels
    var $y_tick_cross = 3;                  // ticks cross y axis this many pixels

    var $x_tick_pos = 'plotdown';           // plotdown, plotup, both, xaxis, none
    var $y_tick_pos = 'plotleft';           // plotright, plotleft, both, yaxis, none

    var $num_x_ticks = '';
    var $num_y_ticks = '';

    var $x_tick_inc = '';                   // Set num_x_ticks or x_tick_inc, not both.
    var $y_tick_inc = '';                   // Set num_y_ticks or y_tick_inc, not both.

    var $skip_top_tick = FALSE;
    var $skip_bottom_tick = FALSE;
    var $skip_left_tick = FALSE;
    var $skip_right_tick = FALSE;

//Grid Formatting
    var $draw_x_grid = FALSE;
    var $draw_y_grid = TRUE;

    var $dashed_grid = TRUE;
    var $grid_at_foreground = FALSE;        // Chooses whether to draw the grid below or above the graph

//Colors and styles       (all colors can be array (R,G,B) or named color)
    var $color_array = 'small';             // 'small', 'large' or array (define your own colors)
                                            // See rgb.inc.php and SetRGBArray()
    var $i_border = array(194, 194, 194);
    var $plot_bg_color = 'white';
    var $bg_color = 'white';
    var $label_color = 'black';
    var $text_color = 'black';
    var $grid_color = 'black';
    var $light_grid_color = 'gray';
    var $tick_color = 'black';
    var $title_color = 'black';
    var $data_colors = array('SkyBlue', 'green', 'orange', 'blue', 'orange', 'red', 'violet', 'azure1');
    var $error_bar_colors = array('SkyBlue', 'green', 'orange', 'blue', 'orange', 'red', 'violet', 'azure1');
    var $data_border_colors = array('black');

    var $line_widths = 1;                  // single value or array
    var $line_styles = array('solid', 'solid', 'dashed');   // single value or array
    var $dashed_style = '2-4';              // colored dots-transparent dots

    var $point_sizes = array(5,5,3);         // single value or array
    var $point_shapes = array('diamond');   // rect, circle, diamond, triangle, dot, line, halfline, cross

    var $error_bar_size = 5;                // right and left size of tee
    var $error_bar_shape = 'tee';           // 'tee' or 'line'
    var $error_bar_line_width = 1;          // single value (or array TODO)

    var $plot_border_type = 'sides';        // left, sides, none, full
    var $image_border_type = 'none';        // 'raised', 'plain', 'none'

    var $shading = 5;                       // 0 for no shading, > 0 is size of shadows in pixels

    var $draw_plot_area_background = FALSE;
    var $draw_broken_lines = FALSE;          // Tells not to draw lines for missing Y data.

//Miscellaneous
    var $callbacks = array(                  // Valid callback reasons (see SetCallBack)
        'draw_setup' => NULL,
        'draw_image_background' => NULL,
        'draw_plotarea_background' => NULL,
        'draw_titles' => NULL,
        'draw_axes' => NULL,
        'draw_graph' => NULL,
        'draw_border' => NULL,
        'draw_legend' => NULL,
        'debug_textbox' => NULL,  // For testing/debugging text box alignment
        'debug_scale' => NULL,    // For testing/debugging scale setup
    );


//////////////////////////////////////////////////////
//BEGIN CODE
//////////////////////////////////////////////////////

    /*!
     * Constructor: Setup img resource, colors and size of the image, and font sizes.
     *
     * \param which_width       int    Image width in pixels.
     * \param which_height      int    Image height in pixels.
     * \param which_output_file string Filename for output.
     * \param which_input_fule  string Path to a file to be used as background.
     */
    function PHPlot($which_width=600, $which_height=400, $which_output_file=NULL, $which_input_file=NULL)
    {
        $this->SetRGBArray($this->color_array);

        $this->background_done = FALSE;     // TRUE after background image is drawn once
        $this->plot_margins_set = FALSE;    // TRUE with user-set plot area or plot margins.

        if ($which_output_file)
            $this->SetOutputFile($which_output_file);

        if ($which_input_file)
            $this->SetInputFile($which_input_file);
        else {
            $this->image_width = $which_width;
            $this->image_height = $which_height;

            $this->img = ImageCreate($this->image_width, $this->image_height);
            if (! $this->img)
                return $this->PrintError('PHPlot(): Could not create image resource.');

        }

        $this->SetDefaultStyles();
        $this->SetDefaultFonts();

        $this->SetTitle('');
        $this->SetXTitle('');
        $this->SetYTitle('');

        $this->print_image = TRUE;      // Use for multiple plots per image (TODO: automatic)
    }

    /*!
     * Reads an image file. Stores width and height, and returns the image
     * resource. On error, calls PrintError and returns False.
     * This is used by the constructor via SetInputFile, and by tile_img().
     */
    function GetImage($image_filename, &$width, &$height)
    {
        $error = '';
        $size = getimagesize($image_filename);
        if (!$size) {
            $error = "Unable to query image file $image_filename";
        } else {
            $image_type = $size[2];
            switch($image_type) {
            case IMAGETYPE_GIF:
                $img = @ ImageCreateFromGIF ($image_filename);
                break;
            case IMAGETYPE_PNG:
                $img = @ ImageCreateFromPNG ($image_filename);
                break;
            case IMAGETYPE_JPEG:
                $img = @ ImageCreateFromJPEG ($image_filename);
                break;
            default:
                $error = "Unknown image type ($image_type) for image file $image_filename";
                break;
            }
        }
        if (empty($error) && !$img) {
            # getimagesize is OK, but GD won't read it. Maybe unsupported format.
            $error = "Failed to read image file $image_filename";
        }
        if (!empty($error)) {
            return $this->PrintError("GetImage(): $error");
        }
        $width = $size[0];
        $height = $size[1];
        return $img;
    }

    /*!
     * Selects an input file to be used as background for the whole graph.
     * This resets the graph size to the image's size.
     * Note: This is used by the constructor. It is deprecated for direct use.
     */
    function SetInputFile($which_input_file)
    {
        $im = $this->GetImage($which_input_file, $this->image_width, $this->image_height);
        if (!$im)
            return FALSE;  // GetImage already produced an error message.

        // Deallocate any resources previously allocated
        if (isset($this->img))
            imagedestroy($this->img);

        $this->img = $im;

        // Do not overwrite the input file with the background color.
        $this->background_done = TRUE;

        return TRUE;
    }

/////////////////////////////////////////////
//////////////                         COLORS
/////////////////////////////////////////////

    /*!
     * Returns an index to a color passed in as anything (string, hex, rgb)
     *
     * \param which_color * Color (can be '#AABBCC', 'Colorname', or array(r,g,b))
     * Returns a GD color index (integer >= 0), or NULL on error.
     */
    function SetIndexColor($which_color)
    {
        list ($r, $g, $b) = $this->SetRGBColor($which_color);  //Translate to RGB
        if (!isset($r)) return NULL;
        return ImageColorResolve($this->img, $r, $g, $b);
    }


    /*!
     * Returns an index to a slightly darker color than the one requested.
     * Returns a GD color index (integer >= 0), or NULL on error.
     */
    function SetIndexDarkColor($which_color)
    {
        list ($r, $g, $b) = $this->SetRGBColor($which_color);
        if (!isset($r)) return NULL;
        $r = max(0, $r - 0x30);
        $g = max(0, $g - 0x30);
        $b = max(0, $b - 0x30);
        return ImageColorResolve($this->img, $r, $g, $b);
    }

    /*!
     * Sets/reverts all colors and styles to their defaults. If session is set, then only updates indices,
     * as they are lost with every script execution, else, sets the default colors by name or value and
     * then updates indices too.
     *
     * FIXME Isn't this too slow?
     *
     */
    function SetDefaultStyles()
    {
        /* Some of the Set*() functions use default values when they get no parameters. */

        if (! isset($this->session_set)) {
            // If sessions are enabled, this variable will be preserved, so upon future executions, we
            // will have it set, as well as color names (though not color indices, that's why we
            // need to rebuild them)
            $this->session_set = TRUE;

            // These only need to be set once
            $this->SetLineWidths();
            $this->SetLineStyles();
            $this->SetDefaultDashedStyle($this->dashed_style);
            $this->SetPointSizes($this->point_sizes);
        }

        $this->SetImageBorderColor($this->i_border);
        $this->SetPlotBgColor($this->plot_bg_color);
        $this->SetBackgroundColor($this->bg_color);
        $this->SetLabelColor($this->label_color);
        $this->SetTextColor($this->text_color);
        $this->SetGridColor($this->grid_color);
        $this->SetLightGridColor($this->light_grid_color);
        $this->SetTickColor($this->tick_color);
        $this->SetTitleColor($this->title_color);
        $this->SetDataColors();
        $this->SetErrorBarColors();
        $this->SetDataBorderColors();
        return TRUE;
    }


    /*
     *
     */
    function SetBackgroundColor($which_color)
    {
        $this->bg_color= $which_color;
        $this->ndx_bg_color= $this->SetIndexColor($this->bg_color);
        return isset($this->ndx_bg_color);
    }

    /*
     *
     */
    function SetPlotBgColor($which_color)
    {
        $this->plot_bg_color= $which_color;
        $this->ndx_plot_bg_color= $this->SetIndexColor($this->plot_bg_color);
        return isset($this->ndx_plot_bg_color);
    }

   /*
    *
    */
    function SetTitleColor($which_color)
    {
        $this->title_color= $which_color;
        $this->ndx_title_color= $this->SetIndexColor($this->title_color);
        return isset($this->ndx_title_color);
    }

    /*
     *
     */
    function SetTickColor ($which_color)
    {
        $this->tick_color= $which_color;
        $this->ndx_tick_color= $this->SetIndexColor($this->tick_color);
        return isset($this->ndx_tick_color);
    }


    /*
     * Do not use. Use SetTitleColor instead.
     */
    function SetLabelColor ($which_color)
    {
        $this->label_color = $which_color;
        $this->ndx_title_color= $this->SetIndexColor($this->label_color);
        return isset($this->ndx_title_color);
    }


    /*
     *
     */
    function SetTextColor ($which_color)
    {
        $this->text_color= $which_color;
        $this->ndx_text_color= $this->SetIndexColor($this->text_color);
        return isset($this->ndx_text_color);
    }


    /*
     *
     */
    function SetLightGridColor ($which_color)
    {
        $this->light_grid_color= $which_color;
        $this->ndx_light_grid_color= $this->SetIndexColor($this->light_grid_color);
        return isset($this->ndx_light_grid_color);
    }


    /*
     *
     */
    function SetGridColor ($which_color)
    {
        $this->grid_color = $which_color;
        $this->ndx_grid_color= $this->SetIndexColor($this->grid_color);
        return isset($this->ndx_grid_color);
    }


    /*
     *
     */
    function SetImageBorderColor($which_color)
    {
        $this->i_border = $which_color;
        $this->ndx_i_border = $this->SetIndexColor($this->i_border);
        $this->ndx_i_border_dark = $this->SetIndexDarkColor($this->i_border);
        return isset($this->ndx_i_border);
    }


    /*
     *
     */
    function SetTransparentColor($which_color)
    {
        $ndx = $this->SetIndexColor($which_color);
        if (!isset($ndx))
            return FALSE;
        ImageColorTransparent($this->img, $ndx);
        return TRUE;
    }


    /*!
     * Sets the array of colors to be used. It can be user defined, a small predefined one
     * or a large one included from 'rgb.inc.php'.
     *
     * \param which_color_array If an array, the used as color array. If a string can
     *        be one of 'small' or 'large'.
     */
    function SetRGBArray ($which_color_array)
    {
        if ( is_array($which_color_array) ) {           // User defined array
            $this->rgb_array = $which_color_array;
            return TRUE;
        } elseif ($which_color_array == 'small') {      // Small predefined color array
            $this->rgb_array = array(
                'white'          => array(255, 255, 255),
                'snow'           => array(255, 250, 250),
                'PeachPuff'      => array(255, 218, 185),
                'ivory'          => array(255, 255, 240),
                'lavender'       => array(230, 230, 250),
                'black'          => array(  0,   0,   0),
                'DimGrey'        => array(105, 105, 105),
                'gray'           => array(190, 190, 190),
                'grey'           => array(190, 190, 190),
                'navy'           => array(  0,   0, 128),
                'SlateBlue'      => array(106,  90, 205),
                'blue'           => array(  0,   0, 255),
                'SkyBlue'        => array(135, 206, 235),
                'cyan'           => array(  0, 255, 255),
                'DarkGreen'      => array(  0, 100,   0),
                'green'          => array(  0, 255,   0),
                'YellowGreen'    => array(154, 205,  50),
                'yellow'         => array(255, 255,   0),
                'orange'         => array(255, 165,   0),
                'gold'           => array(255, 215,   0),
                'peru'           => array(205, 133,  63),
                'beige'          => array(245, 245, 220),
                'wheat'          => array(245, 222, 179),
                'tan'            => array(210, 180, 140),
                'brown'          => array(165,  42,  42),
                'salmon'         => array(250, 128, 114),
                'red'            => array(255,   0,   0),
                'pink'           => array(255, 192, 203),
                'maroon'         => array(176,  48,  96),
                'magenta'        => array(255,   0, 255),
                'violet'         => array(238, 130, 238),
                'plum'           => array(221, 160, 221),
                'orchid'         => array(218, 112, 214),
                'purple'         => array(160,  32, 240),
                'azure1'         => array(240, 255, 255),
                'aquamarine1'    => array(127, 255, 212)
                );
            return TRUE;
        } elseif ($which_color_array === 'large')  {    // Large color array
            include("./rgb.inc.php");
            $this->rgb_array = $RGBArray;
        } else {                                        // Default to black and white only.
            $this->rgb_array = array('white' => array(255, 255, 255), 'black' => array(0, 0, 0));
        }

        return TRUE;
    }

    /*!
     * Returns an array in R, G, B format 0-255
     *
     *  \param color_asked array(R,G,B) or string (named color or '#AABBCC')
     */
    function SetRGBColor($color_asked)
    {
        if (empty($color_asked)) {
            $ret_val = array(0, 0, 0);
        } elseif (count($color_asked) == 3 ) {    // already array of 3 rgb
            $ret_val = $color_asked;
        } elseif ($color_asked[0] == '#') {       // Hex RGB notation #RRGGBB
            $ret_val = array(hexdec(substr($color_asked, 1, 2)),
                             hexdec(substr($color_asked, 3, 2)),
                             hexdec(substr($color_asked, 5, 2)));

        } elseif (isset($this->rgb_array[$color_asked])) {  // Color by name
            $ret_val = $this->rgb_array[$color_asked];
        } else {
            return $this->PrintError("SetRGBColor(): Color '$color_asked' is not valid.");
        }
        return $ret_val;
    }


    /*!
     * Sets the colors for the data.
     */
    function SetDataColors($which_data = NULL, $which_border = NULL)
    {
        if (is_null($which_data) && is_array($this->data_colors)) {
            // use already set data_colors
        } else if (! is_array($which_data)) {
            $this->data_colors = ($which_data) ? array($which_data) : array('blue', 'red', 'green', 'orange');
        } else {
            $this->data_colors = $which_data;
        }

        $i = 0;
        foreach ($this->data_colors as $col) {
            $ndx = $this->SetIndexColor($col);
            if (!isset($ndx))
                return FALSE;
            $this->ndx_data_colors[$i] = $ndx;
            $this->ndx_data_dark_colors[$i] = $this->SetIndexDarkColor($col);
            $i++;
        }

        // For past compatibility:
        return $this->SetDataBorderColors($which_border);
    } // function SetDataColors()


    /*!
     *
     */
    function SetDataBorderColors($which_br = NULL)
    {
        if (is_null($which_br) && is_array($this->data_border_colors)) {
            // use already set data_border_colors
        } else if (! is_array($which_br)) {
            // Create new array with specified color
            $this->data_border_colors = ($which_br) ? array($which_br) : array('black');
        } else {
            $this->data_border_colors = $which_br;
        }

        $i = 0;
        foreach($this->data_border_colors as $col) {
            $ndx = $this->SetIndexColor($col);
            if (!isset($ndx))
                return FALSE;
            $this->ndx_data_border_colors[$i] = $ndx;
            $i++;
        }
        return TRUE;
    } // function SetDataBorderColors()


    /*!
     * Sets the colors for the data error bars.
     */
    function SetErrorBarColors($which_err = NULL)
    {
        if (is_null($which_err) && is_array($this->error_bar_colors)) {
            // use already set error_bar_colors
        } else if (! is_array($which_err)) {
            $this->error_bar_colors = ($which_err) ? array($which_err) : array('black');
        } else {
            $this->error_bar_colors = $which_err;
        }

        $i = 0;
        foreach($this->error_bar_colors as $col) {
            $ndx = $this->SetIndexColor($col);
            if (!isset($ndx))
                return FALSE;
            $this->ndx_error_bar_colors[$i] = $ndx;
            $i++;
        }
        return TRUE;
    } // function SetErrorBarColors()


    /*!
     * Sets the default dashed style.
     *  \param which_style A string specifying order of colored and transparent dots,
     *         i.e: '4-3' means 4 colored, 3 transparent;
     *              '2-3-1-2' means 2 colored, 3 transparent, 1 colored, 2 transparent.
     */
    function SetDefaultDashedStyle($which_style)
    {
        // String: "numcol-numtrans-numcol-numtrans..."
        $asked = explode('-', $which_style);

        if (count($asked) < 2) {
            return $this->PrintError("SetDefaultDashedStyle(): Wrong parameter '$which_style'.");
        }

        // Build the string to be eval()uated later by SetDashedStyle()
        $this->default_dashed_style = 'array( ';

        $t = 0;
        foreach($asked as $s) {
            if ($t % 2 == 0) {
                $this->default_dashed_style .= str_repeat('$which_ndxcol,', $s);
            } else {
                $this->default_dashed_style .= str_repeat('IMG_COLOR_TRANSPARENT,', $s);
            }
            $t++;
        }
        // Remove trailing comma and add closing parenthesis
        $this->default_dashed_style = substr($this->default_dashed_style, 0, -1);
        $this->default_dashed_style .= ')';

        return TRUE;
    }


    /*!
     * Sets the style before drawing a dashed line. Defaults to $this->default_dashed_style
     *   \param which_ndxcol Color index to be used.
     */
    function SetDashedStyle($which_ndxcol)
    {
        // See SetDefaultDashedStyle() to understand this.
        eval ("\$style = $this->default_dashed_style;");
        return imagesetstyle($this->img, $style);
    }


    /*!
     * Sets line widths on a per-line basis.
     */
    function SetLineWidths($which_lw=NULL)
    {
        if (is_null($which_lw)) {
            // Do nothing, use default value.
        } else if (is_array($which_lw)) {
            // Did we get an array with line widths?
            $this->line_widths = $which_lw;
        } else {
            $this->line_widths = array($which_lw);
        }
        return TRUE;
    }

    /*!
     *
     */
    function SetLineStyles($which_ls=NULL)
    {
        if (is_null($which_ls)) {
            // Do nothing, use default value.
        } else if ( is_array($which_ls)) {
            // Did we get an array with line styles?
            $this->line_styles = $which_ls;
        } else {
            $this->line_styles = ($which_ls) ? array($which_ls) : array('solid');
        }
        return TRUE;
    }


/////////////////////////////////////////////
//////////////                 TEXT and FONTS
/////////////////////////////////////////////


    /*!
     * Sets number of pixels between lines of the same text.
     */
    function SetLineSpacing($which_spc)
    {
        $this->line_spacing = $which_spc;
        return TRUE;
    }


    /*!
     * Enables use of TrueType fonts in the graph. Font initialisation methods
     * depend on this setting, so when called, SetUseTTF() resets the font
     * settings
     */
    function SetUseTTF($which_ttf)
    {
        $this->use_ttf = $which_ttf;
        return $this->SetDefaultFonts();
    }

    /*!
     * Sets the directory name to look into for TrueType fonts.
     */
    function SetTTFPath($which_path)
    {
        // Maybe someone needs really dynamic config. He'll need this:
        // clearstatcache();

        if (is_dir($which_path) && is_readable($which_path)) {
            $this->ttf_path = $which_path;
            return TRUE;
        }
        return $this->PrintError("SetTTFPath(): $which_path is not a valid path.");
    }

    /*!
     * Sets the default TrueType font and updates all fonts to that.
     * The default font might be a full path, or relative to the TTFPath,
     * so let SetFont check that it exists.
     * Side effect: enable use of TrueType fonts.
     */
    function SetDefaultTTFont($which_font)
    {
        $this->default_ttfont = $which_font;
        return $this->SetUseTTF(TRUE);
    }

    /*!
     * Sets fonts to their defaults
     */
    function SetDefaultFonts()
    {
        // TTF:
        if ($this->use_ttf) {
            return $this->SetFont('generic', '', 8)
                && $this->SetFont('title', '', 14)
                && $this->SetFont('legend', '', 8)
                && $this->SetFont('x_label', '', 6)
                && $this->SetFont('y_label', '', 6)
                && $this->SetFont('x_title', '', 10)
                && $this->SetFont('y_title', '', 10);
        }
        // Fixed GD Fonts:
        return $this->SetFont('generic', 2)
            && $this->SetFont('title', 5)
            && $this->SetFont('legend', 2)
            && $this->SetFont('x_label', 1)
            && $this->SetFont('y_label', 1)
            && $this->SetFont('x_title', 3)
            && $this->SetFont('y_title', 3);
    }

    /*!
     * Sets Fixed/Truetype font parameters.
     *  \param $which_elem Is the element whose font is to be changed.
     *         It can be one of 'title', 'legend', 'generic',
     *         'x_label', 'y_label', x_title' or 'y_title'
     *  \param $which_font Can be a number (for fixed font sizes) or
     *         a string with the font pathname or filename when using TTFonts.
     *         For TTFonts, an empty string means use the default font.
     *  \param $which_size Point size (TTF only)
     * Calculates and updates internal height and width variables.
     */
    function SetFont($which_elem, $which_font, $which_size = 12)
    {
        // TTF:
        if ($this->use_ttf) {
            // Empty font name means use the default font.
            if (empty($which_font))
                $which_font = $this->default_ttfont;
            $path = $which_font;

            // First try the font name directly, if not then try with path.
            if (!is_file($path) || !is_readable($path)) {
                $path = $this->ttf_path . '/' . $which_font;
                if (!is_file($path) || !is_readable($path)) {
                    return $this->PrintError("SetFont(): Can't find TrueType font $which_font");
                }
            }

            switch ($which_elem) {
            case 'generic':
                $this->generic_font['font'] = $path;
                $this->generic_font['size'] = $which_size;
                break;
            case 'title':
                $this->title_font['font'] = $path;
                $this->title_font['size'] = $which_size;
                break;
            case 'legend':
                $this->legend_font['font'] = $path;
                $this->legend_font['size'] = $which_size;
                break;
            case 'x_label':
                $this->x_label_font['font'] = $path;
                $this->x_label_font['size'] = $which_size;
                break;
            case 'y_label':
                $this->y_label_font['font'] = $path;
                $this->y_label_font['size'] = $which_size;
                break;
            case 'x_title':
                $this->x_title_font['font'] = $path;
                $this->x_title_font['size'] = $which_size;
                break;
            case 'y_title':
                $this->y_title_font['font'] = $path;
                $this->y_title_font['size'] = $which_size;
                break;
            default:
                return $this->PrintError("SetFont(): Unknown element '$which_elem' specified.");
            }
            return TRUE;

        }

        // Fixed fonts:
        if ($which_font > 5 || $which_font < 0) {
            return $this->PrintError('SetFont(): Non-TTF font size must be 1, 2, 3, 4 or 5');
        }

        switch ($which_elem) {
        case 'generic':
            $this->generic_font['font'] = $which_font;
            $this->generic_font['height'] = ImageFontHeight($which_font);
            $this->generic_font['width'] = ImageFontWidth($which_font);
            break;
        case 'title':
           $this->title_font['font'] = $which_font;
           $this->title_font['height'] = ImageFontHeight($which_font);
           $this->title_font['width'] = ImageFontWidth($which_font);
           break;
        case 'legend':
            $this->legend_font['font'] = $which_font;
            $this->legend_font['height'] = ImageFontHeight($which_font);
            $this->legend_font['width'] = ImageFontWidth($which_font);
            break;
        case 'x_label':
            $this->x_label_font['font'] = $which_font;
            $this->x_label_font['height'] = ImageFontHeight($which_font);
            $this->x_label_font['width'] = ImageFontWidth($which_font);
            break;
        case 'y_label':
            $this->y_label_font['font'] = $which_font;
            $this->y_label_font['height'] = ImageFontHeight($which_font);
            $this->y_label_font['width'] = ImageFontWidth($which_font);
            break;
        case 'x_title':
            $this->x_title_font['font'] = $which_font;
            $this->x_title_font['height'] = ImageFontHeight($which_font);
            $this->x_title_font['width'] = ImageFontWidth($which_font);
            break;
        case 'y_title':
            $this->y_title_font['font'] = $which_font;
            $this->y_title_font['height'] = ImageFontHeight($which_font);
            $this->y_title_font['width'] = ImageFontWidth($which_font);
            break;
        default:
            return $this->PrintError("SetFont(): Unknown element '$which_elem' specified.");
        }
        return TRUE;
    }

    /*!
     * Text drawing and sizing functions:
     * ProcessText is meant for use only by DrawText and SizeText.
     *    ProcessText(True, ...)  - Draw a block of text
     *    ProcessText(False, ...) - Just return ($width, $height) of
     *       the orthogonal bounding box containing the text.
     * ProcessText is further split into separate functions for GD and TTF
     * text, due to the size of the code.
     *
     * Horizontal and vertical alignment are relative to the drawing. That is:
     * vertical text (90 deg) gets centered along Y postition with
     * v_align = 'center', and adjusted to the right of X position with
     * h_align = 'right'.  Another way to look at this is to say
     * that text rotation happens first, then alignment.
     *
     * Original multiple lines code submitted by Remi Ricard.
     * Original vertical code submitted by Marlin Viss.
     *
     * Text routines rewritten by ljb to fix alignment and position problems.
     * Here is my explanation and notes. More information and pictures will be
     * placed in the PHPlot Reference Manual.
     *
     *    + Process TTF text one line at a time, not as a block. (See below)
     *    + Flipped top vs bottom vertical alignment. The usual interpretation
     *  is: bottom align means bottom of the text is at the specified Y
     *  coordinate. For some reason, PHPlot did left/right the correct way,
     *  but had top/bottom reversed. I fixed it, and left the default valign
     *  argument as bottom, but the meaning of the default value changed.
     *
     *    For GD font text, only single-line text is handled by GD, and the
     *  basepoint is the upper left corner of each text line.
     *    For TTF text, multi-line text could be handled by GD, with the text
     *  basepoint at the lower left corner of the first line of text.
     *  (Behavior of TTF drawing routines on multi-line text is not documented.)
     *  But you cannot do left/center/right alignment on each line that way,
     *  or proper line spacing.
     *    Therefore, for either text type, we have to break up the text into
     *  lines and position each line independently.
     *
     *    There are 9 alignment modes: Horizontal = left, center, or right, and
     *  Vertical = top, center, or bottom. Alignment is interpreted relative to
     *  the image, not as the text is read. This makes sense when you consider
     *  for example X axis labels. They need to be centered below the marks
     *  (center, top alignment) regardless of the text angle.
     *
     *    GD font text is supported (by libgd) at 0 degrees and 90 degrees only.
     *  Multi-line or single line text works with any of the 9 alignment modes.
     *
     *    TTF text can be at any angle. The 9 aligment modes work for all angles,
     *  but the results might not be what you expect for multi-line text. See
     *  the PHPlot Reference Manual for pictures and details. In short, alignment
     *  applies to the orthogonal (aligned with X and Y axes) bounding box that
     *  contains the text, and to each line in the multi-line text box. Since
     *  alignment is relative to the image, 45 degree multi-line text aligns
     *  differently from 46 degree text.
     *
     *    Note that PHPlot allows multi-line text for the 3 titles, and they
     *  are only drawn at 0 degrees (main and X titles) or 90 degrees (Y title).
     *  Data labels can also be multi-line, and they can be drawn at any angle.
     *  -ljb 2007-11-03
     *
     */

    /*
     * ProcessTextGD() - Draw or size GD fixed-font text.
     * This is intended for use only by ProcessText().
     *    $draw_it : True to draw the text, False to just return the orthogonal width and height.
     *    $font_number : GD font number, 1-5.
     *    $font_width : Fixed character cell width for the font
     *    $font_height : Fixed character cell height for the font
     *    $angle : Text angle in degrees. GD only supports 0 and 90. We treat >= 45 as 90, else 0.
     *    $x, $y : Reference point for the text (ignored if !$draw_it)
     *    $color : GD color index to use for drawing the text (ignored if !$draw_it)
     *    $text : The text to draw or size. Put a newline between lines.
     *    $h_factor : Horizontal alignment factor: 0(left), .5(center), or 1(right) (ignored if !$draw_it)
     *    $v_factor : Vertical alignment factor: 0(top), .5(center), or 1(bottom) (ignored if !$draw_it)
     * Returns: True, if drawing text, or an array of ($width, $height) if not.
     */
    function ProcessTextGD($draw_it, $font_number, $font_width, $font_height, $angle, $x, $y, $color,
                           $text, $h_factor, $v_factor)
    {
        # Break up the text into lines, trim whitespace, find longest line.
        # Save the lines and length for drawing below.
        $longest = 0;
        foreach (explode("\n", $text) as $each_line) {
            $lines[] = $line = trim($each_line);
            $line_lens[] = $line_len = strlen($line);
            if ($line_len > $longest) $longest = $line_len;
        }
        $n_lines = count($lines);
        $spacing = $this->line_spacing * ($n_lines - 1); // Total inter-line spacing

        # Width, height are based on font size and longest line, line count respectively.
        # These are relative to the text angle.
        $total_width = $longest * $font_width;
        $total_height = $n_lines * $font_height + $spacing;

        if (!$draw_it) {
            if ($angle < 45) return array($total_width, $total_height);
            return array($total_height, $total_width);
        }

        $interline_step = $font_height + $this->line_spacing; // Line-to-line step

        if ($angle >= 45) {
            // Vertical text (90 degrees):
            // (Remember the alignment convention with vertical text)
            // For 90 degree text, alignment factors change like this:
            $temp = $v_factor;
            $v_factor = $h_factor;
            $h_factor = 1 - $temp;

            $draw_func = 'ImageStringUp';

            // Rotation matrix "R" for 90 degrees (with Y pointing down):
            $r00 = 0;  $r01 = 1;
            $r10 = -1; $r11 = 0;

        } else {
            // Horizontal text (0 degrees):
            $draw_func = 'ImageString';

            // Rotation matrix "R" for 0 degrees:
            $r00 = 1; $r01 = 0;
            $r10 = 0; $r11 = 1;
        }

        // Adjust for vertical alignment (horizontal text) or horizontal aligment (vertical text):
        $factor = (int)($total_height * $v_factor);
        $xpos = $x - $r01 * $factor;
        $ypos = $y - $r11 * $factor;

        # Debug callback provides the bounding box:
        if ($this->GetCallback('debug_textbox')) {
            if ($angle >= 45) {
                $bbox_width  = $total_height;
                $bbox_height = $total_width;
                $px = $xpos;
                $py = $ypos - (1 - $h_factor) * $total_width;
            } else {
                $bbox_width  = $total_width;
                $bbox_height = $total_height;
                $px = $xpos - $h_factor * $total_width;
                $py = $ypos;
            }
            $this->DoCallback('debug_textbox', $px, $py, $bbox_width, $bbox_height);
        }

        for ($i = 0; $i < $n_lines; $i++) {

            $line = $lines[$i];
            $line_len = $line_lens[$i];

            // Adjust for alignment of this line within the text block:
            $factor = (int)($line_len * $font_width * $h_factor);
            $x = $xpos - $r00 * $factor;
            $y = $ypos - $r10 * $factor;

            // Call ImageString or ImageStringUp:
            $draw_func($this->img, $font_number, $x, $y, $line, $color);

            // Step to the next line of text. This is a rotation of (x=0, y=interline_spacing)
            $xpos += $r01 * $interline_step;
            $ypos += $r11 * $interline_step;
        }
        return TRUE;
    }


    /*
     * ProcessTextTTF() - Draw or size TTF text.
     * This is intended for use only by ProcessText().
     *    $draw_it : True to draw the text, False to just return the orthogonal width and height.
     *    $font_file : Path or filename to a TTF font file.
     *    $font_size : Font size in "points".
     *    $angle : Text angle in degrees.
     *    $x, $y : Reference point for the text (ignored if !$draw_it)
     *    $color : GD color index to use for drawing the text (ignored if !$draw_it)
     *    $text : The text to draw or size. Put a newline between lines.
     *    $h_factor : Horizontal alignment factor: 0(left), .5(center), or 1(right) (ignored if !$draw_it)
     *    $v_factor : Vertical alignment factor: 0(top), .5(center), or 1(bottom) (ignored if !$draw_it)
     * Returns: True, if drawing text, or an array of ($width, $height) if not.
     */
    function ProcessTextTTF($draw_it, $font_file, $font_size, $angle, $x, $y, $color,
                            $text, $h_factor, $v_factor)
    {
        # Break up the text into lines, trim whitespace.
        # Calculate the total width and height of the text box at 0 degrees.
        # This has to be done line-by-line so the interline-spacing is right.
        # Save the trimmed line, and its 0-degree height and width for later
        # when the text is drawn.
        # Note: Total height = sum of each line height plus inter-line spacing
        #   (which is added after the loop). Total width = width of widest line.
        $total_height = 0;
        $total_width = 0;
        foreach (explode("\n", $text) as $each_line) {
            $lines[] = $line = trim($each_line);
            $bbox = ImageTTFBBox($font_size, 0, $font_file, $line);
            $height = $bbox[1] - $bbox[5];
            $width = $bbox[2] - $bbox[0];
            $total_height += $height;
            if ($width > $total_width) $total_width = $width;
            $line_widths[] = $width;
            $line_heights[] = $height;
        }
        $n_lines = count($lines);
        $total_height += ($n_lines - 1) * $this->line_spacing;

        # Calculate the rotation matrix for the text's angle. Remember that GD points Y down,
        # so the sin() terms change sign.
        $theta = deg2rad($angle);
        $cos_t = cos($theta);
        $sin_t = sin($theta);
        $r00 = $cos_t;    $r01 = $sin_t;
        $r10 = -$sin_t;   $r11 = $cos_t;

        # Make a bounding box of the right size, with upper left corner at (0,0).
        # By convention, the point order is: LL, LR, UR, UL.
        # Note this is still working with the text at 0 degrees.
        $b[0] = 0;             $b[1] = $total_height;
        $b[2] = $total_width;  $b[3] = $b[1];
        $b[4] = $b[2];         $b[5] = 0;
        $b[6] = $b[0];         $b[7] = $b[5];

        # Rotate the bounding box, then offset to the reference point:
        for ($i = 0; $i < 8; $i += 2) {
            $x_b = $b[$i];
            $y_b = $b[$i+1];
            $c[$i]   = $x + $r00 * $x_b + $r01 * $y_b;
            $c[$i+1] = $y + $r10 * $x_b + $r11 * $y_b;
        }

        # Get an orthogonal (aligned with X and Y axes) bounding box around it, by
        # finding the min and max X and Y:
        $bbox_ref_x = $bbox_max_x = $c[0];
        $bbox_ref_y = $bbox_max_y = $c[1];
        for ($i = 2; $i < 8; $i += 2) {
            $x_b = $c[$i];
            if ($x_b < $bbox_ref_x) $bbox_ref_x = $x_b; elseif ($bbox_max_x < $x_b) $bbox_max_x = $x_b;
            $y_b = $c[$i+1];
            if ($y_b < $bbox_ref_y) $bbox_ref_y = $y_b; elseif ($bbox_max_y < $y_b) $bbox_max_y = $y_b;
        }
        $bbox_width = $bbox_max_x - $bbox_ref_x;
        $bbox_height = $bbox_max_y - $bbox_ref_y;

        if (!$draw_it) {
            # Return the bounding box, rounded up (so it always contains the text):
            return array((int)ceil($bbox_width), (int)ceil($bbox_height));
        }

        # Calculate the offsets from the supplied reference point to the
        # required upper-left corner of the text.
        # Start at the reference point at the upper left corner of the bounding
        # box (bbox_ref_x, bbox_ref_y) then adjust it for the 9 point alignment.
        # h,v_factor are 0,0 for top,left, .5,.5 for center,center, 1,1 for bottom,right.
        #    $off_x = $bbox_ref_x + $bbox_width * $h_factor - $x;
        #    $off_y = $bbox_ref_y + $bbox_height * $v_factor - $y;
        # Then use that offset to calculate back to the supplied reference point x, y
        # to get the text base point.
        #    $qx = $x - $off_x;
        #    $qy = $y - $off_y;
        # Reduces to:
        $qx = 2 * $x - $bbox_ref_x - $bbox_width * $h_factor;
        $qy = 2 * $y - $bbox_ref_y - $bbox_height * $v_factor;

        # Check for debug callback. Don't calculate bounding box unless it is wanted.
        if ($this->GetCallback('debug_textbox')) {
            # Calculate the orthogonal bounding box coordinates for debug testing.

            # qx, qy is upper left corner relative to the text.
            # Calculate px,py: upper left corner (absolute) of the bounding box.
            # There are 4 equation sets for this, depending on the quadrant:
            if ($sin_t > 0) {
                if ($cos_t > 0) {
                    # Quadrant: 0d - 90d:
                    $px = $qx; $py = $qy - $total_width * $sin_t;
                } else {
                    # Quadrant: 90d - 180d:
                   $px = $qx + $total_width * $cos_t; $py = $qy - $bbox_height;
                }
            } else {
                if ($cos_t < 0) {
                    # Quadrant: 180d - 270d:
                    $px = $qx - $bbox_width; $py = $qy + $total_height * $cos_t;
                } else {
                    # Quadrant: 270d - 360d:
                    $px = $qx + $total_height * $sin_t; $py = $qy;
                }
            }
            $this->DoCallback('debug_textbox', $px, $py, $bbox_width, $bbox_height);
        }

        # Since alignment is applied after rotation, which parameter is used
        # to control alignment of each line within the text box varies with
        # the angle.
        #   Angle (degrees):       Line alignment controlled by:
        #  -45 < angle <= 45          h_align
        #   45 < angle <= 135         reversed v_align
        #  135 < angle <= 225         reversed h_align
        #  225 < angle <= 315         v_align
        if ($cos_t >= $sin_t) {
            if ($cos_t >= -$sin_t) $line_align_factor = $h_factor;
            else $line_align_factor = $v_factor;
        } else {
            if ($cos_t >= -$sin_t) $line_align_factor = 1-$v_factor;
            else $line_align_factor = 1-$h_factor;
        }

        # Now we have the start point, spacing and in-line alignment factor.
        # We are finally ready to start drawing the text, line by line.
        for ($i = 0; $i < $n_lines; $i++) {
            $line = $lines[$i];
            # These are height and width at 0 degrees, calculated above:
            $height = $line_heights[$i];
            $width = $line_widths[$i];

            # For drawing TTF text, the lower left corner is the base point.
            # The adjustment is inside the loop, since lines can have different
            # heights. The following also adjusts for horizontal (relative to
            # the text) alignment of the current line within the box.
            # What is happening is rotation of this vector by the text angle:
            #    (x = (total_width - line_width) * factor, y = line_height)

            $width_factor = ($total_width - $width) * $line_align_factor;
            $rx = $qx + $r00 * $width_factor + $r01 * $height;
            $ry = $qy + $r10 * $width_factor + $r11 * $height;

            # Finally, draw the text:
            ImageTTFText($this->img, $font_size, $angle, $rx, $ry, $color, $font_file, $line);

            # Step to position of next line.
            # This is a rotation of (x=0,y=text_height+line_height) by $angle:
            $interline_step = $this->line_spacing + $height;
            $qx += $r01 * $interline_step;
            $qy += $r11 * $interline_step;
        }
        return True;
    }

    /*
     * ProcessText() - Wrapper for ProcessTextTTF() and ProcessTextGD(). See notes above.
     * This is intended for use from within PHPlot only, and only by DrawText() and SizeText().
     *    $draw_it : True to draw the text, False to just return the orthogonal width and height.
     *    $font : PHPlot font array. (Contents depend on use_ttf flag).
     *    $angle : Text angle in degrees
     *    $x, $y : Reference point for the text (ignored if !$draw_it)
     *    $color : GD color index to use for drawing the text (ignored if !$draw_it)
     *    $text : The text to draw or size. Put a newline between lines.
     *    $halign : Horizontal alignment: left, center, or right (ignored if !$draw_it)
     *    $valign : Vertical alignment: top, center, or bottom (ignored if !$draw_it)
     *      Note: Alignment is relative to the image, not the text.
     * Returns: True, if drawing text, or an array of ($width, $height) if not.
     */
    function ProcessText($draw_it, $font, $angle, $x, $y, $color, $text, $halign, $valign)
    {
        # Empty text case:
        if ($text === '') {
            if ($draw_it) return TRUE;
            return array(0, 0);
        }

        # Calculate width and height offset factors using the alignment args:
        if ($valign == 'top') $v_factor = 0;
        elseif ($valign == 'center') $v_factor = 0.5;
        else $v_factor = 1.0; # 'bottom'
        if ($halign == 'left') $h_factor = 0;
        elseif ($halign == 'center') $h_factor = 0.5;
        else $h_factor = 1.0; # 'right'

        if ($this->use_ttf) {
            return $this->ProcessTextTTF($draw_it, $font['font'], $font['size'],
                                         $angle, $x, $y, $color, $text, $h_factor, $v_factor);
        }

        return $this->ProcessTextGD($draw_it, $font['font'], $font['width'], $font['height'],
                                    $angle, $x, $y, $color, $text, $h_factor, $v_factor);
    }


    /*
     * Draws a block of text. See comments above before ProcessText().
     *    $which_font : PHPlot font array. (Contents depend on use_ttf flag).
     *    $which_angle : Text angle in degrees
     *    $which_xpos, $which_ypos: Reference point for the text
     *    $which_color : GD color index to use for drawing the text
     *    $which_text :  The text to draw, with newlines (\n) between lines.
     *    $which_halign : Horizontal (relative to the image) alignment: left, center, or right.
     *    $which_valign : Vertical (relative to the image) alignment: top, center, or bottom.
     */
    function DrawText($which_font, $which_angle, $which_xpos, $which_ypos, $which_color, $which_text,
                      $which_halign = 'left', $which_valign = 'bottom')
    {
        return $this->ProcessText(True,
                           $which_font, $which_angle, $which_xpos, $which_ypos,
                           $which_color, $which_text, $which_halign, $which_valign);
    }

    /*
     * Returns the size of block of text. This is the orthogonal width and height of a bounding
     * box aligned with the X and Y axes of the text. Only for angle=0 is this the actual
     * width and height of the text block, but for any angle it is the amount of space needed
     * to contain the text.
     *    $which_font : PHPlot font array. (Contents depend on use_ttf flag).
     *    $which_angle : Text angle in degrees
     *    $which_text :  The text to draw, with newlines (\n) between lines.
     * Returns a two element array with: $width, $height.
     * This is just a wrapper for ProcessText() - see above.
     */
    function SizeText($which_font, $which_angle, $which_text)
    {
        // Color, position, and alignment are not used when calculating the size.
        return $this->ProcessText(False,
                           $which_font, $which_angle, 0, 0, 1, $which_text, '', '');
    }


/////////////////////////////////////////////
///////////            INPUT / OUTPUT CONTROL
/////////////////////////////////////////////

    /*!
     * Sets output file format.
     */
    function SetFileFormat($format)
    {
        $asked = $this->CheckOption($format, 'jpg, png, gif, wbmp', __FUNCTION__);
        if (!$asked) return False;
        switch ($asked) {
        case 'jpg':
            $format_test = IMG_JPG;
            break;
        case 'png':
            $format_test = IMG_PNG;
            break;
        case 'gif':
            $format_test = IMG_GIF;
            break;
        case 'wbmp':
            $format_test = IMG_WBMP;
            break;
        }
        if (!(imagetypes() & $format_test)) {
            return $this->PrintError("SetFileFormat(): File format '$format' not supported");
        }
        $this->file_format = $asked;
        return TRUE;
    }


    /*!
     * Selects an input file to be used as graph background and scales or tiles this image
     * to fit the sizes.
     *  \param input_file string Path to the file to be used (jpeg, png and gif accepted)
     *  \param mode       string 'centeredtile', 'tile', 'scale' (the image to the graph's size)
     */
    function SetBgImage($input_file, $mode='centeredtile')
    {
        $this->bgmode = $this->CheckOption($mode, 'tile, centeredtile, scale', __FUNCTION__);
        $this->bgimg  = $input_file;
        return (boolean)$this->bgmode;
    }

    /*!
     * Selects an input file to be used as plot area background and scales or tiles this image
     * to fit the sizes.
     *  \param input_file string Path to the file to be used (jpeg, png and gif accepted)
     *  \param mode       string 'centeredtile', 'tile', 'scale' (the image to the graph's size)
     */
    function SetPlotAreaBgImage($input_file, $mode='tile')
    {
        $this->plotbgmode = $this->CheckOption($mode, 'tile, centeredtile, scale', __FUNCTION__);
        $this->plotbgimg  = $input_file;
        return (boolean)$this->plotbgmode;
    }


    /*!
     * Sets the name of the file to be used as output file.
     */
    function SetOutputFile($which_output_file)
    {
        $this->output_file = $which_output_file;
        return TRUE;
    }

    /*!
     * Sets the output image as 'inline', that is: no Content-Type headers are sent
     * to the browser. Needed if you want to embed the images.
     */
    function SetIsInline($which_ii)
    {
        $this->is_inline = (bool)$which_ii;
        return TRUE;
    }


    /*!
     * Performs the actual outputting of the generated graph.
     */
    function PrintImage()
    {
        // Browser cache stuff submitted by Thiemo Nagel
        if ( (! $this->browser_cache) && (! $this->is_inline)) {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }

        switch($this->file_format) {
        case 'png':
            if (! $this->is_inline) {
                Header('Content-type: image/png');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImagePng($this->img, $this->output_file);
            } else {
                ImagePng($this->img);
            }
            break;
        case 'jpg':
            if (! $this->is_inline) {
                Header('Content-type: image/jpeg');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImageJPEG($this->img, $this->output_file);
            } else {
                ImageJPEG($this->img);
            }
            break;
        case 'gif':
            if (! $this->is_inline) {
                Header('Content-type: image/gif');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImageGIF($this->img, $this->output_file);
            } else {
                ImageGIF($this->img);
            }

            break;
        case 'wbmp':        // wireless bitmap, 2 bit.
            if (! $this->is_inline) {
                Header('Content-type: image/wbmp');
            }
            if ($this->is_inline && $this->output_file != '') {
                ImageWBMP($this->img, $this->output_file);
            } else {
                ImageWBMP($this->img);
            }

            break;
        default:
            return $this->PrintError('PrintImage(): Please select an image type!');
        }
        return TRUE;
    }

    /*!
     *  Error handling for 'fatal' errors:
     *   $error_message       Text of the error message
     *  Standard output from PHPlot is expected to be an image file, such as
     *  when handling an <img> tag browser request. So it is not permitted to
     *  output text to standard output. (You should have display_errors=off)
     *  Here is how PHPlot handles fatal errors:
     *    + Write the error message into an image, and output the image.
     *    + If no image can be output, write nothing and produce an HTTP
     *      error header.
     *    + Trigger a user-level error containing the error message.
     *      If no error handler was set up, the script will log the
     *      error and exit with non-zero status.
     *  
     *  PrintError() and DrawError() are now equivalent. Both are provided for
     *  compatibility. (In earlier releases, PrintError sent the message to
     *  stdout only, and DrawError sent it in an image only.)
     *
     *  This function does not return, unless the calling script has set up
     *  an error handler which does not exit. In that case, PrintError will
     *  return False. But not all of PHPlot will handle this correctly, so
     *  it is probably a bad idea for an error handler to return.
     */
    function PrintError($error_message)
    {
        // Be sure not to loop recursively, e.g. PrintError - PrintImage - PrintError.
        if (isset($this->in_error)) return FALSE;
        $this->in_error = TRUE;

        // Output an image containing the error message:
        if (!empty($this->img)) {
            $ypos = $this->image_height/2;
            $xpos = $this->image_width/2;
            $bgcolor = ImageColorResolve($this->img, 255, 255, 255);
            $fgcolor = ImageColorResolve($this->img, 0, 0, 0);
            ImageFilledRectangle($this->img, 0, 0, $this->image_width, $this->image_height, $bgcolor);

            // Switch to built-in fonts, in case of error with TrueType fonts:
            $this->SetUseTTF(FALSE);

            $this->DrawText($this->generic_font, 0, $xpos, $ypos, $fgcolor,
                            wordwrap($error_message), 'center', 'center');

            $this->PrintImage();
        } elseif (! $this->is_inline) {
            Header('HTTP/1.0 500 Internal Server Error');
        }
        trigger_error($error_message, E_USER_ERROR);
        unset($this->in_error);
        return FALSE;  # In case error handler returns, rather than doing exit().
    }

    /*!
     * Display an error message and exit.
     * This is provided for backward compatibility only. Use PrintError() instead.
     *   $error_message       Text of the error message
     *   $where_x, $where_y   Ignored, provided for compatibility.
     */
    function DrawError($error_message, $where_x = NULL, $where_y = NULL)
    {
        return $this->PrintError($error_message);
    }

/////////////////////////////////////////////
///////////                            LABELS
/////////////////////////////////////////////


    /*!
     * Sets position for X labels following data points.
     */
    function SetXDataLabelPos($which_xdlp)
    {
        $which_xdlp = $this->CheckOption($which_xdlp, 'plotdown, plotup, both, xaxis, all, none',
                                         __FUNCTION__);
        if (!$which_xdlp) return FALSE;
        $this->x_data_label_pos = $which_xdlp;
        if ($this->x_data_label_pos != 'none')
            $this->x_tick_label_pos = 'none';

        return TRUE;
    }

    /*!
     * Sets position for Y labels near data points.
     * For past compatability we accept plotleft, ...but pass it to SetTickLabelPos
     * eventually to specify how far up/down or left/right of the data point
     */
    function SetYDataLabelPos($which_ydlp, $which_distance_from_point=0)
    {
        $which_ydlp = $this->CheckOption($which_ydlp, 'plotleft, plotright, both, yaxis, all, plotin, none',
                                          __FUNCTION__);
        if (!$which_ydlp) return FALSE;
        $this->y_data_label_pos = $which_ydlp;
        //This bit in SetYDataLabelPos about plotleft is for those who were 
        //using this function to set SetYTickLabelPos.
        if ( ($which_ydlp == 'plotleft') || ($which_ydlp == 'plotright') || 
             ($which_ydlp == 'both') || ($which_ydlp == 'yaxis') ) { 

            //Call sety_TICK_labelpos instead of sety_DATA_labelpos
            $this->SetYTickLabelPos($which_ydlp);

        } elseif ($which_ydlp != 'none') { 
            //right now its plotin or none
            $this->y_data_label_pos = 'plotin';
        }

        return TRUE;
    }


    /*!
     * Sets position for X labels following ticks (hence grid lines)
     */
    function SetXTickLabelPos($which_xtlp)
    {
        $which_xtlp = $this->CheckOption($which_xtlp, 'plotdown, plotup, both, xaxis, all, none',
                                         __FUNCTION__);
        if (!$which_xtlp) return FALSE;
        $this->x_tick_label_pos = $which_xtlp;
        if ($which_xtlp != 'none')
            $this->x_data_label_pos = 'none';

        return TRUE;
    }

    /*!
     * Sets position for Y labels following ticks (hence grid lines)
     */
    function SetYTickLabelPos($which_ytlp)
    {
        $this->y_tick_label_pos = $this->CheckOption($which_ytlp, 'plotleft, plotright, both, yaxis, all, none',
                                                      __FUNCTION__);
        return (boolean)$this->y_tick_label_pos;
    }

    /*!
     * Sets type for tick and data labels on X axis.
     * \note 'title' type left for backwards compatibility.
     */
    function SetXLabelType($which_xlt)
    {
        $this->x_label_type = $this->CheckOption($which_xlt, 'data, time, title', __FUNCTION__);
        return (boolean)$this->x_label_type;
    }

    /*!
     * Sets type for tick and data labels on Y axis.
     */
    function SetYLabelType($which_ylt)
    {
        $this->y_label_type = $this->CheckOption($which_ylt, 'data, time', __FUNCTION__);
        return (boolean)$this->y_label_type;
    }

    function SetXTimeFormat($which_xtf)
    {
        $this->x_time_format = $which_xtf;
        return TRUE;
    }

    function SetYTimeFormat($which_ytf)
    {
        $this->y_time_format = $which_ytf;
        return TRUE;
    }

    function SetNumberFormat($decimal_point, $thousands_sep)
    {
        $this->decimal_point = $decimal_point;
        $this->thousands_sep = $thousands_sep;
        return TRUE;
    }


    function SetXLabelAngle($which_xla)
    {
        $this->x_label_angle = $which_xla;
        return TRUE;
    }

    function SetYLabelAngle($which_yla)
    {
        $this->y_label_angle = $which_yla;
        return TRUE;
    }

/////////////////////////////////////////////
///////////                              MISC
/////////////////////////////////////////////

    /*!
     * Checks the valididy of an option.
     *   $which_opt  String to check, such as the provided value of a function argument.
     *   $which_acc  String of accepted choices. Must be lower-case, and separated
     *               by exactly ', ' (comma, space).
     *   $which_func Name of the calling function, for error messages.
     * Returns the supplied option value, downcased and trimmed, if it is valid.
     * Reports an error if the supplied option is not valid.
     */
    function CheckOption($which_opt, $which_acc, $which_func)
    {
        $asked = strtolower(trim($which_opt));

        # Look for the supplied value in a comma/space separated list.
        if (strpos(", $which_acc,", ", $asked,") !== False)
            return $asked;

        $this->PrintError("$which_func(): '$which_opt' not in available choices: '$which_acc'.");
        return NULL;
    }


    /*!
     *  \note Submitted by Thiemo Nagel
     */
    function SetBrowserCache($which_browser_cache)
    {
        $this->browser_cache = $which_browser_cache;
        return TRUE;
    }

    /*!
     * Whether to show the final image or not
     */
    function SetPrintImage($which_pi)
    {
        $this->print_image = $which_pi;
        return TRUE;
    }

    /*!
     * Sets the graph's legend. If argument is not an array, appends it to the legend.
     */
    function SetLegend($which_leg)
    {
        if (is_array($which_leg)) {             // use array
            $this->legend = $which_leg;
        } elseif (! is_null($which_leg)) {     // append string
            $this->legend[] = $which_leg;
        } else {
            return $this->PrintError("SetLegend(): argument must not be null.");
        }
        return TRUE;
    }

    /*!
     * Specifies the position of the legend's upper/leftmost corner,
     * in pixel (device) coordinates.
     */
    function SetLegendPixels($which_x, $which_y)
    {
        $this->legend_x_pos = $which_x;
        $this->legend_y_pos = $which_y;
        // Make sure this is unset, meaning we have pixel coords:
        unset($this->legend_xy_world);

        return TRUE;
    }

    /*!
     * Specifies the position of the legend's upper/leftmost corner,
     * in world (data space) coordinates.
     * Since the scale factor to convert world to pixel coordinates
     * is probably not available, set a flag and defer conversion
     * to later.
     */
    function SetLegendWorld($which_x, $which_y)
    {
        $this->legend_x_pos = $which_x;
        $this->legend_y_pos = $which_y;
        $this->legend_xy_world = True;

        return TRUE;
    }

    /*
     * Set legend text alignment, color box alignment, and style options
     *     $text_align accepts 'left' or 'right'.
     *     $colorbox_align accepts 'left', 'right', 'none', or missing/empty. If missing or empty,
     *        the same alignment as $text_align is used. Color box is positioned first.
     *     $style is reserved for future use.
     */
    function SetLegendStyle($text_align, $colorbox_align = '', $style = '')
    {
        $this->legend_text_align = $this->CheckOption($text_align, 'left, right', __FUNCTION__);
        if (empty($colorbox_align))
            $this->legend_colorbox_align = $this->legend_text_align;
        else
            $this->legend_colorbox_align = $this->CheckOption($colorbox_align, 'left, right, none', __FUNCTION__);
        return ((boolean)$this->legend_text_align && (boolean)$this->legend_colorbox_align);
    }

    /*!
     * Accepted values are: left, sides, none, full
     */
    function SetPlotBorderType($pbt)
    {
        $this->plot_border_type = $this->CheckOption($pbt, 'left, sides, none, full', __FUNCTION__);
        return (boolean)$this->plot_border_type;
    }

    /*!
     * Accepted values are: raised, plain
     */
    function SetImageBorderType($sibt)
    {
        $this->image_border_type = $this->CheckOption($sibt, 'raised, plain', __FUNCTION__);
        return (boolean)$this->image_border_type;
    }


    /*!
     * \param dpab bool
     */
    function SetDrawPlotAreaBackground($dpab)
    {
        $this->draw_plot_area_background = (bool)$dpab;
        return TRUE;
    }


    /*!
     * \param dyg bool
     */
    function SetDrawYGrid($dyg)
    {
        $this->draw_y_grid = (bool)$dyg;
        return TRUE;
    }


    /*!
     * \param dxg bool
     */
    function SetDrawXGrid($dxg)
    {
        $this->draw_x_grid = (bool)$dxg;
        return TRUE;
    }


    /*!
     * \param ddg bool
     */
    function SetDrawDashedGrid($ddg)
    {
        $this->dashed_grid = (bool)$ddg;
        return TRUE;
    }


    /*!
     * \param dxdl bool
     */
    function SetDrawXDataLabelLines($dxdl)
    {
        $this->draw_x_data_label_lines = (bool)$dxdl;
        return TRUE;
    }


    /*!
     * TODO: draw_y_data_label_lines not implemented.
     * \param dydl bool
     */
    function SetDrawYDataLabelLines($dydl)
    {
        $this->draw_y_data_label_lines = $dydl;
        return TRUE;
    }

    /*!
     * Sets the graph's title.
     * TODO: add parameter to choose title placement: left, right, centered=
     */
    function SetTitle($which_title)
    {
        $this->title_txt = $which_title;
        return TRUE;
    }

    /*!
     * Sets the X axis title and position.
     */
    function SetXTitle($which_xtitle, $which_xpos = 'plotdown')
    {
        if ($which_xtitle == '')
            $which_xpos = 'none';

        $this->x_title_pos = $this->CheckOption($which_xpos, 'plotdown, plotup, both, none', __FUNCTION__);
        if (!$this->x_title_pos) return FALSE;
        $this->x_title_txt = $which_xtitle;
        return TRUE;
    }


    /*!
     * Sets the Y axis title and position.
     */
    function SetYTitle($which_ytitle, $which_ypos = 'plotleft')
    {
        if ($which_ytitle == '')
            $which_ypos = 'none';

        $this->y_title_pos = $this->CheckOption($which_ypos, 'plotleft, plotright, both, none', __FUNCTION__);
        if (!$this->y_title_pos) return FALSE;
        $this->y_title_txt = $which_ytitle;
        return TRUE;
    }

    /*!
     * Sets the size of the drop shadow for bar and pie charts.
     * \param which_s int Size in pixels.
     */
    function SetShading($which_s)
    {
        $this->shading = (int)$which_s;
        return TRUE;
    }

    function SetPlotType($which_pt)
    {
        $this->plot_type = $this->CheckOption($which_pt,
                           'bars, stackedbars, lines, linepoints, area, points, pie, thinbarline, squared',
                            __FUNCTION__);
        return (boolean)$this->plot_type;
    }

    /*!
     * Sets the position of Y axis.
     * \param pos int Position in world coordinates.
     */
    function SetYAxisPosition($pos)
    {
        $this->y_axis_position = (int)$pos;
        return TRUE;
    }

    /*!
     * Sets the position of X axis.
     * \param pos int Position in world coordinates.
     */
    function SetXAxisPosition($pos)
    {
        $this->x_axis_position = (int)$pos;
        return TRUE;
    }


    function SetXScaleType($which_xst)
    {
        $this->xscale_type = $this->CheckOption($which_xst, 'linear, log', __FUNCTION__);
        return (boolean)$this->xscale_type;
    }

    function SetYScaleType($which_yst)
    {
        $this->yscale_type = $this->CheckOption($which_yst, 'linear, log',  __FUNCTION__);
        return (boolean)$this->yscale_type;
    }

    function SetPrecisionX($which_prec)
    {
        $this->x_precision = $which_prec;
        $this->SetXLabelType('data');
        return TRUE;
    }

    function SetPrecisionY($which_prec)
    {
        $this->y_precision = $which_prec;
        $this->SetYLabelType('data');
        return TRUE;
    }

    function SetErrorBarLineWidth($which_seblw)
    {
        $this->error_bar_line_width = $which_seblw;
        return TRUE;
    }

    function SetLabelScalePosition($which_blp)
    {
        //0 to 1
        $this->label_scale_position = $which_blp;
        return TRUE;
    }

    function SetErrorBarSize($which_ebs)
    {
        //in pixels
        $this->error_bar_size = $which_ebs;
        return TRUE;
    }

    /*!
     * Can be one of: 'tee', 'line'
     */
    function SetErrorBarShape($which_ebs)
    {
        $this->error_bar_shape = $this->CheckOption($which_ebs, 'tee, line', __FUNCTION__);
        return (boolean)$this->error_bar_shape;
    }

    /*!
     * Sets point shape for each data set via an array.
     * Shape can be one of: 'halfline', 'line', 'plus', 'cross', 'rect', 'circle', 'dot',
     * 'diamond', 'triangle', 'trianglemid', or 'none'.
     */
    function SetPointShapes($which_pt)
    {
        if (is_null($which_pt)) {
            // Do nothing, use default value.
        } else if (is_array($which_pt)) {
            // Did we get an array with point shapes?
            $this->point_shapes = $which_pt;
        } else {
            // Single value into array
            $this->point_shapes = array($which_pt);
        }

        foreach ($this->point_shapes as $shape)
        {
            if (!$this->CheckOption($shape,
               'halfline, line, plus, cross, rect, circle, dot, diamond, triangle, trianglemid, none',
                __FUNCTION__))
                return FALSE;
        }

        // Make both point_shapes and point_sizes same size.
        $ps = count($this->point_sizes);
        $pt = count($this->point_shapes);

        if ($ps < $pt) {
            $this->pad_array($this->point_sizes, $pt);
        } else if ($pt > $ps) {
            $this->pad_array($this->point_shapes, $ps);
        }
        return TRUE;
    }

    /*!
     * Sets the point size for point plots.
     * \param ps int Size in pixels.
     * \note Test this more extensively
     */
    function SetPointSizes($which_ps)
    {
        if (is_null($which_ps)) {
            // Do nothing, use default value.
        } else if (is_array($which_ps)) {
            // Did we get an array with point sizes?
            $this->point_sizes = $which_ps;
        } else {
            // Single value into array
            $this->point_sizes = array($which_ps);
        }

        // Make both point_shapes and point_sizes same size.
        $ps = count($this->point_sizes);
        $pt = count($this->point_shapes);

        if ($ps < $pt) {
            $this->pad_array($this->point_sizes, $pt);
        } else if ($pt > $ps) {
            $this->pad_array($this->point_shapes, $ps);
        }

        // Fix odd point sizes for point shapes which need it
        for ($i = 0; $i < $pt; $i++) {
            if ($this->point_shapes[$i] == 'diamond' or $this->point_shapes[$i] == 'triangle') {
                if ($this->point_sizes[$i] % 2 != 0) {
                    $this->point_sizes[$i]++;
                }
            }
        }
        return TRUE;
    }


    /*!
     * Tells not to draw lines for missing Y data. Only works with 'lines' and 'squared' plots.
     * \param bl bool
     */
    function SetDrawBrokenLines($bl)
    {
        $this->draw_broken_lines = (bool)$bl;
        return TRUE;
    }


    /*!
     *  text-data: ('label', y1, y2, y3, ...)
     *  text-data-single: ('label', data), for some pie charts.
     *  data-data: ('label', x, y1, y2, y3, ...)
     *  data-data-error: ('label', x1, y1, e1+, e2-, y2, e2+, e2-, y3, e3+, e3-, ...)
     */
    function SetDataType($which_dt)
    {
        //The next four lines are for past compatibility.
        if ($which_dt == 'text-linear') { $which_dt = 'text-data'; }
        elseif ($which_dt == 'linear-linear') { $which_dt = 'data-data'; }
        elseif ($which_dt == 'linear-linear-error') { $which_dt = 'data-data-error'; }
        elseif ($which_dt == 'text-data-pie') { $which_dt = 'text-data-single'; }


        $this->data_type = $this->CheckOption($which_dt, 'text-data, text-data-single, '.
                                                         'data-data, data-data-error', __FUNCTION__);
        return (boolean)$this->data_type;
    }

    /*!
     * Copy the array passed as data values. We convert to numerical indexes, for its
     * use for (or while) loops, which sometimes are faster. Performance improvements
     * vary from 28% in DrawLines() to 49% in DrawArea() for plot drawing functions.
     */
    function SetDataValues(&$which_dv)
    {
        $this->num_data_rows = count($which_dv);
        $this->total_records = 0;               // Perform some useful calculations.
        $this->records_per_group = 1;
        for ($i = 0, $recs = 0; $i < $this->num_data_rows; $i++) {
            // Copy
            $this->data[$i] = array_values($which_dv[$i]);   // convert to numerical indices.

            // Compute some values
            $recs = count($this->data[$i]);
            $this->total_records += $recs;

            if ($recs > $this->records_per_group)
                $this->records_per_group = $recs;

            $this->num_recs[$i] = $recs;
        }
        return TRUE;
    }

    /*!
     * Pad styles arrays for later use by plot drawing functions:
     * This removes the need for $max_data_colors, etc. and $color_index = $color_index % $max_data_colors
     * in DrawBars(), DrawLines(), etc.
     */
    function PadArrays()
    {
        $this->pad_array($this->line_widths, $this->records_per_group);
        $this->pad_array($this->line_styles, $this->records_per_group);

        $this->pad_array($this->data_colors, $this->records_per_group);
        $this->pad_array($this->data_border_colors, $this->records_per_group);
        $this->pad_array($this->error_bar_colors, $this->records_per_group);

        $this->SetDataColors();
        $this->SetDataBorderColors();
        $this->SetErrorBarColors();

        return TRUE;
    }

    /*!
     * Pads an array with itself. This only works on 0-based sequential integer indexed arrays.
     *  \param arr array  Original array (reference), or scalar.
     *  \param size int   Minimum size of the resulting array.
     * If $arr is a scalar, it will be converted first to a single element array.
     * If $arr has at least $size elements, it is unchanged.
     * Otherwise, append elements of $arr to itself until it reaches $size elements.
     */
    function pad_array(&$arr, $size)
    {
        if (! is_array($arr)) {
            $arr = array($arr);
        }
        $n = count($arr);
        $base = 0;
        while ($n < $size) $arr[$n++] = $arr[$base++];
    }

    /*
     * Format a floating-point number.
     * This is like PHP's number_format, but uses class variables for separators.
     * The separators will default to locale-specific values, if available.
     */
    function number_format($number, $decimals=0)
    {
        if (!isset($this->decimal_point) || !isset($this->thousands_sep)) {
            // Load locale-specific values from environment:
            @setlocale(LC_ALL, '');
            // Fetch locale settings:
            $locale = @localeconv();
            if (!empty($locale) && isset($locale['decimal_point']) &&
                    isset($locale['thousands_sep'])) {
              $this->decimal_point = $locale['decimal_point'];
              $this->thousands_sep = $locale['thousands_sep'];
            } else {
              // Locale information not available.
              $this->decimal_point = '.';
              $this->thousands_sep = ',';
            }
        }
        return number_format($number, $decimals, $this->decimal_point, $this->thousands_sep);
    }

    /*
     * Register a callback (hook) function
     *   reason - A pre-defined name where a callback can be defined.
     *   function - The name of a function to register for callback, or an instance/method
     *      pair in an array (see 'callbacks' in the PHP reference manual).
     *   arg - Optional argument to supply to the callback function when it is triggered.
     *      (Often called "clientData")
     * Returns: True if the callback reason is valid, else False.
     */
    function SetCallback($reason, $function, $arg = NULL)
    {
        // Use array_key_exists because valid reason keys have NULL as value.
        if (!array_key_exists($reason, $this->callbacks))
            return False;
        $this->callbacks[$reason] = array($function, $arg);
        return True;
    }

    /*
     * Return the name of a function registered for callback. See SetCallBack.
     *   reason - A pre-defined name where a callback can be defined.
     * Returns the current callback function (name or array) for the given reason,
     * or False if there was no active callback or the reason is not valid.
     * Note you can safely test the return value with a simple 'if', as
     * no valid function name evaluates to false.
     */
    function GetCallback($reason)
    {
        if (isset($this->callbacks[$reason]))
            return $this->callbacks[$reason][0];
        return False;
    }

    /*
     * Un-register (remove) a function registered for callback.
     *   reason - A pre-defined name where a callback can be defined.
     * Returns: True if it was a valid callback reason, else False.
     * Note: Returns True whether or not there was a callback registered.
     */
    function RemoveCallback($reason)
    {
        if (!array_key_exists($reason, $this->callbacks))
            return False;
        $this->callbacks[$reason] = NULL;
        return True;
    }

    /*
     * Invoke a callback, if one is registered.
     * Accepts a variable number of arguments >= 1:
     *    reason : A string naming the callback.
     *    ... : Zero or more additional arguments to be passed to the
     *      callback function, after the passthru argument:
     *           callback_function($image, $passthru, ...)
     * Returns: nothing.
     */
    function DoCallback()  # Note: Variable arguments
    {
        $args = func_get_args();
        $reason = $args[0];
        if (!isset($this->callbacks[$reason]))
            return;
        list($function, $args[0]) = $this->callbacks[$reason];
        array_unshift($args, $this->img);
        # Now args[] looks like: img, passthru, extra args...
        call_user_func_array($function, $args);
    }


//////////////////////////////////////////////////////////
///////////         DATA ANALYSIS, SCALING AND TRANSLATION
//////////////////////////////////////////////////////////

    /*!
     * Analizes data and sets up internal maxima and minima
     * Needed by: CalcMargins(), ...
     *   Text-Data is different than data-data graphs. For them what
     *   we have, instead of X values, is # of records equally spaced on data.
     *   text-data is passed in as $data[] = (title, y1, y2, y3, y4, ...)
     *   data-data is passed in as $data[] = (title, x, y1, y2, y3, y4, ...)
     */
    function FindDataLimits()
    {
        // Set some default min and max values before running through the data
        switch ($this->data_type) {
        case 'text-data':
        case 'text-data-single':
            $minx = 0;
            $maxx = $this->num_data_rows - 1 ;
            $miny = $this->data[0][1];
            $maxy = $miny;
            break;
        default:  //Everything else: data-data, etc, take first value
            $minx = $this->data[0][1];
            $maxx = $minx;
            $miny = $this->data[0][2];
            $maxy = $miny;
            break;
        }

        $mine = 0;  // Maximum value for the -error bar (assume error bars always > 0)
        $maxe = 0;  // Maximum value for the +error bar (assume error bars always > 0)

        if ($this->plot_type == 'stackedbars') {
            $maxmaxy = $minminy = 0;
        } else {
            $minminy = $miny;
            $maxmaxy = $maxy;
        }

        // Process each row of data
        for ($i=0; $i < $this->num_data_rows; $i++) {
            $j = 1; // Skip over the data label for this row.

            switch ($this->data_type) {
            case 'text-data':           // Data is passed in as (title, y1, y2, y3, ...)
            case 'text-data-single':    // This one is for some pie charts
                // $numrecs = @ count($this->data[$i]);
                $maxy = (double)$this->data[$i][$j++];
                if ($this->plot_type == 'stackedbars') {
                    $miny = 0;
                } else {
                    $miny = $maxy;
                }
                for (; $j < $this->num_recs[$i]; $j++) {
                    $val = (double)$this->data[$i][$j];
                    if ($this->plot_type == 'stackedbars') {
                        $maxy += abs($val);      // only positive values for the moment
                    } else {
                        if ($val > $maxy) $maxy = $val;
                        if ($val < $miny) $miny = $val;
                    }
                }
                break;
            case 'data-data':           // Data is passed in as (title, x, y, y2, y3, ...)
                // X value:
                $val = (double)$this->data[$i][$j++];
                if ($val > $maxx) $maxx = $val;
                if ($val < $minx) $minx = $val;

                $miny = $maxy = (double)$this->data[$i][$j++];
                // $numrecs = @ count($this->data[$i]);
                for (; $j < $this->num_recs[$i]; $j++) {
                    $val = (double)$this->data[$i][$j];
                    if ($val > $maxy) $maxy = $val;
                    if ($val < $miny) $miny = $val;
                }
                break;
            case 'data-data-error':     // Data is passed in as (title, x, y, err+, err-, y2, err2+, err2-,...)
                // X value:
                $val = (double)$this->data[$i][$j++];
                if ($val > $maxx) $maxx = $val;
                if ($val < $minx) $minx = $val;

                $miny = $maxy = (double)$this->data[$i][$j];
                // $numrecs = @ count($this->data[$i]);
                for (; $j < $this->num_recs[$i];) {
                    // Y value:
                    $val = (double)$this->data[$i][$j++];
                    if ($val > $maxy) $maxy = $val;
                    if ($val < $miny) $miny = $val;
                    // Error +:
                    $val = (double)$this->data[$i][$j++];
                    if ($val > $maxe) $maxe = $val;
                    // Error -:
                    $val = (double)$this->data[$i][$j++];
                    if ($val > $mine) $mine = $val;
                }
                $maxy = $maxy + $maxe;
                $miny = $miny - $mine;      // assume error bars are always > 0
                break;
            default:
                return $this->PrintError("FindDataLimits(): Unknown data type '$this->data_type'.");
            }
            // Remember this row's min and max Y values:
            $this->data_miny[$i] = $miny;
            $this->data_maxy[$i] = $maxy;

            if ($miny < $minminy) $minminy = $miny;   // global min
            if ($maxy > $maxmaxy) $maxmaxy = $maxy;   // global max
        }

        $this->min_x = $minx;
        $this->max_x = $maxx;
        $this->min_y = $minminy;
        $this->max_y = $maxmaxy;

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'min_x' => $this->min_x, 'min_y' => $this->min_y,
                'max_x' => $this->max_x, 'max_y' => $this->max_y));
        }
        return TRUE;
    }

    /*!
     * Calculates image margins on the fly from title positions and sizes,
     * and tick labels positions and sizes.
     *
     * TODO: add x_tick_label_width and y_tick_label_height and use them to calculate
     *       max_x_labels and max_y_labels, to be used by drawing functions to avoid overlapping.
     *
     * A picture of the locations of elements and spacing can be found in the
     * PHPlot Reference Manual.
     *
     * Calculates the following (class variables unless noted):
     *
     * Plot area margins (see note below):
     *     y_top_margin
     *     y_bot_margin
     *     x_left_margin
     *     x_right_margin
     *
     * Title sizes (these are now local, not class variables, since they are not used elsewhere):
     *     title_height : Height of main title
     *     x_title_height : Height of X axis title, 0 if no X title
     *     y_title_width : Width of Y axis title, 0 if no Y title
     *
     * Tick/Data label offsets, relative to plot_area:
     *     x_label_top_offset, x_label_bot_offset, x_label_axis_offset
     *     y_label_left_offset, y_label_right_offset, y_label_axis_offset
     *
     * Title offsets, relative to plot area:
     *     x_title_top_offset, x_title_bot_offset
     *     y_title_left_offset, y_title_left_offset
     *
     *  Note: The margins are calculated, but not stored, if margins or plot area were
     *  set by the user with SetPlotAreaPixels or SetMarginsPixels (as indicated by
     *  the plot_margins_set flag). The margin calculation is mixed in with the offset
     *  variables, so it doesn't see worth the trouble to separate them.
     *
     * If the $maximize argument is true, we use the full image size, minus safe_margin
     * and main title, for the plot. This is for pie charts which have no axes or X/Y titles.
     */
    function CalcMargins($maximize)
    {
        // This is the line-to-line or line-to-text spacing:
        $gap = $this->safe_margin;

        // Minimum margin on each side. This reduces the chance that the
        // right-most tick label (for example) will run off the image edge
        // if there are no titles on that side.
        $min_margin = 3 * $gap;

        // Calculate the title sizes:
        list($unused, $title_height) = $this->SizeText($this->title_font, 0, $this->title_txt);
        list($unused, $x_title_height) = $this->SizeText($this->x_title_font, 0, $this->x_title_txt);
        list($y_title_width, $unused) = $this->SizeText($this->y_title_font, 90, $this->y_title_txt);

        // Special case for maximum area usage with no X/Y titles or labels, only main title:
        if ($maximize) {
            if (!$this->plot_margins_set) {
                $this->x_left_margin = $gap;
                $this->x_right_margin = $gap;
                $this->y_top_margin = $gap;
                $this->y_bot_margin = $gap;
                if ($title_height > 0)
                    $this->y_top_margin += $title_height + $gap;
            }
            return TRUE;
        }

        // Make local variables for these. (They get used a lot and I'm tired of this, this, this.)
        $x_tick_label_pos = $this->x_tick_label_pos;
        $x_data_label_pos = $this->x_data_label_pos;
        $x_tick_pos       = $this->x_tick_pos;
        $x_tick_len       = $this->x_tick_length;
        $y_tick_label_pos = $this->y_tick_label_pos;
        $y_tick_pos       = $this->y_tick_pos;
        $y_tick_len       = $this->y_tick_length;

        //////// Calculate maximum X/Y axis label height / width:

        // Calculate maximum height of X tick or data labels, depending on which one is on.
        // It is possible for both to be on, but this is only valid if all the data labels are empty.
        if ($x_data_label_pos == 'none') {
            $x_label_height = 0;
        } else {
            $x_label_height = $this->CalcMaxDataLabelSize();
        }
        if ($x_tick_label_pos != 'none' &&
            ($height = $this->CalcMaxTickLabelSize('x')) > $x_label_height) {
                $x_label_height = $height;
        }

        // If Y tick labels are on, calculate the width of the widest tick label:
        if ($y_tick_label_pos == 'none')
            $y_label_width = 0;
        else
            $y_label_width = $this->CalcMaxTickLabelSize('y');

        ///////// Calculate margins:

        // For X/Y tick and label position of 'xaxis' or 'yaxis', determine if the axis happens to be
        // on an edge of a plot. If it is, we need to account for the margins there.
        if ($this->x_axis_position <= $this->plot_min_y)
            $x_axis_pos = 'bottom';
        elseif ($this->x_axis_position >= $this->plot_max_y)
            $x_axis_pos = 'top';
        else
            $x_axis_pos = 'none';

        if ($this->y_axis_position <= $this->plot_min_x)
            $y_axis_pos = 'left';
        elseif ($this->y_axis_position >= $this->plot_max_x)
            $y_axis_pos = 'right';
        else
            $y_axis_pos = 'none';

        // y_top_margin: Main title, Upper X title, X ticks and tick labels, and X data labels:
        // y_bot_margin: Lower title, ticks and tick labels, and data labels:
        $top_margin = $gap;
        $bot_margin = $gap;
        $this->x_title_top_offset = $gap;
        $this->x_title_bot_offset = $gap;

        if ($title_height > 0)
            $top_margin += $title_height + $gap;

        if ($x_title_height > 0) {
            $pos = $this->x_title_pos;
            if ($pos == 'plotup' || $pos == 'both')
                $top_margin += $x_title_height + $gap;
            if ($pos == 'plotdown' || $pos == 'both')
                $bot_margin += $x_title_height + $gap;
        }

        if ($x_tick_label_pos == 'plotup' || $x_tick_label_pos == 'both'
           || $x_data_label_pos == 'plotup' || $x_data_label_pos == 'both'
           || ($x_tick_label_pos == 'xaxis' && $x_axis_pos == 'top')) {
            // X Tick or Data Labels above the plot:
            $top_margin += $x_label_height + $gap;
            $this->x_title_top_offset += $x_label_height + $gap;
        }
        if ($x_tick_label_pos == 'plotdown' || $x_tick_label_pos == 'both'
           || $x_data_label_pos == 'plotdown' || $x_data_label_pos == 'both'
           || ($x_tick_label_pos == 'xaxis' && $x_axis_pos == 'bottom')) {
            // X Tick or Data Labels below the plot:
            $bot_margin += $x_label_height + $gap;
            $this->x_title_bot_offset += $x_label_height + $gap;
        }
        if ($x_tick_pos == 'plotup' || $x_tick_pos == 'both'
           || ($x_tick_pos == 'xaxis' && $x_axis_pos == 'top')) {
            // X Ticks above the plot:
            $top_margin += $x_tick_len;
            $this->x_label_top_offset = $x_tick_len + $gap;
            $this->x_title_top_offset += $x_tick_len;
        } else {
            // No X Ticks above the plot:
            $this->x_label_top_offset = $gap;
        }
        if ($x_tick_pos == 'plotdown' || $x_tick_pos == 'both'
           || ($x_tick_pos == 'xaxis' && $x_axis_pos == 'bottom')) {
            // X Ticks below the plot:
            $bot_margin += $x_tick_len;
            $this->x_label_bot_offset = $x_tick_len + $gap;
            $this->x_title_bot_offset += $x_tick_len;
        } else {
            // No X Ticks below the plot:
            $this->x_label_bot_offset = $gap;
        }
        // Label offsets for on-axis ticks:
        if ($x_tick_pos == 'xaxis') {
            $this->x_label_axis_offset = $x_tick_len + $gap;
        } else {
            $this->x_label_axis_offset = $gap;
        }

        // x_left_margin: Left Y title, Y ticks and tick labels:
        // x_right_margin: Right Y title, Y ticks and tick labels:
        $left_margin = $gap;
        $right_margin = $gap;
        $this->y_title_left_offset = $gap;
        $this->y_title_right_offset = $gap;

        if ($y_title_width > 0) {
            $pos = $this->y_title_pos;
            if ($pos == 'plotleft' || $pos == 'both')
                $left_margin += $y_title_width + $gap;
            if ($pos == 'plotright' || $pos == 'both')
                $right_margin += $y_title_width + $gap;
        }

        if ($y_tick_label_pos == 'plotleft' || $y_tick_label_pos == 'both'
           || ($y_tick_label_pos == 'yaxis' && $y_axis_pos == 'left')) {
            // Y Tick Labels left of plot:
            $left_margin += $y_label_width + $gap;
            $this->y_title_left_offset += $y_label_width + $gap;
        }
        if ($y_tick_label_pos == 'plotright' || $y_tick_label_pos == 'both'
           || ($y_tick_label_pos == 'yaxis' && $y_axis_pos == 'right')) {
            // Y Tick Labels right of plot:
            $right_margin += $y_label_width + $gap;
            $this->y_title_right_offset += $y_label_width + $gap;
        }
        if ($y_tick_pos == 'plotleft' || $y_tick_pos == 'both'
           || ($y_tick_pos == 'yaxis' && $y_axis_pos == 'left')) {
            // Y Ticks left of plot:
            $left_margin += $y_tick_len;
            $this->y_label_left_offset = $y_tick_len + $gap;
            $this->y_title_left_offset += $y_tick_len;
        } else {
            // No Y Ticks left of plot:
            $this->y_label_left_offset = $gap;
        }
        if ($y_tick_pos == 'plotright' || $y_tick_pos == 'both'
           || ($y_tick_pos == 'yaxis' && $y_axis_pos == 'right')) {
            // Y Ticks right of plot:
            $right_margin += $y_tick_len;
            $this->y_label_right_offset = $y_tick_len + $gap;
            $this->y_title_right_offset += $y_tick_len;
        } else {
            // No Y Ticks right of plot:
            $this->y_label_right_offset = $gap;
        }

        // Label offsets for on-axis ticks:
        if ($x_tick_pos == 'yaxis') {
            $this->y_label_axis_offset = $y_tick_len + $gap;
        } else {
            $this->y_label_axis_offset = $gap;
        }

        // Apply the minimum margins and store in the object.
        // Do not set margins if they are user-defined (see note at top of function).
        if (!$this->plot_margins_set) {
            $this->y_top_margin = max($min_margin, $top_margin);
            $this->y_bot_margin = max($min_margin, $bot_margin);
            $this->x_left_margin = max($min_margin, $left_margin);
            $this->x_right_margin = max($min_margin, $right_margin);
        }
 
        if ($this->GetCallback('debug_scale')) {
            // (Too bad compact() doesn't work on class member variables...)
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'x_label_height' => $x_label_height,
                'y_label_width' => $y_label_width,
                'x_tick_len' => $x_tick_len,
                'y_tick_len' => $y_tick_len,
                'x_left_margin' => $this->x_left_margin,
                'x_right_margin' => $this->x_right_margin,
                'y_top_margin' => $this->y_top_margin,
                'y_bot_margin' => $this->y_bot_margin,
                'x_label_top_offset' => $this->x_label_top_offset,
                'x_label_bot_offset' => $this->x_label_bot_offset,
                'y_label_left_offset' => $this->y_label_left_offset,
                'y_label_right_offset' => $this->y_label_right_offset,
                'x_title_top_offset' => $this->x_title_top_offset,
                'x_title_bot_offset' => $this->x_title_bot_offset,
                'y_title_left_offset' => $this->y_title_left_offset,
                'y_title_right_offset' => $this->y_title_right_offset));
        }

        return TRUE;
    }

    /*
     * Calculate the plot area (device coordinates) from the margins.
     * (This used to be part of SetPlotAreaPixels.)
     * The margins might come from SetMarginsPixels, SetPlotAreaPixels,
     * or CalcMargins.
     */
    function CalcPlotAreaPixels()
    {
        $this->plot_area = array($this->x_left_margin, $this->y_top_margin,
                                 $this->image_width - $this->x_right_margin,
                                 $this->image_height - $this->y_bot_margin);
        $this->plot_area_width = $this->plot_area[2] - $this->plot_area[0];
        $this->plot_area_height = $this->plot_area[3] - $this->plot_area[1];

        $this->DoCallback('debug_scale', __FUNCTION__, $this->plot_area);
        return TRUE;
    }


    /*!
     * Set the margins in pixels (left, right, top, bottom)
     * This determines the plot area, equivalent to SetPlotAreaPixels().
     * Deferred calculations now occur in CalcPlotAreaPixels().
     */
    function SetMarginsPixels($which_lm, $which_rm, $which_tm, $which_bm)
    {
        $this->x_left_margin = $which_lm;
        $this->x_right_margin = $which_rm;
        $this->y_top_margin = $which_tm;
        $this->y_bot_margin = $which_bm;

        $this->plot_margins_set = TRUE;

        return TRUE;
    }

    /*!
     * Sets the limits for the plot area.
     * This stores the margins, not the area. That may seem odd, but
     * the idea is to make SetPlotAreaPixels and SetMarginsPixels two
     * ways to accomplish the same thing, and the deferred calculations
     * in CalcMargins and CalcPlotAreaPixels don't need to know which
     * was used.
     *   (x1, y1) - Upper left corner of the plot area
     *   (x2, y2) - Lower right corner of the plot area
     */
    function SetPlotAreaPixels($x1, $y1, $x2, $y2)
    {
        $this->x_left_margin = $x1;
        $this->x_right_margin = $this->image_width - $x2;
        $this->y_top_margin = $y1;
        $this->y_bot_margin = $this->image_height - $y2;

        $this->plot_margins_set = TRUE;

        return TRUE;
    }

    /*
     * Calculate the World Coordinate limits of the plot area.
     * This goes with SetPlotAreaWorld, but the calculations are
     * deferred until the graph is being drawn.
     * Uses: plot_min_x, plot_max_x, plot_min_y, plot_max_y
     * which can be user-supplied or NULL to auto-calculate.
     * Pre-requisites: FindDataLimits()
     */
    function CalcPlotAreaWorld()
    {
        if (isset($this->plot_min_x) && $this->plot_min_x !== '')
            $xmin = $this->plot_min_x;
        elseif ($this->data_type == 'text-data')  // Valid for data without X values only.
            $xmin = 0;
        else
            $xmin = $this->min_x;

        if (isset($this->plot_max_x) && $this->plot_max_x !== '')
            $xmax = $this->plot_max_x;
        elseif ($this->data_type == 'text-data')  // Valid for data without X values only.
            $xmax = $this->max_x + 1;
        else
            $xmax = $this->max_x;

        // Leave room above and below the highest and lowest data points.

        if (!isset($this->plot_min_y) || $this->plot_min_y === '')
            $ymin = floor($this->min_y - abs($this->min_y) * 0.1);
        else
            $ymin = $this->plot_min_y;

        if (!isset($this->plot_max_y) || $this->plot_max_y === '')
            $ymax = ceil($this->max_y + abs($this->max_y) * 0.1);
        else
            $ymax = $this->plot_max_y;

        // Error checking

        if ($ymin == $ymax)     // Minimum height
            $ymax += 1;

        if ($this->yscale_type == 'log') {
            if ($ymin <= 0) {
                $ymin = 1;
            }
            if ($ymax <= 0) {
                // Note: Error messages reference the user function, not this function.
                return $this->PrintError('SetPlotAreaWorld(): Log plots need data greater than 0');
            }
        }

        if ($ymax <= $ymin) {
            return $this->PrintError('SetPlotAreaWorld(): Error in data - max not greater than min');
        }

        $this->plot_min_x = $xmin;
        $this->plot_max_x = $xmax;
        $this->plot_min_y = $ymin;
        $this->plot_max_y = $ymax;
        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'plot_min_x' => $this->plot_min_x, 'plot_min_y' => $this->plot_min_y,
                'plot_max_x' => $this->plot_max_x, 'plot_max_y' => $this->plot_max_y));
        }

        return TRUE;
    }

    /*!
     * Stores the desired World Coordinate range of the plot.
     * The user calls this to force one or more of the range limits to
     * specific values. Anything not set will be calculated in CalcPlotAreaWorld().
     */
    function SetPlotAreaWorld($xmin=NULL, $ymin=NULL, $xmax=NULL, $ymax=NULL)
    {
        $this->plot_min_x = $xmin;
        $this->plot_max_x = $xmax;
        $this->plot_min_y = $ymin;
        $this->plot_max_y = $ymax;
        return TRUE;
    } //function SetPlotAreaWorld


    /*!
     * For bar plots, which have equally spaced x variables.
     */
    function CalcBarWidths()
    {
        // group_width is the width of a group, including padding
        $group_width = $this->plot_area_width / $this->num_data_rows;

        // Actual number of bar spaces in the group. This includes the drawn bars, and
        // 'bar_extra_space'-worth of extra bars.
        // Note that 'records_per_group' includes the label, so subtract one to get
        // the number of points in the group. 'stackedbars' have 1 bar space per group.
        if ($this->plot_type == 'stackedbars') {
          $num_spots = 1 + $this->bar_extra_space;
        } else {
          $num_spots = $this->records_per_group - 1 + $this->bar_extra_space;
        }

        // record_bar_width is the width of each bar's allocated area.
        // If bar_width_adjust=1 this is the width of the bar, otherwise
        // the bar is centered inside record_bar_width.
        // The equation is:
        //   group_frac_width * group_width = record_bar_width * num_spots
        $this->record_bar_width = $this->group_frac_width * $group_width / $num_spots;

        // Note that the extra space due to group_frac_width and bar_extra_space will be
        // evenly divided on each side of the group: the drawn bars are centered in the group.

        // Within each bar's allocated space, if bar_width_adjust=1 the bar fills the 
        // space, otherwise it is centered.
        // This is the actual drawn bar width:
        $this->actual_bar_width = $this->record_bar_width * $this->bar_width_adjust;
        // This is the gap on each side of the bar (0 if bar_width_adjust=1):
        $this->bar_adjust_gap = ($this->record_bar_width - $this->actual_bar_width) / 2;

        return TRUE;
    }

    /*
     * Calculate X and Y Axis Positions, world coordinates.
     * This needs the min/max x/y range set by CalcPlotAreaWorld.
     * It adjusts or sets x_axis_position and y_axis_position per the data.
     * Empty string means the values need to be calculated; otherwise they
     * are supplied but need to be validated against the World area.
     *
     * Note: This used to be in CalcTranslation, but CalcMargins needs it too.
     * This does not calculate the pixel values of the axes. That happens in
     * CalcTranslation, after scaling is set up (which has to happen after
     * margins are set up).
     */
    function CalcAxisPositions()
    {
        // If no user-provided Y axis position, default to axis on left side.
        // Otherwise, make sure user-provided position is inside the plot area.
        if ($this->y_axis_position === '')
            $this->y_axis_position = $this->plot_min_x;
        else
            $this->y_axis_position = min(max($this->plot_min_x, $this->y_axis_position), $this->plot_max_x);

        // If no user-provided X axis position, default to axis at Y=0 (if in range), or min_y
        //   if the range does not include 0, or 1 for log plots.
        // Otherwise, make sure user-provided position is inside the plot area.
        if ($this->x_axis_position === '') {
            if ($this->yscale_type == 'log')
                $this->x_axis_position = 1;
            elseif ($this->plot_min_y <= 0 && 0 <= $this->plot_max_y)
                $this->x_axis_position = 0;
             else 
                $this->x_axis_position = $this->plot_min_y;
        } else
            $this->x_axis_position = min(max($this->plot_min_y, $this->x_axis_position), $this->plot_max_y);

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'x_axis_position' => $this->x_axis_position,
                'y_axis_position' => $this->y_axis_position));
        }

        return TRUE;
    }

    /*!
     * Calculates scaling stuff...
     */
    function CalcTranslation()
    {
        if ($this->plot_max_x - $this->plot_min_x == 0) { // Check for div by 0
            $this->xscale = 0;
        } else {
            if ($this->xscale_type == 'log') {
                $this->xscale = ($this->plot_area_width)/(log10($this->plot_max_x) - log10($this->plot_min_x));
            } else {
                $this->xscale = ($this->plot_area_width)/($this->plot_max_x - $this->plot_min_x);
            }
        }

        if ($this->plot_max_y - $this->plot_min_y == 0) { // Check for div by 0
            $this->yscale = 0;
        } else {
            if ($this->yscale_type == 'log') {
                $this->yscale = ($this->plot_area_height)/(log10($this->plot_max_y) - log10($this->plot_min_y));
            } else {
                $this->yscale = ($this->plot_area_height)/($this->plot_max_y - $this->plot_min_y);
            }
        }
        // GD defines x = 0 at left and y = 0 at TOP so -/+ respectively
        if ($this->xscale_type == 'log') {
            $this->plot_origin_x = $this->plot_area[0] - ($this->xscale * log10($this->plot_min_x) );
        } else {
            $this->plot_origin_x = $this->plot_area[0] - ($this->xscale * $this->plot_min_x);
        }
        if ($this->yscale_type == 'log') {
            $this->plot_origin_y = $this->plot_area[3] + ($this->yscale * log10($this->plot_min_y));
        } else {
            $this->plot_origin_y = $this->plot_area[3] + ($this->yscale * $this->plot_min_y);
        }

        // Convert axis positions to device coordinates:
        $this->y_axis_x_pixels = $this->xtr($this->y_axis_position);
        $this->x_axis_y_pixels = $this->ytr($this->x_axis_position);

        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'xscale' => $this->xscale, 'yscale' => $this->yscale,
                'plot_origin_x' => $this->plot_origin_x, 'plot_origin_y' => $this->plot_origin_y,
                'y_axis_x_pixels' => $this->y_axis_x_pixels,
                'x_axis_y_pixels' => $this->x_axis_y_pixels));
        }

        return TRUE;
    } // function CalcTranslation()


    /*!
     * Translate X world coordinate into pixel coordinate
     * See CalcTranslation() for calculation of xscale.
     */
    function xtr($x_world)
    {
        if ($this->xscale_type == 'log') {
            $x_pixels = $this->plot_origin_x + log10($x_world) * $this->xscale ;
        } else {
            $x_pixels = $this->plot_origin_x + $x_world * $this->xscale ;
        }
        return round($x_pixels);
    }


    /*!
     * Translate Y world coordinate into pixel coordinate.
     * See CalcTranslation() for calculation of yscale.
     */
    function ytr($y_world)
    {
        if ($this->yscale_type == 'log') {
            //minus because GD defines y = 0 at top. doh!
            $y_pixels =  $this->plot_origin_y - log10($y_world) * $this->yscale ;
        } else {
            $y_pixels =  $this->plot_origin_y - $y_world * $this->yscale ;
        }
        return round($y_pixels);
    }

    /*
     * Calculate tick parameters: Start, end, and delta values. This is used
     * by both DrawXTicks() and DrawYTicks().
     * This currently uses the same simplistic method previously used by
     * PHPlot (basically just range/10), but splitting this out into its
     * own function is the first step in replacing the method.
     * This is also used by CalcMaxTickSize() for CalcMargins().
     *
     *   $which : 'x' or 'y' : Which tick parameters to calculate
     *
     * Returns an array of 3 elements: tick_start, tick_end, tick_step
     */
    function CalcTicks($which)
    {
        if ($which == 'x') {
            $num_ticks = $this->num_x_ticks;
            $tick_inc = $this->x_tick_inc;
            $data_max = $this->plot_max_x;
            $data_min = $this->plot_min_x;
            $skip_lo = $this->skip_left_tick;
            $skip_hi = $this->skip_right_tick;
        } elseif ($which == 'y') {
            $num_ticks = $this->num_y_ticks;
            $tick_inc = $this->y_tick_inc;
            $data_max = $this->plot_max_y;
            $data_min = $this->plot_min_y;
            $skip_lo = $this->skip_bottom_tick;
            $skip_hi = $this->skip_top_tick;
        } else {
          return $this->PrintError("CalcTicks: Invalid usage ($which)");
        }

        if (!empty($tick_inc)) {
            $tick_step = $tick_inc;
        } elseif (!empty($num_ticks)) {
            $tick_step = ($data_max - $data_min) / $num_ticks;
        } else {
            $tick_step = ($data_max - $data_min) / 10;
        }

        // NOTE: When working with floats, because of approximations when adding $tick_step,
        // the value may not quite reach the end, or may exceed it very slightly.
        // So apply a "fudge" factor.
        $tick_start = (double)$data_min;
        $tick_end = (double)$data_max + ($data_max - $data_min) / 10000.0;

        if ($skip_lo)
            $tick_start += $tick_step;

        if ($skip_hi)
            $tick_end -= $tick_step;

        return array($tick_start, $tick_end, $tick_step);
    }

    /*
     * Calculate the size of the biggest tick label. This is used by CalcMargins().
     * For 'x' ticks, it returns the height . For 'y' ticks, it returns the width.
     * This means height along Y, or width along X - not relative to the text angle.
     * That is what we need to calculate the needed margin space.
     * (Previous versions of PHPlot estimated this, using the maximum X or Y value,
     * or maybe the longest string. That doesn't work. -10 is longer than 9, etc.
     * So this gets the actual size of each label, slow as that may be.
     */
    function CalcMaxTickLabelSize($which)
    {
        list($tick_val, $tick_end, $tick_step) = $this->CalcTicks($which);

        if ($which == 'x') {
          $font = $this->x_label_font;
          $angle = $this->x_label_angle;
        } elseif ($which == 'y') {
          $font = $this->y_label_font;
          $angle = $this->y_label_angle;
        } else {
          return $this->PrintError("CalcMaxTickLabelSize: Invalid usage ($which)");
        }

        $max_width = 0;
        $max_height = 0;

        // Loop over ticks, same as DrawXTicks and DrawYTicks:
        while ($tick_val <= $tick_end) {
            $tick_label = $this->FormatLabel($which, $tick_val);
            list($width, $height) = $this->SizeText($font, $angle, $tick_label);
            if ($width > $max_width) $max_width = $width;
            if ($height > $max_height) $max_height = $height;
            $tick_val += $tick_step;
        }
        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'which' => $which, 'height' => $max_height, 'width' => $max_width));
        }

        if ($which == 'x')
            return $max_height;
        return $max_width;
    }

    /*
     * Calculate the size of the biggest X data label. This is used by CalcMargins().
     * Returns the height along Y axis of the biggest X data label.
     * (This calculates width and height, but only height is used at present.)
     */
    function CalcMaxDataLabelSize()
    {
        $font = $this->x_label_font;
        $angle = $this->x_label_angle;
        $max_width = 0;
        $max_height = 0;

        // Loop over all data labels and find the biggest:
        for ($i = 0; $i < $this->num_data_rows; $i++) {
            $label = $this->FormatLabel('x', $this->data[$i][0]);
            list($width, $height) = $this->SizeText($font, $angle, $label);
            if ($width > $max_width) $max_width = $width;
            if ($height > $max_height) $max_height = $height;
        }
        if ($this->GetCallback('debug_scale')) {
            $this->DoCallback('debug_scale', __FUNCTION__, array(
                'height' => $max_height, 'width' => $max_width));
        }

        return $max_height;
    }

    /*!
     * Formats a tick or data label.
     *    which_pos - 'x' or 'y', selects formatting controls.
     *    which_lab - String to format as a label.
     * \note Time formatting suggested by Marlin Viss
     */
    function FormatLabel($which_pos, $which_lab)
    {
        $lab = $which_lab;   // Default to no formatting.
        if ($lab !== '') {   // Don't format empty strings (especially as time or numbers)
            if ($which_pos == 'x') {
                switch ($this->x_label_type) {
                case 'title':  // Note: This is obsolete
                    $lab = @ $this->data[$which_lab][0];
                    break;
                case 'data':
                    $lab = $this->number_format($which_lab, $this->x_precision).$this->data_units_text;
                    break;
                case 'time':
                    $lab = strftime($this->x_time_format, $which_lab);
                    break;
                }
            } elseif ($which_pos == 'y') {
                switch ($this->y_label_type) {
                case 'data':
                    $lab = $this->number_format($which_lab, $this->y_precision).$this->data_units_text;
                    break;
                case 'time':
                    $lab = strftime($this->y_time_format, $which_lab);
                    break;
                }
            }
        }
        return $lab;
    } //function FormatLabel



/////////////////////////////////////////////
///////////////                         TICKS
/////////////////////////////////////////////

    /*!
     * Use either this or SetNumXTicks() to set where to place x tick marks
     */
    function SetXTickIncrement($which_ti='')
    {
        $this->x_tick_inc = $which_ti;
        if (!empty($which_ti)) {
            $this->num_x_ticks = ''; //either use num_x_ticks or x_tick_inc, not both
        }
        return TRUE;
    }

    /*!
     * Use either this or SetNumYTicks() to set where to place y tick marks
     */
    function SetYTickIncrement($which_ti='')
    {
        $this->y_tick_inc = $which_ti;
        if (!empty($which_ti)) {
            $this->num_y_ticks = ''; //either use num_y_ticks or y_tick_inc, not both
        }
        return TRUE;
    }


    function SetNumXTicks($which_nt)
    {
        $this->num_x_ticks = $which_nt;
        if (!empty($which_nt)) {
            $this->x_tick_inc = '';  //either use num_x_ticks or x_tick_inc, not both
        }
        return TRUE;
    }

    function SetNumYTicks($which_nt)
    {
        $this->num_y_ticks = $which_nt;
        if (!empty($which_nt)) {
            $this->y_tick_inc = '';  //either use num_y_ticks or y_tick_inc, not both
        }
        return TRUE;
    }

    /*!
     *
     */
    function SetYTickPos($which_tp)
    {
        $this->y_tick_pos = $this->CheckOption($which_tp, 'plotleft, plotright, both, yaxis, none', __FUNCTION__);
        return (boolean)$this->y_tick_pos;
    }
    /*!
     *
     */
    function SetXTickPos($which_tp)
    {
        $this->x_tick_pos = $this->CheckOption($which_tp, 'plotdown, plotup, both, xaxis, none', __FUNCTION__);
        return (boolean)$this->x_tick_pos;
    }

    /*!
     * \param skip bool
     */
    function SetSkipTopTick($skip)
    {
        $this->skip_top_tick = (bool)$skip;
        return TRUE;
    }

    /*!
     * \param skip bool
     */
    function SetSkipBottomTick($skip)
    {
        $this->skip_bottom_tick = (bool)$skip;
        return TRUE;
    }

    /*!
     * \param skip bool
     */
    function SetSkipLeftTick($skip)
    {
        $this->skip_left_tick = (bool)$skip;
        return TRUE;
    }

    /*!
     * \param skip bool
     */
    function SetSkipRightTick($skip)
    {
        $this->skip_right_tick = (bool)$skip;
        return TRUE;
    }

    function SetXTickLength($which_xln)
    {
        $this->x_tick_length = $which_xln;
        return TRUE;
    }

    function SetYTickLength($which_yln)
    {
        $this->y_tick_length = $which_yln;
        return TRUE;
    }

    function SetXTickCrossing($which_xc)
    {
        $this->x_tick_cross = $which_xc;
        return TRUE;
    }

    function SetYTickCrossing($which_yc)
    {
        $this->y_tick_cross = $which_yc;
        return TRUE;
    }


/////////////////////////////////////////////
////////////////////          GENERIC DRAWING
/////////////////////////////////////////////

    /*!
     * Fills the background.
     */
    function DrawBackground()
    {
        // Don't draw this twice if drawing two plots on one image
        if (! $this->background_done) {
            if (isset($this->bgimg)) {    // If bgimg is defined, use it
                $this->tile_img($this->bgimg, 0, 0, $this->image_width, $this->image_height, $this->bgmode);
            } else {                        // Else use solid color
                ImageFilledRectangle($this->img, 0, 0, $this->image_width, $this->image_height,
                                     $this->ndx_bg_color);
            }
            $this->background_done = TRUE;
        }
        return TRUE;
    }


    /*!
     * Fills the plot area background.
     */
    function DrawPlotAreaBackground()
    {
        if (isset($this->plotbgimg)) {
            $this->tile_img($this->plotbgimg, $this->plot_area[0], $this->plot_area[1],
                            $this->plot_area_width, $this->plot_area_height, $this->plotbgmode);
        }
        else {
            if ($this->draw_plot_area_background) {
                ImageFilledRectangle($this->img, $this->plot_area[0], $this->plot_area[1],
                                     $this->plot_area[2], $this->plot_area[3], $this->ndx_plot_bg_color);
            }
        }

        return TRUE;
    }


    /*!
     * Tiles an image at some given coordinates.
     *
     * \param $file   string Filename of the picture to be used as tile.
     * \param $xorig  int    X coordinate of the plot where the tile is to begin.
     * \param $yorig  int    Y coordinate of the plot where the tile is to begin.
     * \param $width  int    Width of the area to be tiled.
     * \param $height int    Height of the area to be tiled.
     * \param $mode   string One of 'centeredtile', 'tile', 'scale'.
     */
    function tile_img($file, $xorig, $yorig, $width, $height, $mode)
    {
        $im = $this->GetImage($file, $tile_width, $tile_height);
        if (!$im)
            return FALSE;  // GetImage already produced an error message.

        if ($mode == 'scale') {
            imagecopyresized($this->img, $im, $xorig, $yorig, 0, 0, $width, $height, $tile_width, $tile_height);
            return TRUE;
        } else if ($mode == 'centeredtile') {
            $x0 = - floor($tile_width/2);   // Make the tile look better
            $y0 = - floor($tile_height/2);
        } else if ($mode = 'tile') {
            $x0 = 0;
            $y0 = 0;
        }

        // Actually draw the tile

        // But first on a temporal image.
        $tmp = ImageCreate($width, $height);
        if (! $tmp)
            return $this->PrintError('tile_img(): Could not create image resource.');

        for ($x = $x0; $x < $width; $x += $tile_width)
            for ($y = $y0; $y < $height; $y += $tile_height)
                imagecopy($tmp, $im, $x, $y, 0, 0, $tile_width, $tile_height);

        // Copy the temporal image onto the final one.
        imagecopy($this->img, $tmp, $xorig, $yorig, 0,0, $width, $height);

        // Free resources
        imagedestroy($tmp);
        imagedestroy($im);

        return TRUE;
    }  // function tile_img


    /*!
     * Draws a border around the final image.
     */
    function DrawImageBorder()
    {
        switch ($this->image_border_type) {
        case 'raised':
            ImageLine($this->img, 0, 0, $this->image_width-1, 0, $this->ndx_i_border);
            ImageLine($this->img, 1, 1, $this->image_width-2, 1, $this->ndx_i_border);
            ImageLine($this->img, 0, 0, 0, $this->image_height-1, $this->ndx_i_border);
            ImageLine($this->img, 1, 1, 1, $this->image_height-2, $this->ndx_i_border);
            ImageLine($this->img, $this->image_width-1, 0, $this->image_width-1,
                      $this->image_height-1, $this->ndx_i_border_dark);
            ImageLine($this->img, 0, $this->image_height-1, $this->image_width-1,
                      $this->image_height-1, $this->ndx_i_border_dark);
            ImageLine($this->img, $this->image_width-2, 1, $this->image_width-2,
                      $this->image_height-2, $this->ndx_i_border_dark);
            ImageLine($this->img, 1, $this->image_height-2, $this->image_width-2,
                      $this->image_height-2, $this->ndx_i_border_dark);
            break;
        case 'plain':
            ImageLine($this->img, 0, 0, $this->image_width-1, 0, $this->ndx_i_border_dark);
            ImageLine($this->img, $this->image_width-1, 0, $this->image_width-1,
                      $this->image_height-1, $this->ndx_i_border_dark);
            ImageLine($this->img, $this->image_width-1, $this->image_height-1, 0, $this->image_height-1,
                      $this->ndx_i_border_dark);
            ImageLine($this->img, 0, 0, 0, $this->image_height-1, $this->ndx_i_border_dark);
            break;
        case 'none':
            break;
        default:
            return $this->PrintError("DrawImageBorder(): unknown image_border_type: '$this->image_border_type'");
        }
        return TRUE;
    }


    /*!
     * Adds the title to the graph.
     */
    function DrawTitle()
    {
        // Center of the plot area
        //$xpos = ($this->plot_area[0] + $this->plot_area_width )/ 2;

        // Center of the image:
        $xpos = $this->image_width / 2;

        // Place it at almost at the top
        $ypos = $this->safe_margin;

        $this->DrawText($this->title_font, 0, $xpos, $ypos,
                        $this->ndx_title_color, $this->title_txt, 'center', 'top');

        return TRUE;

    }


    /*!
     * Draws the X-Axis Title
     */
    function DrawXTitle()
    {
        if ($this->x_title_pos == 'none')
            return TRUE;

        // Center of the plot
        $xpos = ($this->plot_area[2] + $this->plot_area[0]) / 2;

        // Upper title
        if ($this->x_title_pos == 'plotup' || $this->x_title_pos == 'both') {
            $ypos = $this->plot_area[1] - $this->x_title_top_offset;
            $this->DrawText($this->x_title_font, 0, $xpos, $ypos, $this->ndx_title_color,
                            $this->x_title_txt, 'center', 'bottom');
        }
        // Lower title
        if ($this->x_title_pos == 'plotdown' || $this->x_title_pos == 'both') {
            $ypos = $this->plot_area[3] + $this->x_title_bot_offset;
            $this->DrawText($this->x_title_font, 0, $xpos, $ypos, $this->ndx_title_color,
                            $this->x_title_txt, 'center', 'top');
        }
        return TRUE;
    }

    /*!
     * Draws the Y-Axis Title
     */
    function DrawYTitle()
    {
        if ($this->y_title_pos == 'none')
            return TRUE;

        // Center the title vertically to the plot area
        $ypos = ($this->plot_area[3] + $this->plot_area[1]) / 2;

        if ($this->y_title_pos == 'plotleft' || $this->y_title_pos == 'both') {
            $xpos = $this->plot_area[0] - $this->y_title_left_offset;
            $this->DrawText($this->y_title_font, 90, $xpos, $ypos, $this->ndx_title_color,
                            $this->y_title_txt, 'right', 'center');
        }
        if ($this->y_title_pos == 'plotright' || $this->y_title_pos == 'both') {
            $xpos = $this->plot_area[2] + $this->y_title_right_offset;
            $this->DrawText($this->y_title_font, 90, $xpos, $ypos, $this->ndx_title_color,
                            $this->y_title_txt, 'left', 'center');
        }

        return TRUE;
    }


    /*
     * \note Horizontal grid lines overwrite horizontal axis with y=0, so call this first, then DrawXAxis()
     */
    function DrawYAxis()
    {
        // Draw ticks, labels and grid, if any
        $this->DrawYTicks();

        // Draw Y axis at X = y_axis_x_pixels
        ImageLine($this->img, $this->y_axis_x_pixels, $this->plot_area[1],
                  $this->y_axis_x_pixels, $this->plot_area[3], $this->ndx_grid_color);

        return TRUE;
    }

    /*
     *
     */
    function DrawXAxis()
    {
        // Draw ticks, labels and grid
        $this->DrawXTicks();

        /* This tick and label tend to overlap with regular Y Axis labels,
         * as Mike Pullen pointed out.
         *
        //Draw Tick and Label for X axis
        if (! $this->skip_bottom_tick) {
            $ylab =$this->FormatLabel('y', $this->x_axis_position);
            $this->DrawYTick($ylab, $this->x_axis_y_pixels);
        }
        */
        //Draw X Axis at Y = x_axis_y_pixels
        ImageLine($this->img, $this->plot_area[0]+1, $this->x_axis_y_pixels,
                  $this->plot_area[2]-1, $this->x_axis_y_pixels, $this->ndx_grid_color);

        return TRUE;
    }

    /*!
     * Draw one Y tick mark and its tick label. Called from DrawYTicks() and DrawXAxis()
     */
    function DrawYTick($which_ylab, $which_ypix)
    {
        // Ticks on Y axis
        if ($this->y_tick_pos == 'yaxis') {
            ImageLine($this->img, $this->y_axis_x_pixels - $this->y_tick_length, $which_ypix,
                      $this->y_axis_x_pixels + $this->y_tick_cross, $which_ypix, $this->ndx_tick_color);
        }

        // Ticks to the left of the Plot Area
        if (($this->y_tick_pos == 'plotleft') || ($this->y_tick_pos == 'both') ) {
            ImageLine($this->img, $this->plot_area[0] - $this->y_tick_length, $which_ypix,
                      $this->plot_area[0] + $this->y_tick_cross, $which_ypix, $this->ndx_tick_color);
        }

        // Ticks to the right of the Plot Area
        if (($this->y_tick_pos == 'plotright') || ($this->y_tick_pos == 'both') ) {
            ImageLine($this->img, $this->plot_area[2] + $this->y_tick_length, $which_ypix,
                      $this->plot_area[2] - $this->y_tick_cross, $which_ypix, $this->ndx_tick_color);
        }

        // Labels on Y axis
        if ($this->y_tick_label_pos == 'yaxis') {
            $this->DrawText($this->y_label_font, $this->y_label_angle,
                            $this->y_axis_x_pixels - $this->y_label_axis_offset, $which_ypix,
                            $this->ndx_text_color, $which_ylab, 'right', 'center');
        }

        // Labels to the left of the plot area
        if ($this->y_tick_label_pos == 'plotleft' || $this->y_tick_label_pos == 'both') {
            $this->DrawText($this->y_label_font, $this->y_label_angle,
                            $this->plot_area[0] - $this->y_label_left_offset, $which_ypix,
                            $this->ndx_text_color, $which_ylab, 'right', 'center');
        }
        // Labels to the right of the plot area
        if ($this->y_tick_label_pos == 'plotright' || $this->y_tick_label_pos == 'both') {
            $this->DrawText($this->y_label_font, $this->y_label_angle,
                            $this->plot_area[2] + $this->y_label_right_offset, $which_ypix,
                            $this->ndx_text_color, $which_ylab, 'left', 'center');
        }
        return TRUE;
    } // Function DrawYTick()


    /*!
     * Draws Grid, Ticks and Tick Labels along Y-Axis
     * Ticks and ticklabels can be left of plot only, right of plot only,
     * both on the left and right of plot, or crossing a user defined Y-axis
     * TODO: marks at whole numbers (-10, 10, 20, 30 ...) no matter where the plot begins (-3, 4.7, etc.)
     */
    function DrawYTicks()
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Calculate the tick start, end, and step:
        list($y_tmp, $y_end, $delta_y) = $this->CalcTicks('y');

        for (;$y_tmp <= $y_end; $y_tmp += $delta_y) {
            $ylab = $this->FormatLabel('y', $y_tmp);
            $y_pixels = $this->ytr($y_tmp);

            // Horizontal grid line
            if ($this->draw_y_grid) {
                ImageLine($this->img, $this->plot_area[0]+1, $y_pixels, $this->plot_area[2]-1, $y_pixels, $style);
            }

            // Draw tick mark(s)
            $this->DrawYTick($ylab, $y_pixels);
        }
        return TRUE;
    } // function DrawYTicks

    /*!
     * Draw one X tick mark and its tick label.
     */
    function DrawXTick($which_xlab, $which_xpix)
    {
        // Ticks on X axis
        if ($this->x_tick_pos == 'xaxis') {
            ImageLine($this->img, $which_xpix, $this->x_axis_y_pixels - $this->x_tick_cross,
                      $which_xpix, $this->x_axis_y_pixels + $this->x_tick_length, $this->ndx_tick_color);
        }

        // Ticks on top of the Plot Area
        if ($this->x_tick_pos == 'plotup' || $this->x_tick_pos == 'both') {
            ImageLine($this->img, $which_xpix, $this->plot_area[1] - $this->x_tick_length,
                      $which_xpix, $this->plot_area[1] + $this->x_tick_cross, $this->ndx_tick_color);
        }

        // Ticks on bottom of Plot Area
        if ($this->x_tick_pos == 'plotdown' || $this->x_tick_pos == 'both') {
            ImageLine($this->img, $which_xpix, $this->plot_area[3] + $this->x_tick_length,
                      $which_xpix, $this->plot_area[3] - $this->x_tick_cross, $this->ndx_tick_color);
        }

        // Label on X axis
        if ($this->x_tick_label_pos == 'xaxis') {
            $this->DrawText($this->x_label_font, $this->x_label_angle,
                            $which_xpix, $this->x_axis_y_pixels + $this->x_label_axis_offset,
                            $this->ndx_text_color, $which_xlab, 'center', 'top');
        }

        // Label on top of the Plot Area
        if ($this->x_tick_label_pos == 'plotup' || $this->x_tick_label_pos == 'both') {
            $this->DrawText($this->x_label_font, $this->x_label_angle,
                            $which_xpix, $this->plot_area[1] - $this->x_label_top_offset,
                            $this->ndx_text_color, $which_xlab, 'center', 'bottom');
        }

        // Label on bottom of the Plot Area
        if ($this->x_tick_label_pos == 'plotdown' || $this->x_tick_label_pos == 'both') {
            $this->DrawText($this->x_label_font, $this->x_label_angle,
                            $which_xpix, $this->plot_area[3] + $this->x_label_bot_offset,
                            $this->ndx_text_color, $which_xlab, 'center', 'top');
        }
        return TRUE;
    }

    /*!
     * Draws Grid, Ticks and Tick Labels along X-Axis
     * Ticks and tick labels can be down of plot only, up of plot only,
     * both on up and down of plot, or crossing a user defined X-axis
     *
     * \note Original vertical code submitted by Marlin Viss
     */
    function DrawXTicks()
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if ($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Calculate the tick start, end, and step:
        list($x_tmp, $x_end, $delta_x) = $this->CalcTicks('x');

        for (;$x_tmp <= $x_end; $x_tmp += $delta_x) {
            $xlab = $this->FormatLabel('x', $x_tmp);
            $x_pixels = $this->xtr($x_tmp);

            // Vertical grid lines
            if ($this->draw_x_grid) {
                ImageLine($this->img, $x_pixels, $this->plot_area[1], $x_pixels, $this->plot_area[3], $style);
            }

            // Draw tick mark(s)
            $this->DrawXTick($xlab, $x_pixels);
        }
        return TRUE;
    } // function DrawXTicks


    /*!
     *
     */
    function DrawPlotBorder()
    {
        switch ($this->plot_border_type) {
        case 'left':    // for past compatibility
        case 'plotleft':
            ImageLine($this->img, $this->plot_area[0], $this->ytr($this->plot_min_y),
                      $this->plot_area[0], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        case 'right':
        case 'plotright':
            ImageLine($this->img, $this->plot_area[2], $this->ytr($this->plot_min_y),
                      $this->plot_area[2], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        case 'both':
        case 'sides':
             ImageLine($this->img, $this->plot_area[0], $this->ytr($this->plot_min_y),
                      $this->plot_area[0], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            ImageLine($this->img, $this->plot_area[2], $this->ytr($this->plot_min_y),
                      $this->plot_area[2], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        case 'none':
            //Draw No Border
            break;
        case 'full':
        default:
            ImageRectangle($this->img, $this->plot_area[0], $this->ytr($this->plot_min_y),
                           $this->plot_area[2], $this->ytr($this->plot_max_y), $this->ndx_grid_color);
            break;
        }
        return TRUE;
    }


    /*!
     * Draws the data label associated with a point in the plot at specified x/y world position.
     * This is currently only used for Y data labels for bar charts.
     */
    function DrawDataLabel($which_font, $which_angle, $x_world, $y_world, $which_color, $which_text,
                      $which_halign = 'center', $which_valign = 'bottom', $x_adjustment=0, $y_adjustment=0) 
    {
        $data_label = $this->FormatLabel('y', $which_text);
        //since DrawDataLabel is going to be called alot - perhaps for speed it is better to 
        //not use this if statement and just always assume which_font is x_label_font (ditto for color).
        if ( empty($which_font) ) 
            $which_font = $this->x_label_font;

        $which_angle = empty($which_angle)?'0':$this->x_label_angle;

        if ( empty($which_color) ) 
            $which_color = $this->ndx_title_color;

        $x_pixels = $this->xtr($x_world) + $x_adjustment;
        $y_pixels = $this->ytr($y_world) + $y_adjustment;

        $this->DrawText($which_font, $which_angle, $x_pixels, $y_pixels,
                        $which_color, $data_label, $which_halign, $which_valign);

        return TRUE;

    }
    /*!
     * Draws the data label associated with a point in the plot.
     * This is different from x_labels drawn by DrawXTicks() and care
     * should be taken not to draw both, as they'd probably overlap.
     * Calling of this function in DrawLines(), etc is decided after x_data_label_pos value.
     * Leave the last parameter out, to avoid the drawing of vertical lines, no matter
     * what the setting is (for plots that need it, like DrawSquared())
     */
    function DrawXDataLabel($xlab, $xpos, $row=FALSE)
    {
        // FIXME!! not working...
        // if (($this->_x_label_cnt++ % $this->x_label_inc) != 0)
        //    return;

        $xlab = $this->FormatLabel('x', $xlab);

        // Labels below the plot area
        if ($this->x_data_label_pos == 'plotdown' || $this->x_data_label_pos == 'both')
            $this->DrawText($this->x_label_font, $this->x_label_angle,
                            $xpos, $this->plot_area[3] + $this->x_label_bot_offset,
                            $this->ndx_text_color, $xlab, 'center', 'top');

        // Labels above the plot area
        if ($this->x_data_label_pos == 'plotup' || $this->x_data_label_pos == 'both')
            $this->DrawText($this->x_label_font, $this->x_label_angle,
                            $xpos, $this->plot_area[1] - $this->x_label_top_offset,
                            $this->ndx_text_color, $xlab, 'center', 'bottom');

        // $row=0 means this is the first row. $row=FALSE means don't do any rows.
        if ($row !== FALSE && $this->draw_x_data_label_lines)
            $this->DrawXDataLine($xpos, $row);
        return TRUE;
    }

    /*!
     * Draws Vertical lines from data points up and down.
     * Which lines are drawn depends on the value of x_data_label_pos,
     * and whether this is at all done or not, on draw_x_data_label_lines
     *
     * \param xpos int position in pixels of the line.
     * \param row int index of the data row being drawn.
     */
    function DrawXDataLine($xpos, $row)
    {
        // Sets the line style for IMG_COLOR_STYLED lines (grid)
        if($this->dashed_grid) {
            $this->SetDashedStyle($this->ndx_light_grid_color);
            $style = IMG_COLOR_STYLED;
        } else {
            $style = $this->ndx_light_grid_color;
        }

        // Lines from the bottom up
        if ($this->x_data_label_pos == 'both') {
            ImageLine($this->img, $xpos, $this->plot_area[3], $xpos, $this->plot_area[1], $style);
        }
        // Lines from the bottom of the plot up to the max Y value at this X:
        else if ($this->x_data_label_pos == 'plotdown') {
            $ypos = $this->ytr($this->data_maxy[$row]);
            ImageLine($this->img, $xpos, $ypos, $xpos, $this->plot_area[3], $style);
        }
        // Lines from the top of the plot down to the min Y value at this X:
        else if ($this->x_data_label_pos == 'plotup') {
            $ypos = $this->ytr($this->data_miny[$row]);
            ImageLine($this->img, $xpos, $this->plot_area[1], $xpos, $ypos, $style);
        }
        return TRUE;
    }


    /*!
     * Draws the graph legend
     *
     * \note Base code submitted by Marlin Viss
     */
    function DrawLegend()
    {
        // Find maximum legend label line width.
        $max_width = 0;
        foreach ($this->legend as $line) {
            list($width, $unused) = $this->SizeText($this->legend_font, 0, $line);
            if ($width > $max_width) $max_width = $width;
        }

        // For the color box and line spacing, use a typical font character: 8.
        list($char_w, $char_h) = $this->SizeText($this->legend_font, 0, '8');

        // Normalize text alignment and colorbox alignment variables:
        $text_align = isset($this->legend_text_align) ? $this->legend_text_align : 'right';
        $colorbox_align = isset($this->legend_colorbox_align) ? $this->legend_colorbox_align : 'right';

        // Sizing parameters:
        $v_margin = $char_h/2;                         // Between vertical borders and labels
        $dot_height = $char_h + $this->line_spacing;   // Height of the small colored boxes
        // Overall legend box width e.g.: | space colorbox space text space |
        // where colorbox and each space are 1 char width.
        if ($colorbox_align != 'none') {
            $width = $max_width + 4 * $char_w;
            $draw_colorbox = True;
        } else {
            $width = $max_width + 2 * $char_w;
            $draw_colorbox = False;
        }

        //////// Calculate box position
        // User-defined position specified?
        if ( !isset($this->legend_x_pos) || !isset($this->legend_y_pos)) {
            // No, use default
            $box_start_x = $this->plot_area[2] - $width - $this->safe_margin;
            $box_start_y = $this->plot_area[1] + $this->safe_margin;
        } elseif (isset($this->legend_xy_world)) {
            // User-defined position in world-coordinates (See SetLegendWorld).
            $box_start_x = $this->xtr($this->legend_x_pos);
            $box_start_y = $this->ytr($this->legend_y_pos);
            unset($this->legend_xy_world);
        } else {
            // User-defined position in pixel coordinates.
            $box_start_x = $this->legend_x_pos;
            $box_start_y = $this->legend_y_pos;
        }

        // Lower right corner
        $box_end_y = $box_start_y + $dot_height*(count($this->legend)) + 2*$v_margin;
        $box_end_x = $box_start_x + $width;

        // Draw outer box
        ImageFilledRectangle($this->img, $box_start_x, $box_start_y, $box_end_x, $box_end_y, $this->ndx_bg_color);
        ImageRectangle($this->img, $box_start_x, $box_start_y, $box_end_x, $box_end_y, $this->ndx_grid_color);

        $color_index = 0;
        $max_color_index = count($this->ndx_data_colors) - 1;

        // Calculate color box and text horizontal positions.
        if (!$draw_colorbox) {
            if ($text_align == 'left')
                $x_pos = $box_start_x + $char_w;
            else
                $x_pos = $box_end_x - $char_w;
        } elseif ($colorbox_align == 'left') {
            $dot_left_x = $box_start_x + $char_w;
            $dot_right_x = $dot_left_x + $char_w;
            if ($text_align == 'left')
                $x_pos = $dot_left_x + 2 * $char_w;
            else
                $x_pos = $box_end_x - $char_w;
        } else {
            $dot_left_x = $box_end_x - 2 * $char_w;
            $dot_right_x = $dot_left_x + $char_w;
            if ($text_align == 'left')
                $x_pos = $box_start_x + $char_w;
            else
                $x_pos = $dot_left_x - $char_w;
        }

        // Calculate starting position of first text line.  The bottom of each color box
        // lines up with the bottom (baseline) of its text line.
        $y_pos = $box_start_y + $v_margin + $dot_height;

        foreach ($this->legend as $leg) {
            // Draw text with requested alignment:
            $this->DrawText($this->legend_font, 0, $x_pos, $y_pos,
                            $this->ndx_text_color, $leg, $text_align, 'bottom');
            if ($draw_colorbox) {
                // Draw a box in the data color
                $y1 = $y_pos - $dot_height + 1;
                $y2 = $y_pos - 1;
                ImageFilledRectangle($this->img, $dot_left_x, $y1, $dot_right_x, $y2,
                                     $this->ndx_data_colors[$color_index]);
                // Draw a rectangle around the box
                ImageRectangle($this->img, $dot_left_x, $y1, $dot_right_x, $y2,
                               $this->ndx_text_color);
            }
            $y_pos += $dot_height;

            $color_index++;
            if ($color_index > $max_color_index)
                $color_index = 0;
        }
        return TRUE;
    } // Function DrawLegend()


    /*!
     * TODO Draws a legend over (or below) an axis of the plot.
     */
    function DrawAxisLegend()
    {
        // Calculate available room
        // Calculate length of all items (boxes included)
        // Calculate number of lines and room it would take. FIXME: this should be known in CalcMargins()
        // Draw.
    }

/////////////////////////////////////////////
////////////////////             PLOT DRAWING
/////////////////////////////////////////////


    /*!
     * Draws a pie chart. Data has to be 'text-data' type.
     *
     *  This can work in two ways: the classical, with a column for each sector
     *  (computes the column totals and draws the pie with that)
     *  OR
     *  Takes each row as a sector and uses it's first value. This has the added
     *  advantage of using the labels provided, which is not the case with the
     *  former method. This might prove useful for pie charts from GROUP BY sql queries
     */
    function DrawPieChart()
    {
        $xpos = $this->plot_area[0] + $this->plot_area_width/2;
        $ypos = $this->plot_area[1] + $this->plot_area_height/2;
        $diameter = min($this->plot_area_width, $this->plot_area_height);
        $radius = $diameter/2;

        // Get sum of each column? One pie slice per column
        if ($this->data_type === 'text-data') {
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                for ($j = 1; $j < $this->num_recs[$i]; $j++) {      // Label ($row[0]) unused in these pie charts
                    @ $sumarr[$j] += abs($this->data[$i][$j]);      // NOTE!  sum > 0 to make pie charts
                }
            }
        }
        // Or only one column per row, one pie slice per row?
        else if ($this->data_type == 'text-data-single') {
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                $legend[$i] = $this->data[$i][0];                   // Set the legend to column labels
                $sumarr[$i] = $this->data[$i][1];
            }
        }
        else if ($this->data_type == 'data-data') {
            for ($i = 0; $i < $this->num_data_rows; $i++) {
                for ($j = 2; $j < $this->num_recs[$i]; $j++) {
                    @ $sumarr[$j] += abs($this->data[$i][$j]);
                }
            }
        }
        else {
            return $this->PrintError("DrawPieChart(): Data type '$this->data_type' not supported.");
        }

        $total = array_sum($sumarr);

        if ($total == 0) {
            return $this->PrintError('DrawPieChart(): Empty data set');
        }

        if ($this->shading) {
            $diam2 = $diameter / 2;
        } else {
            $diam2 = $diameter;
        }
        $max_data_colors = count ($this->data_colors);

        for ($h = $this->shading; $h >= 0; $h--) {
            $color_index = 0;
            $start_angle = 0;
            $end_angle = 0;
            foreach ($sumarr as $val) {
                // For shaded pies: the last one (at the top of the "stack") has a brighter color:
                if ($h == 0)
                    $slicecol = $this->ndx_data_colors[$color_index];
                else
                    $slicecol = $this->ndx_data_dark_colors[$color_index];

                $label_txt = $this->number_format(($val / $total * 100), $this->y_precision) . '%';
                $val = 360 * ($val / $total);

                // NOTE that imagefilledarc measures angles CLOCKWISE (go figure why),
                // so the pie chart would start clockwise from 3 o'clock, would it not be
                // for the reversal of start and end angles in imagefilledarc()
                // Also note ImageFilledArc only takes angles in integer degrees, and if the
                // the start and end angles match then you get a full circle not a zero-width
                // pie. This is bad. So skip any zero-size wedge. On the other hand, we cannot
                // let cumulative error from rounding to integer result in missing wedges. So
                // keep the running total as a float, and round the angles. It should not
                // be necessary to check that the last wedge ends at 360 degrees.
                $start_angle = $end_angle;
                $end_angle += $val;
                // This method of conversion to integer - truncate after reversing it - was
                // chosen to match the implicit method of PHPlot<=5.0.4 to get the same slices.
                $arc_start_angle = (int)(360 - $start_angle);
                $arc_end_angle = (int)(360 - $end_angle);

                if ($arc_start_angle > $arc_end_angle) {
                    $mid_angle = deg2rad($end_angle - ($val / 2));

                    // Draw the slice
                    ImageFilledArc($this->img, $xpos, $ypos+$h, $diameter, $diam2,
                                   $arc_end_angle, $arc_start_angle,
                                   $slicecol, IMG_ARC_PIE);

                    // Draw the labels only once
                    if ($h == 0) {
                        // Draw the outline
                        if (! $this->shading)
                            ImageFilledArc($this->img, $xpos, $ypos+$h, $diameter, $diam2,
                                           $arc_end_angle, $arc_start_angle,
                                           $this->ndx_grid_color, IMG_ARC_PIE | IMG_ARC_EDGED |IMG_ARC_NOFILL);


                        // The '* 1.2' trick is to get labels out of the pie chart so there are more
                        // chances they can be seen in small sectors.
                        $label_x = $xpos + ($diameter * 1.2 * cos($mid_angle)) * $this->label_scale_position;
                        $label_y = $ypos+$h - ($diam2 * 1.2 * sin($mid_angle)) * $this->label_scale_position;

                        $this->DrawText($this->generic_font, 0, $label_x, $label_y, $this->ndx_grid_color,
                                        $label_txt, 'center', 'center');
                    }
                }
                if (++$color_index >= $max_data_colors)
                    $color_index = 0;
            }   // end for
        }   // end for
        return TRUE;
    }


    /*!
     * Supported data formats: data-data-error, text-data-error (doesn't exist yet)
     * ( data comes in as array("title", x, y, error+, error-, y2, error2+, error2-, ...) )
     */
    function DrawDotsError()
    {
        if ($this->data_type != 'data-data-error') {
            return $this->PrintError("DrawDotsError(): Data type '$this->data_type' not supported.");
        }

        // Suppress duplicate X data labels in linepoints mode; let DrawLinesError() do them.
        $do_labels = ($this->plot_type != 'linepoints');

        for($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                // Skip record #0 (title)

            $x_now = $this->data[$row][$record++];  // Read it, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates.

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none' && $do_labels)
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Now go for Y, E+, E-
            for ($idx = 0; $record < $this->num_recs[$row]; $idx++) {
                    // Y:
                    $y_now = $this->data[$row][$record++];
                    $this->DrawDot($x_now, $y_now, $idx, $this->ndx_data_colors[$idx]);

                    // Error +
                    $val = $this->data[$row][$record++];
                    $this->DrawYErrorBar($x_now, $y_now, $val, $this->error_bar_shape,
                                         $this->ndx_error_bar_colors[$idx]);
                    // Error -
                    $val = $this->data[$row][$record++];
                    $this->DrawYErrorBar($x_now, $y_now, -$val, $this->error_bar_shape,
                                         $this->ndx_error_bar_colors[$idx]);
            }
        }
        return TRUE;
    } // function DrawDotsError()


    /*
     * Supported data types:
     *  - data-data ("title", x, y1, y2, y3, ...)
     *  - text-data ("title", y1, y2, y3, ...)
     */
    function DrawDots()
    {
        if (!$this->CheckOption($this->data_type, 'text-data, data-data', __FUNCTION__))
            return FALSE;

        // Suppress duplicate X data labels in linepoints mode; let DrawLines() do them.
        $do_labels = ($this->plot_type != 'linepoints');

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)

            // Do we have a value for X?
            if ($this->data_type == 'data-data')
                $x_now = $this->data[$row][$rec++];  // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;       // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none' && $do_labels)
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Proceed with Y values
            for($idx = 0;$rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data
                    $this->DrawDot($x_now, $this->data[$row][$rec],
                                   $idx, $this->ndx_data_colors[$idx]);
                }
            }
        }
        return TRUE;
    } //function DrawDots


    /*!
     * A clean, fast routine for when you just want charts like stock volume charts
     */
    function DrawThinBarLines()
    {
        if (!$this->CheckOption($this->data_type, 'text-data, data-data', __FUNCTION__))
            return FALSE;

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                    // Skip record #0 (data label)

            // Do we have a value for X?
            if ($this->data_type == 'data-data')
                $x_now = $this->data[$row][$rec++];  // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;       // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);

            // Draw X Data labels?
            if ($this->x_data_label_pos != 'none')
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Proceed with Y values
            for($idx = 0;$rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data
                    ImageSetThickness($this->img, $this->line_widths[$idx]);
                    // Draws a line from user defined x axis position up to ytr($val)
                    ImageLine($this->img, $x_now_pixels, $this->x_axis_y_pixels, $x_now_pixels,
                              $this->ytr($this->data[$row][$rec]), $this->ndx_data_colors[$idx]);
                }
            }
        }

        ImageSetThickness($this->img, 1);
        return TRUE;
    }  //function DrawThinBarLines

    /*!
     *
     */
    function DrawYErrorBar($x_world, $y_world, $error_height, $error_bar_type, $color)
    {
        /*
        // TODO: add a parameter to show datalabels next to error bars?
        // something like this:
        if ($this->x_data_label_pos == 'plot')
            $this->DrawText($this->error_font, 90, $x1, $y2,
                            $color, $label, 'center', 'bottom');
        */

        $x1 = $this->xtr($x_world);
        $y1 = $this->ytr($y_world);
        $y2 = $this->ytr($y_world+$error_height) ;

        ImageSetThickness($this->img, $this->error_bar_line_width);
        ImageLine($this->img, $x1, $y1 , $x1, $y2, $color);

        switch ($error_bar_type) {
        case 'line':
            break;
        case 'tee':
            ImageLine($this->img, $x1-$this->error_bar_size, $y2, $x1+$this->error_bar_size, $y2, $color);
            break;
        default:
            ImageLine($this->img, $x1-$this->error_bar_size, $y2, $x1+$this->error_bar_size, $y2, $color);
            break;
        }

        ImageSetThickness($this->img, 1);
        return TRUE;
    }

    /*!
     * Draws a styled dot. Uses world coordinates.
     * Supported types: 'halfline', 'line', 'plus', 'cross', 'rect', 'circle', 'dot',
     * 'diamond', 'triangle', 'trianglemid'
     */
    function DrawDot($x_world, $y_world, $record, $color)
    {
        // TODO: optimize, avoid counting every time we are called.
        $record = $record % count ($this->point_shapes);

        $half_point = $this->point_sizes[$record] / 2;

        $x_mid = $this->xtr($x_world);
        $y_mid = $this->ytr($y_world);

        $x1 = $x_mid - $half_point;
        $x2 = $x_mid + $half_point;
        $y1 = $y_mid - $half_point;
        $y2 = $y_mid + $half_point;

        switch ($this->point_shapes[$record]) {
        case 'halfline':
            ImageLine($this->img, $x1, $y_mid, $x_mid, $y_mid, $color);
            break;
        case 'line':
            ImageLine($this->img, $x1, $y_mid, $x2, $y_mid, $color);
            break;
        case 'plus':
            ImageLine($this->img, $x1, $y_mid, $x2, $y_mid, $color);
            ImageLine($this->img, $x_mid, $y1, $x_mid, $y2, $color);
            break;
        case 'cross':
            ImageLine($this->img, $x1, $y1, $x2, $y2, $color);
            ImageLine($this->img, $x1, $y2, $x2, $y1, $color);
            break;
        case 'rect':
            ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        case 'circle':
            ImageArc($this->img, $x_mid, $y_mid, $this->point_sizes[$record], $this->point_sizes[$record],
                     0, 360, $color);
            break;
        case 'dot':
            ImageFilledArc($this->img, $x_mid, $y_mid, $this->point_sizes[$record],
                           $this->point_sizes[$record], 0, 360, $color, IMG_ARC_PIE);
            break;
        case 'diamond':
            $arrpoints = array( $x1, $y_mid, $x_mid, $y1, $x2, $y_mid, $x_mid, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 4, $color);
            break;
        case 'triangle':
            $arrpoints = array( $x1, $y_mid, $x2, $y_mid, $x_mid, $y2);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'trianglemid':
            $arrpoints = array( $x1, $y1, $x2, $y1, $x_mid, $y_mid);
            ImageFilledPolygon($this->img, $arrpoints, 3, $color);
            break;
        case 'none':
            break;
        default:
            ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $color);
            break;
        }
        return TRUE;
    }

    /*!
     * Draw an area plot. Supported data types:
     *      'text-data'
     *      'data-data'
     * NOTE: This function used to add first and last data values even on incomplete
     *       sets. That is not the behaviour now. As for missing data in between,
     *       there are two posibilities: replace the point with one on the X axis (previous
     *       way), or forget about it and use the preceding and following ones to draw the polygon.
     *       There is the possibility to use both, we just need to add the method to set
     *       it. Something like SetMissingDataBehaviour(), for example.
     */
    function DrawArea()
    {
        $incomplete_data_defaults_to_x_axis = FALSE;        // TODO: make this configurable

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $rec = 1;                                       // Skip record #0 (data label)

            if ($this->data_type == 'data-data')            // Do we have a value for X?
                $x_now = $this->data[$row][$rec++];         // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                      // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates


            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Proceed with Y values
            // Create array of points for imagefilledpolygon()
            for($idx = 0; $rec < $this->num_recs[$row]; $rec++, $idx++) {
                if (is_numeric($this->data[$row][$rec])) {              // Allow for missing Y data
                    $y_now_pixels = $this->ytr($this->data[$row][$rec]);

                    $posarr[$idx][] = $x_now_pixels;
                    $posarr[$idx][] = $y_now_pixels;

                    $num_points[$idx] = isset($num_points[$idx]) ? $num_points[$idx]+1 : 1;
                }
                // If there's missing data...
                else {
                    if (isset ($incomplete_data_defaults_to_x_axis)) {
                        $posarr[$idx][] = $x_now_pixels;
                        $posarr[$idx][] = $this->x_axis_y_pixels;
                        $num_points[$idx] = isset($num_points[$idx]) ? $num_points[$idx]+1 : 1;
                    }
                }
            }
        }   // end for

        $end = count($posarr);
        for ($i = 0; $i < $end; $i++) {
            // Prepend initial points. X = first point's X, Y = x_axis_y_pixels
            $x = $posarr[$i][0];
            array_unshift($posarr[$i], $x, $this->x_axis_y_pixels);

            // Append final points. X = last point's X, Y = x_axis_y_pixels
            $x = $posarr[$i][count($posarr[$i])-2];
            array_push($posarr[$i], $x, $this->x_axis_y_pixels);

            $num_points[$i] += 2;

            // Draw the poligon
            ImageFilledPolygon($this->img, $posarr[$i], $num_points[$i], $this->ndx_data_colors[$i]);
        }
        return TRUE;
    } // function DrawArea()


    /*!
     * Draw Lines. Supported data-types:
     *      'data-data',
     *      'text-data'
     * NOTE: Please see the note regarding incomplete data sets on DrawArea()
     */
    function DrawLines()
    {
        // This will tell us if lines have already begun to be drawn.
        // It is an array to keep separate information for every line, with a single
        // variable we would sometimes get "undefined offset" errors and no plot...
        $start_lines = array_fill(0, $this->records_per_group, FALSE);

        if ($this->data_type == 'text-data') {
            $lastx[0] = $this->xtr(0);
            $lasty[0] = $this->xtr(0);
        }

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->data_type == 'data-data')            // Do we have a value for X?
                $x_now = $this->data[$row][$record++];      // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                      // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (($line_style = $this->line_styles[$idx]) == 'none')
                    continue; //Allow suppressing entire line, useful with linepoints
                if (is_numeric($this->data[$row][$record])) {           //Allow for missing Y data
                    $y_now_pixels = $this->ytr($this->data[$row][$record]);

                    if ($start_lines[$idx] == TRUE) {
                        // Set line width, revert it to normal at the end
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        if ($line_style == 'dashed') {
                            $this->SetDashedStyle($this->ndx_data_colors[$idx]);
                            ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx],
                                      IMG_COLOR_STYLED);
                        } else {
                            ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx],
                                      $this->ndx_data_colors[$idx]);
                        }

                    }
                    $lasty[$idx] = $y_now_pixels;
                    $lastx[$idx] = $x_now_pixels;
                    $start_lines[$idx] = TRUE;
                }
                // Y data missing... should we leave a blank or not?
                else if ($this->draw_broken_lines) {
                    $start_lines[$idx] = FALSE;
                }
            }   // end for
        }   // end for

        ImageSetThickness($this->img, 1);       // Revert to original state for lines to be drawn later.
        return TRUE;
    } // function DrawLines()


    /*!
     * Draw lines with error bars - data comes in as
     *      array("label", x, y, error+, error-, y2, error2+, error2-, ...);
     */
    function DrawLinesError()
    {
        if ($this->data_type != 'data-data-error') {
            return $this->PrintError("DrawLinesError(): Data type '$this->data_type' not supported.");
        }

        $start_lines = array_fill(0, $this->records_per_group, FALSE);

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now = $this->data[$row][$record++];          // Read X value, advance record index

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates.


            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels, $row);

            // Now go for Y, E+, E-
            for ($idx = 0; $record < $this->num_recs[$row]; $idx++) {
                if (($line_style = $this->line_styles[$idx]) == 'none')
                    continue; //Allow suppressing entire line, useful with linepoints
                // Y
                $y_now = $this->data[$row][$record++];
                $y_now_pixels = $this->ytr($y_now);

                if ($start_lines[$idx] == TRUE) {
                    ImageSetThickness($this->img, $this->line_widths[$idx]);

                    if ($line_style == 'dashed') {
                        $this->SetDashedStyle($this->ndx_data_colors[$idx]);
                        ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx],
                                  IMG_COLOR_STYLED);
                    } else {
                        ImageLine($this->img, $x_now_pixels, $y_now_pixels, $lastx[$idx], $lasty[$idx],
                                  $this->ndx_data_colors[$idx]);
                    }
                }

                // Error+
                $val = $this->data[$row][$record++];
                $this->DrawYErrorBar($x_now, $y_now, $val, $this->error_bar_shape,
                                     $this->ndx_error_bar_colors[$idx]);

                // Error-
                $val = $this->data[$row][$record++];
                $this->DrawYErrorBar($x_now, $y_now, -$val, $this->error_bar_shape,
                                     $this->ndx_error_bar_colors[$idx]);

                // Update indexes:
                $start_lines[$idx] = TRUE;   // Tells us if we already drew the first column of points,
                                             // thus having $lastx and $lasty ready for the next column.
                $lastx[$idx] = $x_now_pixels;
                $lasty[$idx] = $y_now_pixels;
            }   // end while
        }   // end for

        ImageSetThickness($this->img, 1);   // Revert to original state for lines to be drawn later.
        return TRUE;
    }   // function DrawLinesError()



    /*!
     * This is a mere copy of DrawLines() with one more line drawn for each point
     */
    function DrawSquared()
    {
        // This will tell us if lines have already begun to be drawn.
        // It is an array to keep separate information for every line, for with a single
        // variable we could sometimes get "undefined offset" errors and no plot...
        $start_lines = array_fill(0, $this->records_per_group, FALSE);

        if ($this->data_type == 'text-data') {
            $lastx[0] = $this->xtr(0);
            $lasty[0] = $this->xtr(0);
        }

        for ($row = 0, $cnt = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            if ($this->data_type == 'data-data')            // Do we have a value for X?
                $x_now = $this->data[$row][$record++];      // Read it, advance record index
            else
                $x_now = 0.5 + $cnt++;                      // Place text-data at X = 0.5, 1.5, 2.5, etc...

            $x_now_pixels = $this->xtr($x_now);             // Absolute coordinates

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels); // notice there is no last param.

            // Draw Lines
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {               // Allow for missing Y data
                    $y_now_pixels = $this->ytr($this->data[$row][$record]);

                    if ($start_lines[$idx] == TRUE) {
                        // Set line width, revert it to normal at the end
                        ImageSetThickness($this->img, $this->line_widths[$idx]);

                        if ($this->line_styles[$idx] == 'dashed') {
                            $this->SetDashedStyle($this->ndx_data_colors[$idx]);
                            ImageLine($this->img, $lastx[$idx], $lasty[$idx], $x_now_pixels, $lasty[$idx],
                                      IMG_COLOR_STYLED);
                            ImageLine($this->img, $x_now_pixels, $lasty[$idx], $x_now_pixels, $y_now_pixels,
                                      IMG_COLOR_STYLED);
                        } else {
                            ImageLine($this->img, $lastx[$idx], $lasty[$idx], $x_now_pixels, $lasty[$idx],
                                      $this->ndx_data_colors[$idx]);
                            ImageLine($this->img, $x_now_pixels, $lasty[$idx], $x_now_pixels, $y_now_pixels,
                                      $this->ndx_data_colors[$idx]);
                        }
                    }
                    $lastx[$idx] = $x_now_pixels;
                    $lasty[$idx] = $y_now_pixels;
                    $start_lines[$idx] = TRUE;
                }
                // Y data missing... should we leave a blank or not?
                else if ($this->draw_broken_lines) {
                    $start_lines[$idx] = FALSE;
                }
            }
        }   // end while

        ImageSetThickness($this->img, 1);
        return TRUE;
    } // function DrawSquared()


    /*!
     * Data comes in as array("title", x, y, y2, y3, ...)
     */
    function DrawBars()
    {
        if ($this->data_type != 'text-data') {
            return $this->PrintError('DrawBars(): Bar plots must be text-data: use function SetDataType("text-data")');
        }

        // This is the X offset from the bar group's label center point to the left side of the first bar
        // in the group. See also CalcBarWidths above.
        $x_first_bar = (($this->records_per_group - 1) * $this->record_bar_width) / 2 - $this->bar_adjust_gap;

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now_pixels = $this->xtr(0.5 + $row);         // Place text-data at X = 0.5, 1.5, 2.5, etc...

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Lower left X of first bar in the group:
            $x1 = $x_now_pixels - $x_first_bar;

            // Draw the bars in the group:
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {       // Allow for missing Y data
                    $x2 = $x1 + $this->actual_bar_width;

                    if ($this->data[$row][$record] < $this->x_axis_position) {
                        $y1 = $this->x_axis_y_pixels;
                        $y2 = $this->ytr($this->data[$row][$record]);
                        $upgoing_bar = False;
                    } else {
                        $y1 = $this->ytr($this->data[$row][$record]);
                        $y2 = $this->x_axis_y_pixels;
                        $upgoing_bar = True;
                    }

                    // Draw the bar
                    ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $this->ndx_data_colors[$idx]);

                    if ($this->shading) {                           // Draw the shade?
                        ImageFilledPolygon($this->img, array($x1, $y1,
                                                       $x1 + $this->shading, $y1 - $this->shading,
                                                       $x2 + $this->shading, $y1 - $this->shading,
                                                       $x2 + $this->shading, $y2 - $this->shading,
                                                       $x2, $y2,
                                                       $x2, $y1),
                                           6, $this->ndx_data_dark_colors[$idx]);
                    }
                    // Or draw a border?
                    else {
                        ImageRectangle($this->img, $x1, $y1, $x2,$y2, $this->ndx_data_border_colors[$idx]);
                    }

                    // Draw optional data labels above the bars (or below, for negative values).
                    if ( $this->y_data_label_pos == 'plotin') {
                        if ($upgoing_bar) {
                          $v_align = 'bottom';
                          $y_offset = -5 - $this->shading;
                        } else {
                          $v_align = 'top';
                          $y_offset = 2;
                        }
                        $this->DrawDataLabel($this->y_label_font, NULL, $row+0.5, $this->data[$row][$record], '',
                                $this->data[$row][$record], 'center', $v_align,
                                ($idx + 0.5) * $this->record_bar_width - $x_first_bar, $y_offset);
                    }

                }
                // Step to next bar in group:
                $x1 += $this->record_bar_width;
            }   // end for
        }   // end for
        return TRUE;
    } //function DrawBars


    /*!
     * Data comes in as array("title", x, y, y2, y3, ...)
     * \note Original stacked bars idea by Laurent Kruk < lolok at users.sourceforge.net >
     */
    function DrawStackedBars()
    {
        if ($this->data_type != 'text-data') {
            return $this->PrintError('DrawStackedBars(): Bar plots must be text-data: use SetDataType("text-data")');
        }

        // This is the X offset from the bar's label center point to the left side of the bar.
        $x_first_bar = $this->record_bar_width / 2 - $this->bar_adjust_gap;

        for ($row = 0; $row < $this->num_data_rows; $row++) {
            $record = 1;                                    // Skip record #0 (data label)

            $x_now_pixels = $this->xtr(0.5 + $row);         // Place text-data at X = 0.5, 1.5, 2.5, etc...

            if ($this->x_data_label_pos != 'none')          // Draw X Data labels?
                $this->DrawXDataLabel($this->data[$row][0], $x_now_pixels);

            // Lower left and lower right X of the bars in this group:
            $x1 = $x_now_pixels - $x_first_bar;
            $x2 = $x1 + $this->actual_bar_width;

            // Draw the bars
            $oldv = 0;
            for ($idx = 0; $record < $this->num_recs[$row]; $record++, $idx++) {
                if (is_numeric($this->data[$row][$record])) {       // Allow for missing Y data

                    $y1 = $this->ytr(abs($this->data[$row][$record]) + $oldv);
                    $y2 = $this->ytr($this->x_axis_position + $oldv);
                    $oldv += abs($this->data[$row][$record]);

                    // Draw the bar
                    ImageFilledRectangle($this->img, $x1, $y1, $x2, $y2, $this->ndx_data_colors[$idx]);

                    if ($this->shading) {                           // Draw the shade?
                        ImageFilledPolygon($this->img, array($x1, $y1,
                                                       $x1 + $this->shading, $y1 - $this->shading,
                                                       $x2 + $this->shading, $y1 - $this->shading,
                                                       $x2 + $this->shading, $y2 - $this->shading,
                                                       $x2, $y2,
                                                       $x2, $y1),
                                           6, $this->ndx_data_dark_colors[$idx]);
                    }
                    // Or draw a border?
                    else {
                        ImageRectangle($this->img, $x1, $y1, $x2,$y2, $this->ndx_data_border_colors[$idx]);
                    }
                }
            }   // end for
        }   // end for
        return TRUE;
    } //function DrawStackedBars


    /*!
     *
     */
    function DrawGraph()
    {
        // Test for missing image, missing data, empty data:
        if (! $this->img) {
            return $this->PrintError('DrawGraph(): No image resource allocated');
        }
        if (empty($this->data) || ! is_array($this->data)) {
            return $this->PrintError("DrawGraph(): No data array");
        }
        if ($this->total_records == 0) {
            return $this->PrintError('DrawGraph(): Empty data set');
        }

        // For pie charts: don't draw grid or border or axes, and maximize area usage.
        // These controls can be split up in the future if needed.
        $draw_axes = ($this->plot_type != 'pie');

        // Get maxima and minima for scaling:
        if (!$this->FindDataLimits())
            return FALSE;

        // Set plot area world values (plot_max_x, etc.):
        if (!$this->CalcPlotAreaWorld())
            return FALSE;

        // Calculate X and Y axis positions in World Coordinates:
        $this->CalcAxisPositions();

        // Calculate the plot margins, if needed.
        // For pie charts, set the $maximize argument to maximize space usage.
        $this->CalcMargins(!$draw_axes);

        // Calculate the actual plot area in device coordinates:
        $this->CalcPlotAreaPixels();

        // Calculate the mapping between world and device coordinates:
        $this->CalcTranslation();

        // Pad color and style arrays to fit records per group:
        $this->PadArrays();
        $this->DoCallback('draw_setup');

        $this->DrawBackground();
        $this->DrawImageBorder();
        $this->DoCallback('draw_image_background');

        $this->DrawPlotAreaBackground();
        $this->DoCallback('draw_plotarea_background');

        $this->DrawTitle();
        $this->DrawXTitle();
        $this->DrawYTitle();
        $this->DoCallback('draw_titles');

        if ($draw_axes && ! $this->grid_at_foreground) {   // Usually one wants grids to go back, but...
            $this->DrawYAxis();     // Y axis must be drawn before X axis (see DrawYAxis())
            $this->DrawXAxis();
            $this->DoCallback('draw_axes');
        }

        switch ($this->plot_type) {
        case 'thinbarline':
            $this->DrawThinBarLines();
            break;
        case 'area':
            $this->DrawArea();
            break;
        case 'squared':
            $this->DrawSquared();
            break;
        case 'lines':
            if ( $this->data_type == 'data-data-error') {
                $this->DrawLinesError();
            } else {
                $this->DrawLines();
            }
            break;
        case 'linepoints':
            if ( $this->data_type == 'data-data-error') {
                $this->DrawLinesError();
                $this->DrawDotsError();
            } else {
                $this->DrawLines();
                $this->DrawDots();
            }
            break;
        case 'points';
            if ( $this->data_type == 'data-data-error') {
                $this->DrawDotsError();
            } else {
                $this->DrawDots();
            }
            break;
        case 'pie':
            $this->DrawPieChart();
            break;
        case 'stackedbars':
            $this->CalcBarWidths();
            $this->DrawStackedBars();
            break;
        case 'bars':
        default:
            $this->plot_type = 'bars';  // Set it if it wasn't already set. (necessary?)
            $this->CalcBarWidths();
            $this->DrawBars();
            break;
        }   // end switch
        $this->DoCallback('draw_graph');

        if ($draw_axes && $this->grid_at_foreground) {   // Usually one wants grids to go back, but...
            $this->DrawYAxis();     // Y axis must be drawn before X axis (see DrawYAxis())
            $this->DrawXAxis();
            $this->DoCallback('draw_axes');
        }

        if ($draw_axes) {
            $this->DrawPlotBorder();
            $this->DoCallback('draw_border');
        }

        if ($this->legend) {
            $this->DrawLegend();
            $this->DoCallback('draw_legend');
        }

        if ($this->print_image && !$this->PrintImage())
            return FALSE;

        return TRUE;
    } //function DrawGraph()

/////////////////////////////////////////////
//////////////////         DEPRECATED METHODS
/////////////////////////////////////////////

    /*!
     * Deprecated, use SetYTickPos()
     */
    function SetDrawVertTicks($which_dvt)
    {
        if ($which_dvt != 1)
            $this->SetYTickPos('none');
        return TRUE;
    }

    /*!
     * Deprecated, use SetXTickPos()
     */
    function SetDrawHorizTicks($which_dht)
    {
        if ($which_dht != 1)
           $this->SetXTickPos('none');
        return TRUE;
    }

    /*!
     * \deprecated Use SetNumXTicks()
     */
    function SetNumHorizTicks($n)
    {
        return $this->SetNumXTicks($n);
    }

    /*!
     * \deprecated Use SetNumYTicks()
     */
    function SetNumVertTicks($n)
    {
        return $this->SetNumYTicks($n);
    }

    /*!
     * \deprecated Use SetXTickIncrement()
     */
    function SetHorizTickIncrement($inc)
    {
        return $this->SetXTickIncrement($inc);
    }


    /*!
     * \deprecated Use SetYTickIncrement()
     */
    function SetVertTickIncrement($inc)
    {
        return $this->SetYTickIncrement($inc);
    }

    /*!
     * \deprecated Use SetYTickPos()
     */
    function SetVertTickPosition($which_tp)
    {
        return $this->SetYTickPos($which_tp);
    }

    /*!
     * \deprecated Use SetXTickPos()
     */
    function SetHorizTickPosition($which_tp)
    {
        return $this->SetXTickPos($which_tp);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function SetTitleFontSize($which_size)
    {
        return $this->SetFont('title', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function SetAxisFontSize($which_size)
    {
        $this->SetFont('x_label', $which_size);
        $this->SetFont('y_label', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function SetSmallFontSize($which_size)
    {
        return $this->SetFont('generic', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function SetXLabelFontSize($which_size)
    {
        return $this->SetFont('x_title', $which_size);
    }

    /*!
     * \deprecated Use SetFont()
     */
    function SetYLabelFontSize($which_size)
    {
        return $this->SetFont('y_title', $which_size);
    }

    /*!
     * \deprecated Use SetXTitle()
     */
    function SetXLabel($which_xlab)
    {
        return $this->SetXTitle($which_xlab);
    }

    /*!
     * \deprecated Use SetYTitle()
     */
    function SetYLabel($which_ylab)
    {
        return $this->SetYTitle($which_ylab);
    }

    /*!
     * \deprecated Use SetXTickLength() and SetYTickLength() instead.
     */
    function SetTickLength($which_tl)
    {
        $this->SetXTickLength($which_tl);
        $this->SetYTickLength($which_tl);
        return TRUE;
    }

    /*!
     * \deprecated  Use SetYLabelType()
     */
    function SetYGridLabelType($which_yglt)
    {
        return $this->SetYLabelType($which_yglt);
    }

    /*!
     * \deprecated  Use SetXLabelType()
     */
    function SetXGridLabelType($which_xglt)
    {
        return $this->SetXLabelType($which_xglt);
    }
    /*!
     * \deprecated Use SetYTickLabelPos()
     */
    function SetYGridLabelPos($which_yglp)
    {
        return $this->SetYTickLabelPos($which_yglp);
    }
    /*!
     * \deprecated Use SetXTickLabelPos()
     */
    function SetXGridLabelPos($which_xglp)
    {
        return $this->SetXTickLabelPos($which_xglp);
    }


    /*!
     * \deprecated Use SetXtitle()
     */
    function SetXTitlePos($xpos)
    {
        $this->x_title_pos = $xpos;
        return TRUE;
    }

    /*!
     * \deprecated Use SetYTitle()
     */
    function SetYTitlePos($xpos)
    {
        $this->y_title_pos = $xpos;
        return TRUE;
    }

    /*!
     * \deprecated Use SetXLabelAngle()
     */
    function SetXDataLabelAngle($which_xdla)
    {
        return $this->SetXLabelAngle($which_xdla);
    }

    /*!
     * Draw Labels (not grid labels) on X Axis, following data points. Default position is
     * down of plot. Care must be taken not to draw these and x_tick_labels as they'd probably overlap.
     *
     * \deprecated Use SetXDataLabelPos()
     */
    function SetDrawXDataLabels($which_dxdl)
    {
        if ($which_dxdl == '1' )
            $this->SetXDataLabelPos('plotdown');
        else
            $this->SetXDataLabelPos('none');
    }

    /*!
     * \deprecated
     */
    function SetNewPlotAreaPixels($x1, $y1, $x2, $y2)
    {
        //Like in GD 0, 0 is upper left set via pixel Coordinates
        $this->plot_area = array($x1, $y1, $x2, $y2);
        $this->plot_area_width = $this->plot_area[2] - $this->plot_area[0];
        $this->plot_area_height = $this->plot_area[3] - $this->plot_area[1];
        $this->y_top_margin = $this->plot_area[1];

        if (isset($this->plot_max_x))
            $this->CalcTranslation();

        return TRUE;
    }

    /*!
     * \deprecated Use _SetRGBColor()
     */
    function SetColor($which_color)
    {
        $this->SetRGBColor($which_color);
        return TRUE;
    }

    /*
     * \deprecated Use SetLineWidths().
     */
    function SetLineWidth($which_lw)
    {

        $this->SetLineWidths($which_lw);

        if (!$this->error_bar_line_width) {
            $this->SetErrorBarLineWidth($which_lw);
        }
        return TRUE;
    }

    /*
     * \deprecated Use SetPointShapes().
     */
    function SetPointShape($which_pt)
    {
        $this->SetPointShapes($which_pt);
        return TRUE;
    }

    /*
     * \deprecated Use SetPointSizes().
     */
    function SetPointSize($which_ps)
    {
        $this->SetPointSizes($which_ps);
        return TRUE;
    }
}  // class PHPlot
