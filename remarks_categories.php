<?php

class RemarksCategories extends RemarksSegment {

	public function __construct( $remarks_post_matrix ) {
		parent::__construct( 'category' );
		$this->populate_category_matrix( $remarks_post_matrix );
	}

	public function populate_category_matrix( $remarks_post_matrix ) {
		global $wpdb;
		$category_count_matrix = array();

		// Get a list of all category ids.
		$get_category_ids_sql = "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE taxonomy='category'";
		$category_ids = $wpdb->get_results( $get_category_ids_sql, ARRAY_A );

		// Initiate their entry in the matrix to have 0.
		foreach ( $category_ids as $category_id ) {
			$category_count_matrix[$category_id['term_taxonomy_id']] = array('num_posts' => 0, 'commentCount' => 0);
		}

		// Get a list of how many posts per category.
		foreach ( $remarks_post_matrix as $remarks_post ) {
			foreach ( $remarks_post['categories'] as $category_index ) {
				if ( array_key_exists( $category_index, $category_count_matrix ) ) {
					$category_count_matrix[$category_index]['num_posts'] ++;
					$category_count_matrix[$category_index]['commentCount'] += $remarks_post['count'];
				}
			}
		}

		// Fill in the segment data.
		foreach ( $category_count_matrix as $category_index => $category ) {
			$get_category_name_sql = "SELECT name FROM $wpdb->terms WHERE term_id = " . $category_index;
			$category_name = $wpdb->get_results( $get_category_name_sql, ARRAY_A );

			$this->segment_data[] = array('name' => $category_name[0]['name'], 'count' => $category['commentCount'], 'id' => $category_index, 'num_posts' => $category['num_posts']);
		}

		usort( $this->segment_data, 'self::reorder' );
	}

	private function render_category_matrix_row( $category_index ) {
		echo "<tr><td><a href='/?cat=" . $this->segment_data[$category_index]['id'] . "'>" . $this->segment_data[$category_index]['name'] .
		"</a></td><td>" . $this->segment_data[$category_index]['count'] . " comments</td><td>" . $this->segment_data[$category_index]['num_posts'] . "</td></tr>\n";
	}

	public function render_matrix() {
		echo "<div id='category_table'>\n\n";
		echo "<table class='centralise'>";
		echo "<tr><td><strong>Post Category</strong></td><td><strong>Number of Comments</strong></td><td><strong>Number of Posts</strong></td></tr>\n";
		foreach ( $this->segment_data as $author_key => $each_author ) {
			$this->render_category_matrix_row( $author_key );
		}
		echo "</table>\n\n";
		echo "</div>\n\n";
	}
}
