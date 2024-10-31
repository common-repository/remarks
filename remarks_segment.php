<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of remarks_segment
 *
 * @author john
 */
class RemarksSegment {

	private $segment_type;
	protected $segment_data;

	protected function __construct( $classType ) {
		$this->segment_type = $classType;
		$this->segment_data = array();
	}

	protected static function reorder( $a, $b ) {
		if ( $a['count'] == $b['count'] ) {
			return 0;
		}
		return ($a['count'] > $b['count']) ? -1 : 1;
	}

	protected function draw_bars() {
		echo "<svg width='1000' height='500' id='" . $this->segment_type . "_bar' class='startHidden'></svg>
            <script src='http://d3js.org/d3.v3.min.js'></script>
            <script>
InitChart();

function InitChart() {

  var barData = [";

		$segmentDataValues = array_values($this->segment_data);

		foreach ( $segmentDataValues as $key => $category ) {
			echo "{ x: '" . $category['name'] . "', y: " . $category['count'] . "}";
			if ( $key <= count( $segmentDataValues ) ) {
				echo ",";
			}
		}

		echo "];

  var vis = d3.select('#" . $this->segment_type . "_bar'),
    WIDTH = 1000,
    HEIGHT = 500,
    MARGINS = {
      top: 20,
      right: 20,
      bottom: 20,
      left: 50
    },
    xRange = d3.scale.ordinal().rangeRoundBands([MARGINS.left, WIDTH - MARGINS.right], 0.1).domain(barData.map(function (d) {
      return d.x;
    })),


    yRange = d3.scale.linear().range([HEIGHT - MARGINS.top, MARGINS.bottom]).domain([0,
      d3.max(barData, function (d) {
        return d.y;
      })
    ]),

    xAxis = d3.svg.axis()
      .scale(xRange)
      .tickSize(5)
      .tickSubdivide(true),

    yAxis = d3.svg.axis()
      .scale(yRange)
      .tickSize(5)
      .orient('left')
      .tickSubdivide(true);

  var color = d3.scale.category20b();

  vis.append('svg:g')
    .attr('class', 'x axis')
    .attr('transform', 'translate(0,' + (HEIGHT - MARGINS.bottom) + ')')
    .call(xAxis);

  vis.append('svg:g')
    .attr('class', 'y axis')
    .attr('transform', 'translate(' + (MARGINS.left) + ',0)')
    .call(yAxis);

  vis.selectAll('rect')
    .data(barData)
    .enter()
    .append('rect')
    .attr('x', function (d) {
      return xRange(d.x);
    })
    .attr('y', function (d) {
      return yRange(d.y);
    })
    .attr('width', xRange.rangeBand())
    .attr('height', function (d) {
      return ((HEIGHT - MARGINS.bottom) - yRange(d.y));
    }).attr('fill', '#393b79');

}
        </script>";
	}

	protected function draw_pie() {
		echo '<div id="' . $this->segment_type . '_pie" class="startHidden" >
			<div id="' . $this->segment_type . '_pie_chart" class="pie_chart" width="500" height="500"></div>
			<div id="' . $this->segment_type . '_pie_legend" class="pie_legend" width="500" height="500"></div>
			</div>
            <script src="http://wordpress/d3.min.js"></script><!-- TODO FIX SCRIPT LOCATION -->
            <script>

            (function(d3) {
            "use strict";

            var dataset = [';

		$segmentDataValues = array_values($this->segment_data);

		foreach ( $segmentDataValues as $key => $category ) {
			echo "{ label: '" . $category['name'] . "', count: " . $category['count'] . "}";
			if ( $key <= count( $segmentDataValues ) ) {
				echo ",";
			}
		}

		echo '];

            var width = 360;
            var height = 360;
            var radius = Math.min(width, height) / 2;
            var donutWidth = 75;                            // NEW

            var color = d3.scale.category20b();

            var svg = d3.select("#' . $this->segment_type . '_pie_chart")
              .append("svg")
              .attr("width", width)
              .attr("height", height)
              .append("g")
              .attr("transform", "translate(" + (width / 2) +
                "," + (height / 2) + ")");

            var arc = d3.svg.arc()
              .innerRadius(radius - donutWidth)             // NEW
              .outerRadius(radius);

            var pie = d3.layout.pie()
              .value(function(d) { return d.count; })
              .sort(null);

            var path = svg.selectAll("path")
              .data(pie(dataset))
              .enter()
              .append("path")
            .attr("d", arc)
              .attr("fill", function(d, i) {
                return color(d.data.label);
              });

			// Create SVG element
			var legend_svg = d3.select("#' . $this->segment_type . '_pie_legend")
				.attr("width", "500")
				.attr("height", "500");

			legend_svg.selectAll("g").data(dataset)
			  .enter()
			  .append("g")
			  .each(function(data, index) {

				if (data.count == 0) {
				  jQuery(this).remove();
				  return;
				}

				var g = d3.select(this);

				g.append("div")
				  .attr("x", 0)
				  .attr("y", index * 25)
				  .attr("width", 10)
				  .attr("height", 10)
				  .attr("display", "inline-block")
				  .style("background-color", color(data.label));

				g.append("li")
				 // .attr("x", 15)
				 // .attr("y", index * 25 + 8)
				  .attr("height",30)
				  .attr("width",200)
				  .html("<span style=\'color: " + color(data.label) + "\'>" + data.label + "</span><br/>");
			  });

            })(window.d3);
        </script>';
	}

	public function get_highest_stat() {
		return $this->segment_data[0]; // has been reordered so that highest is at the top.
	}

	public function render() {
		echo "<div id='" . $this->segment_type . "_div' class='startHidden'>";
		RemarksInterface::render_navigation_options( $this->segment_type );
		echo "<br/>";
		$this->render_matrix();
		echo '<br/>';
		$this->draw_bars();
		echo '<br/>';
		$this->draw_pie();
		echo '<br/>';
		echo '</div>';
	}

	public static function remarks_handle_biggest_source( &$biggestName, &$biggestNumber, $candidateName, $candidateNumber ) {
		if ( $biggestNumber < $candidateNumber ) {
			$biggestName = $candidateName;
			$biggestNumber = $candidateNumber;
		} elseif ( $biggestNumber == $candidateNumber ) {
			$biggestName = $biggestName . ', ' . $candidateName;
		}
	}

}
