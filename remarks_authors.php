<?php

class RemarksAuthors extends RemarksSegment {

	private $remarks_posts;

	public function __construct( $remarks_posts ) {
		$this->remarks_posts = $remarks_posts;
		parent::__construct( 'author' );
		$this->populate_author_matrix();
	}

	private function populate_author_matrix_row( $author_id, $author_name ) {
		global $wpdb;

		$retrieve_posts = "SELECT ID FROM $wpdb->posts WHERE post_author = $author_id AND post_status='publish'";
		$authors = $wpdb->get_results( $retrieve_posts, ARRAY_A );

		$num_posts = 0;
		$num_comments = 0;

		foreach ( $authors as $post ) {
			$num_posts += 1;
			$num_comments += $this->remarks_posts[$post['ID']]['count'];
		    //echo print_r($this->remarks_posts, TRUE) . "<br/><br/>";
		}

		$this->segment_data[] = array('num_posts' => $num_posts, 'count' => $num_comments, 'name' => $author_name, 'id' => $author_id);
	}

	public function populate_author_matrix() {
		global $wpdb;

		$retrieveAuthors = "SELECT ID, display_name FROM $wpdb->users WHERE 1";
		$authors = $wpdb->get_results( $retrieveAuthors, ARRAY_A );

		foreach ( $authors as $each_author ) {
			$this->populate_author_matrix_row( $each_author['ID'], $each_author['display_name'] );
		}
//		die();
		uasort( $this->segment_data, 'self::reorder' ); // Maintain array association.
	}

	private function render_author_matrix_row( $author_index ) {
		echo "<tr><td><a href = '" . get_bloginfo( 'url' ) . "/?author=" . $this->segment_data[$author_index]['id'] . "'>" . $this->segment_data[$author_index]['name'] . "</a></td><td>" . $this->segment_data[$author_index]['count'] . " comments</td><td>" . $this->segment_data[$author_index]['num_posts'] . " posts</td></tr>\n";
	}

	public function render_matrix() {
		echo "<div id='author_table'>\n\n";
		echo "<table id='author_table' class='centralise'>";
		echo "<tr><td><strong>Post Author</strong></td><td><strong>Number of Comments</strong></td><td><strong>Number of Posts</strong></td></tr>\n";
		foreach ( $this->segment_data as $author_index => $each_author ) {
			$this->render_author_matrix_row( $author_index );
		}
		echo "</table>\n\n";
		echo "</div>\n\n";
	}
}
