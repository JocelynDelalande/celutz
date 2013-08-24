<?php

abstract class utils {
  static public function init() {
    function __autoload($class) {
      $class_loc = 'class/'.$class.'.class.php';
      if (is_readable($class_loc)) {
	require_once($class_loc);
      }
    }

    function errorToException($code, $msg, $file, $line) {
      throw new Exception($msg);
    }
    set_error_handler('errorToException');
  }

}
