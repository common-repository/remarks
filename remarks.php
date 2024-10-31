<?php

/*
  Plugin Name: Remarks
  Plugin URI: http://www.civifirst.com
  Description: Analyse the number of comments you get by post, category, author, and location. Uses charts and maps!
  Version: 4.2
  Author: CiviFirst John
  Author URI: http://www.civifirst.com
  License: GPL2
 */

/*  Copyright 2013  CiviFirst John  (email : john@civifirst.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

include_once dirname( __FILE__ ) . "/remarks_segment.php";
include_once dirname( __FILE__ ) . "/remarks_posts.php";
include_once dirname( __FILE__ ) . "/remarks_overview.php";
include_once dirname( __FILE__ ) . "/remarks_categories.php";
include_once dirname( __FILE__ ) . "/remarks_authors.php";
include_once dirname( __FILE__ ) . "/remarks_globe.php";
include_once dirname( __FILE__ ) . "/remarks_navigation.php";

register_activation_hook( __FILE__, 'initialise_remarks_table::initialise_remarks_table' );

// TODO: fix up language things below
//if(!load_plugin_textdomain('remarks','/wp-content/languages/'))
//load_plugin_textdomain('remarks','/wp-content/plugins/remarks/mo-po-files/');

add_action( 'admin_menu', 'wrapper' );
add_action( 'wp_set_comment_status', 'comment_changes', 10, 2 );


/* include css and javascript */
// register jquery and style on initialization

wp_register_style( 'remarks_css', plugins_url( '/css/style.css', __FILE__ ), false, '1.0.20', 'all' );
wp_enqueue_style( 'remarks_css' );

wp_register_script( 'remarks_jquery', plugins_url( '/js/functionality.js', __FILE__ ), array('jquery'), '2.5.11' );
wp_enqueue_script( 'remarks_jquery' );

RemarksGlobe::setHeaders();
/* end include css and javascript */

function wrapper() {
	add_comments_page( 'Remarks', 'Remarks', 'manage_options', 'remarks', 'remarks_main' );
}

function comment_changes( $commentID, $status ) {

	global $wpdb;
	$wpdb->remarks_comments = $wpdb->prefix . 'remarks_comments';

	if ( $status === 'spam' || $status === 'trash' || $status === 'hold' ) {
		RemarksGlobe::on_post_deletion( $commentID );
	} elseif ( $status === 'approve' ) {
		RemarksGlobe::on_post_creation( $commentID );
	}
}

function remarks_main() {
	global $wpdb;

	$wpdb->remarks_comments = $wpdb->prefix . 'remarks_comments';

	// Get the total number of approved comments.
	$getApprovedCommentCountQuery = "SELECT count(comment_approved) comments_count FROM $wpdb->comments where comment_approved = '1' group by comment_approved";
	$query_results = $wpdb->get_row( $getApprovedCommentCountQuery, ARRAY_A );
	if ( $query_results != FALSE ) {
		$total_approved_comments = $query_results['comments_count'];
	} else {
		$total_approved_comments = 0;
	}

	// Create the section objects.
	$interfaceSection = new RemarksInterface( $total_approved_comments );
	$postsSection = new RemarksPosts();
	$categoriesSection = new RemarksCategories( $postsSection->get_posts() );
	$authorsSection = new RemarksAuthors( $postsSection->get_posts() );
	$globeSection = new RemarksGlobe();

	$overviewSection = new RemarksOverview(
			$total_approved_comments, 
			$postsSection->get_highest_stat(),
			$categoriesSection->get_highest_stat(),
			$authorsSection->get_highest_stat(),
			$globeSection->get_highest_stat()
	);

	// Start rendering.
	$interfaceSection->render_interface();
	echo "<div id='display'>";
	$overviewSection->render();
	$postsSection->render();
	$categoriesSection->render();
	$authorsSection->render();
	$globeSection->render();

	include dirname( __FILE__ ) . "/remarks_about.html";

	echo "</div><!-- end display div #-->";

	echo "<br/><br/>";
}
