<?php

class RemarksGlobe { // For common functionality, although there isn't much.

	const MAP_MAX_ZOOM = 8;

	private $longlats;
	private $countries;
	private $countries_top;

// TODO add common city list
// TODO add google API fallthrough

	public function __construct() {
		$this->longlats = array();
		$this->countries = array();
		$this->countries_top = array('label' => '', 'count' => 0);

		self::initialise_remarks_table();
		$this->populate_city_by_comments();
	}

	private function add_coordinates( $long, $lat ) {

		if ( array_key_exists( $long, $this->longlats ) ) {
			if ( array_key_exists( $lat, $this->longlats[$long] ) ) {
				$this->longlats[$long][$lat] ++;
				RETURN -1;
			}
		}

		// fallthrough

		$this->longlats[$long] = array($lat => 1);
	}

	private function strip_country( $raw_country ) {
		return ucwords( strtolower( substr( $raw_country, 0, strpos( $raw_country, " (" ) ) ) );
	}

	private function Geolocation_InsertCommentLocation( $commentID, $country, $city, $latitude, $longitude ) {
		global $wpdb;

		$sql = "INSERT INTO `" . $wpdb->prefix . "remarks_comments` VALUES ($commentID" . ', \'' . $city . '\', \'' . $country . '\', ' . $latitude . ', ' . $longitude . ')';
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );

		if ( ($latitude != 0.0) || ($longitude != 0.0) ) {
			$this->add_coordinates( $longitude, $latitude );
		}
	}

	private function Geolocation_RegisterCountry( $country ) {

		// 3. see if that city and country exists
		if ( array_key_exists( $country, $this->countries ) ) {
			// 3b. otherwise increase that city and country's value by 1
			$this->countries[$country] ++;
		} else {
			// 3a. if that city and country doesn't exist, create a key in an array for that city and country, and set its number to 1
			$this->countries[$country] = 1;
		}
	}

	private function IPtoLocationEntry_HostIP( $commentIndex, $eachIP ) {

		// thanks Dan Grossman of Stack Overflow!
		$response = file( "http://api.hostip.info/get_html.php?ip=$eachIP&position=true" );

		IF ( strpos( $response[0], "(XX)" ) != FALSE ) {
			RETURN -1;
		}

		foreach ( $response as $line ) {
			$line = trim( $line );
			if ( !empty( $line ) ) {
				$parts = explode( ': ', $line );
				if ( array_key_exists( 1, $parts ) ) {
					$result[$parts[0]] = $parts[1];
				}
			}
		}

		$strippedCountry = $this->strip_country( $result['Country'] );

		$this->Geolocation_RegisterCountry( $strippedCountry );

		// 5. add any longlats to the longlatlist
		if ( array_key_exists( 'Longitude', $result ) && array_key_exists( 'Latitude', $result ) ) { // 5a. use the ones from hpinfo
			$this->Geolocation_InsertCommentLocation( $commentIndex, $strippedCountry, $result['City'], $result['Latitude'], $result['Longitude'] );
		} elseif ( array_key_exists( 'City', $result ) && array_key_exists( 'Country', $result ) ) {
			$this->Geolocation_InsertCommentLocation( $commentIndex, $strippedCountry, $result['City'], 0.0, 0.0 );
		} elseif ( array_key_exists( 'Country', $result ) ) {
			$this->Geolocation_InsertCommentLocation( $commentIndex, $strippedCountry, "?", 0.0, 0.0 );
		}
	}

	private function IPtoLocationEntry_FreeGeoIp( $commentIndex, $eachIP ) {

		$response_raw = file( "http://freegeoip.net/csv/$eachIP" );

		$responseArray = str_getcsv( $response_raw['0'] );
		$country = $responseArray['2'];
		$city = $responseArray['5'];
		$latitude = $responseArray['8'];
		$longitude = $responseArray['9'];

		$this->Geolocation_RegisterCountry( $country );

		$this->Geolocation_InsertCommentLocation( $commentIndex, $country, $city, $latitude, $longitude );
	}

	public static function on_post_deletion( $commentID ) {
		global $wpdb;

		$sql = "DELETE
			FROM       `" . $wpdb->prefix . "remarks_comments`
			WHERE      comment_ID = '$commentID'";

		$wpdb->query( $sql );
	}

	public static function on_post_creation( $commentID ) {
		global $wpdb;

		$sql = "SELECT  comment_author_IP
			FROM       `$wpdb->comments`
			WHERE      comment_ID = '$commentID'";

		$rawIP = $wpdb->get_results( $sql, ARRAY_A );

		IPtoLocationEntry_FreeGeoIp( $commentID, $rawIP[0]['comment_author_IP'] );
	}

	private function update_table_records() {

		global $wpdb;

		$sql = "SELECT     a.comment_ID, a.comment_author_IP
	    FROM       `$wpdb->comments` AS a
	    WHERE      NOT EXISTS (SELECT * FROM `" . $wpdb->prefix . "remarks_comments` AS b WHERE b.comment_ID = a.comment_ID)  AND a.comment_approved='1'";

		$uninterpretedComments = $wpdb->get_results( $sql, ARRAY_A );

		foreach ( $uninterpretedComments as $eachComment ) {
			/* IPtoLocationEntry_HostIP($eachComment['comment_ID'], $eachComment['comment_author_IP']); */
			$this->IPtoLocationEntry_FreeGeoIp( $eachComment['comment_ID'], $eachComment['comment_author_IP'] );
		}
	}

	private function populate_city_by_comments() {
		global $wpdb;

		// 0. retrieve the data
		$retrieveComments = "SELECT * FROM `" . $wpdb->prefix . "remarks_comments` WHERE 1";
		$retrieveCountry = "SELECT COUNTRY, COUNT(COUNTRY) AS COUNT FROM `" . $wpdb->prefix . "remarks_comments` WHERE COUNTRY != '' GROUP BY COUNTRY";

		// 1. iterate through each comment
		$commentDetails = $wpdb->get_results( $retrieveComments, ARRAY_A );
		$countryDetails = $wpdb->get_results( $retrieveCountry, ARRAY_A );

		// 2. for each comment, divide the IP address into the city and country
		foreach ( $countryDetails as $eachCountry ) {
			$this->countries[$eachCountry['COUNTRY']] = $eachCountry['COUNT'];
			RemarksSegment::remarks_handle_biggest_source( $this->countries_top['label'], $this->countries_top['count'], $eachCountry['COUNTRY'], $eachCountry['COUNT'] );
		}

		foreach ( $commentDetails as $eachComment ) {
			if ( ($eachComment['longitude'] > 0 || $eachComment['longitude'] < 0) && ($eachComment['latitude'] > 0 || $eachComment['latitude'] < 0) ) {
				$this->add_coordinates( $eachComment['longitude'], $eachComment['latitude'] );
			}
		}

		$this->update_table_records();
		// 4. order by count
		arsort( $this->countries );
	}

	function render_geolocation_comments_table() {
		// draw a table of each city by the number of comments it has
		echo "<table id='geolocate_table'>\n";
		echo "\t<tr><td><strong>Location</strong></td><td><strong>Number of Comments</strong></td></tr>\n";
		foreach ( $this->countries as $countryKey => $eachCountry ) {
			echo "\t<tr><td>$countryKey</td><td align='center'>$eachCountry</td></tr>\n";
		}
		echo "\n</table>\n";
	}

	function render_map() {
		echo "<div id='geolocate_map'></div>";

		echo "<script>
			var loadMap = function() {

				// Add map on a timeout: otherwise tiles don't render.
				setTimeout(function(){
					var map = L.map('geolocate_map').setView([0.0, 0.0], 1);

					L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						  attribution: 'Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Imagery © <a href=\"http://mapbox.com\">Mapbox</a>',
						  maxZoom: " . self::MAP_MAX_ZOOM . ",
						  id: 'mapbox.streets'
					}).addTo(map);";

					// Add markers.
					foreach ($this->longlats as $eachLongitude => $eachLongitudeArray) {
						foreach ($eachLongitudeArray as $eachLatitude => $commentsCount) {
							if ($eachLongitude == 0.0 AND $eachLatitude == 0.0) {
								continue;
							}

							$popup_string = $commentsCount . ($commentsCount == 1 ? ' comment.' : ' comments.');

							echo "marker = new L.marker([" . $eachLatitude . "," . $eachLongitude . "])
								.bindPopup('$popup_string')
								.addTo(map);";
						}
					}

				echo "},1);

				jQuery('#geolocate_button').unbind('mouseover', loadMap);
			};

			jQuery('#geolocate_button').mouseover(loadMap);
		</script>";
	}

	public static function initialise_remarks_table() {
		global $wpdb;

		// if the table doesn't exist, create it

		$table_name = $wpdb->prefix . "remarks_comments";

		$sql = "CREATE TABLE " . $table_name . " (
			comment_id mediumint(9) NOT NULL,
			city text NOT NULL,
			country text NOT NULL,
			latitude decimal(10,6) NOT NULL,
			longitude decimal(10,6) NOT NULL,
			UNIQUE KEY comment_id (comment_id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta( $sql );
	}

	public function render() {
		echo "<div id='geolocate_div' class='startHidden'>
		<div id='geolocate_table_div'>";
		$this->render_geolocation_comments_table();
		echo "</div>
		<div id='geolocate_map_div'>";
		$this->render_map();
		echo "</div><br/><br/>
		<em>Geolocation powered by <a href='http://www.freegeoip.net/'>FreeGeoIP</a>.</em><br/>
		<em>Map powered by <a href='https://leafletjs.com/'>Leaflet</a>.</em><br/>
		<em>Unfortunately, the above map may be missing the locations of some of your comments. This is because sometimes it's impossible to translate the IP address into a geographic location.</em>
		</div>";
	}

	public function get_highest_stat() {
		return $this->countries_top; // has been reordered so that highest is at the top.
	}

	public static function setHeaders() {
		wp_register_style( 'leaflet_css', 'https://unpkg.com/leaflet@1.1.0/dist/leaflet.css', false, '1.0.20', 'all' );
		wp_enqueue_style( 'leaflet_css' );

		wp_register_script( 'leaflet_js', 'https://unpkg.com/leaflet@1.1.0/dist/leaflet.js', array('jquery'), '2.5.9' );
		wp_enqueue_script( 'leaflet_js' );
	}

}