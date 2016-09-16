<?php
namespace CaT\Plugins\TalentAssessment\Observations;

class ReportSVGRenderer {

	/**
	 * Properties of the render box. Total width = 1000. Height is variable.
	 */
	const MAX_WIDTH = 1000;

	/**
	 * @var array
	 */
	protected $categories;

	/**
	 * @var array
	 */
	protected $scale;

	/**
	 * Top padding of the render box.
	 *
	 * @param \ilTemplate 	$tpl
	 */
	public function __construct(\ilTemplate $tpl) {
		$this->tpl = $tpl;
		$this->categories = array();
		$this->scale = array();
	}

	/**
	 * Top padding of the render box.
	 *
	 * @param int 	$padding_top
	 */
	public function setPaddingTop($padding_top) {
		$this->padding_top = $padding_top;
	}

	/**
	 * Bottom padding of the render box.
	 *
	 * @param int 	$padding_bottom
	 */
	public function setPaddingBottom($padding_bottom) {
		$this->padding_bottom = $padding_bottom;
	}

	/**
	 * Width of the render area inside box.
	 * Must be < 1000.
	 *
	 * @param int 	$inner_width
	 */
	public function setInnerWidth($inner_width) {
		if($inner_width > self::MAX_WIDTH) {
			throw new \ilException('width must be < 1000');
		}
		$this->inner_width = $inner_width;
	}

	/**
	 * Properties of the legend render box.
	 */

	/**
	 * Locate legend box verticaly inside the render box.
	 *
	 * @param int 	$legend_position_vertical
	 */
	public function setLegendPositionVertical($legend_position_vertical) {
		$this->legend_position_vertical = $legend_position_vertical;
	}

	/**
	 * Width of delimiters between legend scale bars.
	 *
	 * @param int 	$legend_delimiter_width
	 */
	public function setLegendDelimiterWidth($legend_delimiter_width) {
		$this->legend_delimiter_width = $legend_delimiter_width;
	}

	/**
	 * Set legend scale bar height.
	 *
	 * @param int 	$legend_bar_height
	 */
	public function setLegendBarHeight($legend_bar_height) {
		$this->legend_bar_height = $legend_bar_height;
	}

	/**
	 * Locate legend bars vertically inside the legend box.
	 *
	 * @param int 	$legend_bar_position
	 */
	public function setLegendBarVerticalPosition($legend_bar_position) {
		$this->legend_bar_position = $legend_bar_position;
	}


	/**
	 * Set the vertical distance from legend box of the graph box.
	 *
	 * @param int 	$graph_legend_distance
	 */
	public function setGraphVerticalDistanceLegend($graph_legend_distance) {
		$this->graph_legend_distance = $graph_legend_distance;
	}

	/**
	 * Set graph box top and bottom padding around graph bar inside category box.
	 *
	 * @param int 	$graph_block_padding
	 */
	public function setCategoryBlockPadding($graph_block_padding) {
		$this->graph_block_padding = $graph_block_padding;
	}

	/**
	 * Set height of graph bar.
	 *
	 * @param int 	$cat_graph_row_height
	 */
	public function setCategoryGraphRowHeight($cat_graph_row_height) {
		$this->cat_graph_row_height = $cat_graph_row_height;
	}

	/**
	 * Set delimiter width in graph bar.
	 *
	 * @param int 	$cat_block_delimiter_width
	 */
	public function setCategoryBlockDelimiterWidth($cat_block_delimiter_width) {
		$this->cat_block_delimiter_width = $cat_block_delimiter_width;
	}

	/**
	 * Add a category defined by it's score and title.
	 *
	 * @param int 		$score
	 * @param string 	$title
	 */
	public function addCategory($score, $title) {
		$this->categories[] = array('score'=>$score, 'title'=>$title);
	}

	/**
	 * Define the legend-related parameters.
	 *
	 * @param string 	$insufficient_label
	 * @param int 		$sufficient
	 * @param string 	$sufficient_label
	 * @param int 		$excellent
	 * @param string 	$excellent_label
	 * @param int 		$max_score
	 */
	public function setLegendParams(
		$insufficient_label, 
		$sufficient, $sufficient_label, 
		$excellent, $excellent_label, 
		$max_score) {

		if($max_score <= 0 || $sufficient <= 0 || $excellent <= 0) {
			throw new \ilException('legend parameters must be > 0');
		}

		if($excellent < $sufficient || $max_score < $excellent) {
			throw new \ilException('invalid legend parameter configuration');
		}

		$this->insufficient_label = $insufficient_label;
		$this->sufficient_label = $sufficient_label;
		$this->excellent_label = $excellent_label;

		$this->sufficient = $sufficient;
		$this->excellent = $excellent;
		$this->max_score = $max_score;
	}

	/**
	 * Return a svg-markup string corresponding to given parameters.
	 */
	public function render() {

		$this->tpl->setVariable('INNER_PADDING_LEFT',(self::MAX_WIDTH - $this->inner_width)/2);
		$this->tpl->setVariable('INNER_PADDING_TOP',$this->padding_top);

		$this->tpl = $this->renderLegend($this->tpl);

		$graph_position = $this->legend_position_vertical + $this->legend_bar_position + $this->graph_legend_distance;
		$this->tpl->setVariable('GRAPH_POSITION_V',$graph_position);

		$category_total_height = 0;

		$category_block_height = 2*$this->graph_block_padding + $this->cat_graph_row_height;
		foreach ($this->categories as $value) {
			$this->tpl = $this->renderCategory($this->tpl,$value['title'],$value['score'], $category_total_height);
			$category_total_height += $category_block_height;
		}

		$this->tpl->setVariable('HEIGHT_TOTAL',
			$this->padding_top + $graph_position + $category_total_height + $this->padding_bottom);
		return $this->tpl->get();
	}

	/**
	 * @param \ilTemplate 	$tpl
	 */
	protected function renderLegend(\ilTemplate $tpl) {
		$legend_delimiter_width = $this->legend_delimiter_width;

		$sufficient = $this->sufficient;
		$excellent = $this->excellent;
		$max_score = $this->max_score;

		$inner_width = $this->inner_width;

		$insufficient_width = $sufficient * $inner_width / $max_score - $legend_delimiter_width;
		$sufficient_position = $insufficient_width + $legend_delimiter_width;
		$sufficient_width = max(($excellent - $sufficient) * $inner_width / $max_score - $legend_delimiter_width,0);
		$excellent_position = $sufficient_position + $sufficient_width + $legend_delimiter_width;
		$excellent_width = ($max_score - $excellent) * $inner_width / $max_score;

		$insufficient_label_position = $insufficient_width / 2;
		$sufficient_label_position = $sufficient_position + $sufficient_width / 2;
		$excellent_label_position = $excellent_position + $excellent_width / 2;


		$tpl->setVariable('LEGEND_TEXT_INSUFFICIENT',$this->insufficient_label);
		$tpl->setVariable('LEGEND_TEXT_SUFFICIENT',$this->sufficient_label);
		$tpl->setVariable('LEGEND_TEXT_EXCELLENT',$this->excellent_label);

		$tpl->setVariable('LEGEND_TEXT_POS_H_INSUFFICIENT',$insufficient_label_position);
		$tpl->setVariable('LEGEND_TEXT_POS_H_SUFFICIENT',$sufficient_label_position);
		$tpl->setVariable('LEGEND_TEXT_POS_H_EXCELLENT',$excellent_label_position);

		$tpl->setVariable('LEGEND_WIDTH_INSUFFICIENT',$insufficient_width);
		$tpl->setVariable('LEGEND_WIDTH_SUFFICIENT',$sufficient_width);
		$tpl->setVariable('LEGEND_WIDTH_EXCELLENT',$excellent_width);

		$tpl->setVariable('LEGEND_POS_SUFFICIENT',$sufficient_position);
		$tpl->setVariable('LEGEND_POS_EXCELLENT',$excellent_position);
		$tpl->setVariable('LEGEND_POS_V', $this->legend_bar_position);
		$tpl->setVariable('LEGEND_HEIGHT',$this->legend_bar_height);

		$tpl->setVariable('LEGEND_POS_TOP',$this->legend_position_vertical);


		return $tpl;
	}

	/**
	 * @param \ilTemplate 	$tpl
	 * @param string 		$category_title
	 * @param int 			$category_score
	 * @param int 			$category_position
	 */
	protected function renderCategory(\ilTemplate $tpl, $category_title, $category_score, $category_position) {
		if($category_score >= $this->excellent) {
			$tpl->setCurrentBlock('graph_row_excellent');
		} elseif($category_score >= $this->sufficient) {
			$tpl->setCurrentBlock('graph_row_sufficient');
		} else {
			$tpl->setCurrentBlock('graph_row_insufficient');
		}

		$tpl->setVariable('CATEGORY_POSITION_V',$category_position);
		$tpl->setVariable('GRAPH_ROW_HEIGHT',$this->cat_graph_row_height);
		$tpl->setVariable('CATEGORY',$category_title);
		$tpl->setVariable('GRAPH_BLOCK_PADDING', $this->graph_block_padding);
		$delimiter_width =$this->cat_block_delimiter_width;
		$inner_width = $this->inner_width;

		$score_width = $inner_width * $category_score / $this->max_score;
		$fill_width = max($inner_width - $score_width - $delimiter_width,0);
		$fill_position = $score_width + $delimiter_width;

		$tpl->setVariable('GRAPH_WIDTH_ACHIEVED',$score_width);
		$tpl->setVariable('GRAPH_POS_FILL',$fill_position);
		$tpl->setVariable('GRAPH_WIDTH_FILL',$fill_width);
		$tpl->parseCurrentBlock();
		return $tpl;
	}

}
