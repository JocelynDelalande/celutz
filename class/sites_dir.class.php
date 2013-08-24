<?php
class sites_dir {
  private $base_dir;

  public function __construct($dir) {
    $this->base_dir = $dir;
  }
  
  public function get_sites() {
    $dir_fd = @opendir($this->base_dir);

    $point_list = array();
    while (false !== ($point_dir = readdir($dir_fd))) {
      $pt = new site_point($this->base_dir.'/'.$point_dir);
      if ($pt->get_prefix() !== false) $point_list[] = $pt;
    }
    return $point_list;
  }
  
  public function get_dir() {
    return $this->base_dir;
  }
}
