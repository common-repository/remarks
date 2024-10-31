<?php

class RemarksInterface {

	private $buttons_list;
	private $total_comments;

	public function __construct( $remarks_total_comments ) {
		$this->buttons_list = array();
		$this->total_comments = $remarks_total_comments;
		if ( $remarks_total_comments > 0 ) {
			$this->populate_buttons_list();
		} else {
			$this->buttons_list[] = $this->add_button_entry( 'overview', '<h4>Overview</h4>', 3, false, true );
			$this->buttons_list[] = $this->add_button_entry( 'about', '<h4>About</h4>', 3, false );
		}
	}

	private function populate_buttons_list() {

		$this->buttons_list[] = $this->add_button_entry( 'overview', '<h4>Overview</h4>', 0, false, true );
		$this->buttons_list[] = $this->add_button_entry( 'about', '<h4>About</h4>', 0, false );
		$this->buttons_list[] = $this->add_button_entry( 'post', '<h4>Post</h4>', 1, true );
		$this->buttons_list[] = $this->add_button_entry( 'category', '<h4>Category</h4>', 1, true );
		$this->buttons_list[] = $this->add_button_entry( 'author', '<h4>Post Author</h4>', 1, true );
		$this->buttons_list[] = $this->add_button_entry( 'geolocate', '<h4>Geolocation</h4>', 1, true );
	}

	private function make_all_buttons() {
		$current_line = 0;
		echo "<div id='nav_row_" . $current_line . "'>";

		foreach ( $this->buttons_list as $button ) {
			if ( $current_line < $button['line'] && $this->total_comments > 0 ) {
				echo "</div>";
				$current_line++;
				echo "<div id='nav_row_" . $current_line . "'>";
				echo "<br/>\n";
			}
			$this->make_button( $button );
		}
		echo "</div>";
	}

	/* POPULATE */

	private function add_button_entry( $tag, $label, $lineNumber, $bPrintPreamble, $startEnabled = false ) {
		return array('tag' => $tag, 'id' => $tag . '_button', 'div' => $tag . '_div', 'label' => $label, 'line' => $lineNumber, 'printPreamble' => $bPrintPreamble, 'startEnabled' => $startEnabled);
	}

	/* PRINT */

	private function make_button( $button ) {
		echo "<div ";

		if ( $button['startEnabled'] == true ) {
			echo "class='remarks_button remarks_button_selected " . $button['tag'] . "_bg_colour'";
		} else {
			echo "class='remarks_button' ";
		}

		echo "id='" . $button['id'] . "'>\n";
		if ( $button['printPreamble'] == true ) {
			echo "\t<div class='preamble'>\n";
			echo "Show Comments by";
			echo "\t</div>\n";
		}
		echo "\t<div class='title'>\n";
		echo $button['label'];
		echo "\n\t</div>\n";
		echo "</div>\n";
	}

	public static function render_navigation_options( $section ) {
		echo "\t<nav id='$section" . "_options'>\n";
		echo "\t\t<div id='$section" . "_options_table' class='remarks_subbutton " . $section . "_bg_colour remarks_subbutton_selected'>Table</div>\n";
		echo "\t\t<div id='$section" . "_options_bar' class='remarks_subbutton'>Bar Chart</div>\n";
		echo "\t\t<div id='$section" . "_options_pie' class='remarks_subbutton'>Pie Chart</div>\n";
		echo "\t</nav><!-- end $section" . "_options -->\n";
	}

	public function render_interface() {
		if ( $this->total_comments > 0 ) {
			echo "<div id='main_nav_with_comments'><br/>";
		} else {
			echo "<div id='main_nav_no_comments'><br/>";
		}
		$this->make_all_buttons();
		echo "</div><br/>";
	}

}