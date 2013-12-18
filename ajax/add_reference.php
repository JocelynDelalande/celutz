<?php
require_once('../class/FormValidator.class.php');
require_once('../class/RefPoint.class.php');
require_once('../class/site_point.class.php');

$fields_spec = array('x'         => array('required', 'numeric', 'positive'),
                     'y'         => array('required', 'numeric', 'positive'),
                     'panorama'  => array('required'),
                     'ref_point' => array('required'));


$validator = new FormValidator($fields_spec);
if ($validator->validate($_REQUEST)) {
  $vals = $validator->sane_values();

  // temp test code
  echo '<h1>pano !</h1>';
  $pano = site_point::get($vals['panorama']);
  var_dump($pano->get_params());

  echo '<h1>ref point !</h1>';
  $ref_point_name = urldecode($vals['ref_point']);
  var_dump(RefPoint::get($ref_point_name));

 } else {
   echo var_dump($validator->errors());
 }

// Test url : clear ;curl 'http://localhost/~jocelyn/panorama/ajax/add_reference.php?x=42&y=42&panorama=pano_couttolenc_bords_jointifs&ref_point=%C3%89glise%20saint-jacques'
?>
