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

/**
* Creates an human-readable overview of active filters.
* Function was made to be able pass into the deeplink function.
* ** Make this function more efficient in the future, it could be improved I think **
* @param $active_filters Array with the active filters
* @param $all_filters Array with all filters
* @return Array or null
**/
function filter_overview($active_filters, $all_filters){
  $overview = []; // Resulting array
  if($active_filters): foreach($active_filters as $key => $value):
    $taxonomy = $all_filters[$key]['taxonomy']; // store the current taxonomy
    $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]); // get taxonomy terms for current filter
    $overview[$key] = $all_filters[$key]['name'].":"; // assign the active filter's name to the result
    if($terms): $i = 0;foreach($terms as $term):
      if(in_array($term->slug, $value)): // check if the slug is in the active filters value array
        $overview[$term->slug."_".$i] = "- ".$term->name; // then assign the active terms to the result
       $i++;endif;
   endforeach;endif;
  endforeach;
    return array_merge($overview); // return the merged array (maybe )
  else: 
    return null;
  endif;
}
?>