<?php

class RemarksPosts extends RemarksSegment {

	const POST_TITLE_MAX_LENGTH = 50;

	public function __construct() {
		parent::__construct( 'post' );
		$this->populate_post_matrix();
	}

	private function categories_links( $array_of_category_indices ) {
		$output_string = "";
		foreach ( $array_of_category_indices as $category_index ) {
			$output_string .= "<a href='" . get_category_link( $category_index ) . "'>" . get_cat_name( $category_index ) . "</a>, ";
		}
		return substr( $output_string, 0, strlen( $output_string ) - 2 );
	}

	private function add_post_matrix_row( $id, $title, $guid, $author_id, $author_name, $num_comments ) {

		$title_length = strlen( $title );

		if ( $title_length >= self::POST_TITLE_MAX_LENGTH ) {
			$title = substr( $title, 0, self::POST_TITLE_MAX_LENGTH ) . '...';
		}

		$this->segment_data[$id] = array('name' => $title, 'guid' => $guid, 'categories' => wp_get_post_categories( $id ), 'author' => $author_id, 'author_name' => $author_name, 'count' => $num_comments);
	}

	private function populate_post_matrix() {
		global $wpdb;

		$get_commented_posts_query = "SELECT $wpdb->posts.ID as post_ID, $wpdb->posts.post_title, $wpdb->posts.post_author, $wpdb->users.display_name AS 'author_name', $wpdb->posts.guid, count($wpdb->comments.comment_ID) AS 'count'
			FROM $wpdb->posts LEFT JOIN $wpdb->comments ON $wpdb->posts.ID=$wpdb->comments.comment_post_id LEFT JOIN $wpdb->users ON $wpdb->posts.post_author = $wpdb->users.ID
			WHERE post_status = 'publish' AND $wpdb->comments.comment_approved='1' AND post_type='post'
			GROUP BY $wpdb->posts.ID
			ORDER BY count($wpdb->comments.comment_ID) DESC";

		$get_uncommented_posts_query = "SELECT $wpdb->posts.ID as post_ID, post_title, guid, post_author, $wpdb->users.display_name AS 'author_name' FROM $wpdb->posts LEFT JOIN $wpdb->users ON $wpdb->posts.post_author = $wpdb->users.ID WHERE post_status = 'publish'  AND post_type='post'";

		//echo "about to call query: $query<br/>";
		$commented_posts = $wpdb->get_results( $get_commented_posts_query, ARRAY_A );

		// TODO produce query of posts with no comments
		if ( $commented_posts != FALSE ) {
			foreach ( $commented_posts as $each_post ) {
				$get_uncommented_posts_query = $get_uncommented_posts_query . " AND $wpdb->posts.ID != " . $each_post['post_ID'];
				$this->add_post_matrix_row( $each_post['post_ID'], $each_post['post_title'], $each_post['guid'], $each_post['post_author'], $each_post['author_name'], $each_post['count'] );
			}
		}

		$uncommented_posts = $wpdb->get_results( $get_uncommented_posts_query, ARRAY_A );
		if ( $uncommented_posts != FALSE ) {
			foreach ( $uncommented_posts as $each_post ) {
				$this->add_post_matrix_row( $each_post['post_ID'], $each_post['post_title'], $each_post['guid'], $each_post['post_author'], $each_post['author_name'], '0' );
			}
		}

		//usort( $this->segment_data, 'self::reorder' );
	}

// populate_post_matrix()

	public function get_highest_stat() {
		// This behaves slightly differently, as currently $this->remarksPosts is indexed by the post ID.
		$unindexed_values = array_values( $this->segment_data ); // Resets the indices to 0, 1, 2 etc.
		return $unindexed_values[0];
	}

	public function get_posts() {
		return $this->segment_data;
	}

	private function render_post_matrix_row( $id ) {
		echo "<tr>\n";
		echo "\t<td><a href='" . $this->segment_data[$id]['guid'] . "' >" . $this->segment_data[$id]['name'] . "</a></td>\n";
		echo "\t<td>" . $this->segment_data[$id]['count'] . " comments</td>\n";
		echo "\t<td>" . $this->categories_links( $this->segment_data[$id]['categories'] ) . "</td>\n";
		echo "\t<td><a href = '" . get_bloginfo( 'url' ) . "/?author=" . $this->segment_data[$id]['author'] . "'>" . $this->segment_data[$id]['author_name'] . "</a></td>\n";
		echo "</tr>\n";
	}

	public function render_matrix() {
		echo "<div id='post_table'>";
		echo "<table>";
		echo "<tr><td><strong>Post Name</strong></td><td><strong>Number of Comments</strong></td><td><strong>Category(s)</strong></td><td><strong>Author</strong></td></tr>\n";
		foreach ( $this->segment_data as $each_postIndex => $each_post ) {
			$this->render_post_matrix_row( $each_postIndex );
		}
		echo "</table>\n\n";
		echo "<br/>";
		echo "</div>";
	}
}
