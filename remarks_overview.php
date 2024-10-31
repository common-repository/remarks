<?php

class RemarksOverview {

  private $total_comments;
  private $posts_highest_stat;
  private $category_highest_stat;
  private $author_highest_stat;
  private $countries_highest_stat;

  function __construct($total_comments, $posts_highest_stat, $category_highest_stat, $author_highest_stat, $countries_highest_stat) {
    $this->total_comments = $total_comments;
    $this->posts_highest_stat = $posts_highest_stat;
    $this->category_highest_stat = $category_highest_stat;
    $this->author_highest_stat = $author_highest_stat;
    $this->countries_highest_stat = $countries_highest_stat;
  }

  function render() {
    echo "<div id='overview_div'>";

    if($this->total_comments == 0){
      echo "You haven't approved any comments yet! Please check back when some have been approved.<br/></div>";  
      return;
    }
    echo $this->total_comments . " approved comments in total.<br/>";

    echo "<br/>
      <h5>Most commented Post:</h5>
      <br/>";

    echo $this->posts_highest_stat['name'] . " (" . $this->posts_highest_stat['count'] . ")";

    echo "<br/>";
    echo "<br/>";

    echo "<h5>Most commented Category:</h5>";
    echo "<br/>";
    echo $this->category_highest_stat['name'] . " (".$this->category_highest_stat['count'] .")";

    echo "<br/>";
    echo "<br/>";
    echo "<h5>Most commented Author:</h5>";
    echo "<br/>";

    echo $this->author_highest_stat['name'] . " (".$this->author_highest_stat['count'] . ")";

    echo "<br/>";
    echo "<br/>";


    echo "<h5>Origin of most comments:</h5>";
    echo "<br/>";

    echo $this->countries_highest_stat['label'] . " (" . $this->countries_highest_stat['count'] . ")";

    echo "<br/>";
    echo "<br/>";
    echo "</div>";
  }
}