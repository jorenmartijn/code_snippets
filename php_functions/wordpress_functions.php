<?php
/**
 * Deeplink builder 
 * @param $base_url string The base url, like site_url or an ACF Page Link
 * @param $params array Named array of parameter names and values, null values don't get merged.
 * @return $deeplink The resulting deeplink
 */
function build_deeplink($base_url = null, $params = []){
  $base_url = (!is_null($base_url))? $base_url: site_url(); // Either site_url when null or the passed value
  $query_string = http_build_query( $params, "&amp;");      // merge the params into a query_string
  $deeplink = $base_url."?".$query_string;                  // Assumes trailing slash on base_url
  return $deeplink;
}
?>