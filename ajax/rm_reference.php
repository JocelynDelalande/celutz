<?php
require_once('../class/FormValidator.class.php');
require_once('../class/RefPoint.class.php');
require_once('../class/site_point.class.php');

$fields_spec = array('panorama'  => array('required'),
                     'ref_point' => array('required'),
);


$validator = new FormValidator($fields_spec);
if ($validator->validate($_REQUEST)) {
  $vals = $validator->sane_values();

  // temp test code
  $pano = site_point::get($vals['panorama']);

  $ref_point_name = urldecode($vals['ref_point']);
  $ref_point = RefPoint::get($ref_point_name);

  $pano->unset_reference($ref_point);
  $pano->save_params();

 } else {
   // Set our response code
   http_response_code(400);
   echo var_dump($validator->errors());
 }
// Test url : clear ;curl 'http://localhost/~jocelyn/panorama/ajax/add_reference.php?x=42&y=42&panorama=pano_couttolenc_bords_jointifs&ref_point=%C3%89glise%20saint-jacques'

?>
