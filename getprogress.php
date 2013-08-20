<?php
header('Expires: Tue, 08 Oct 1991 00:00:00 GMT');
header('Cache-Control: no-cache, must-revalidate');

if(isset($_GET['uid'])){
   $status = apc_fetch('upload_' . $_GET['uid']);
   $url = apc_fetch('link');
   $message = apc_fetch('info');
   echo round($status['current']/$status['total']*100)."#".$url."#".$message;
}
?>