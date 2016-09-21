<?php
/**
 * Enrollment form switcher
 * Make a duplicate of the default form and add extra questions in the duplicate.
 * Then assign that form on the research edit page using the custom field.
 * Option field: global_signup_form
 * Page specific field: signup_form_id 
 */
function enrollment_form(){
  if(isset($_GET['research_id'])){
    // get the page object
    $the_page     = get_page_by_path($_GET['research_id'], OBJECT, 'research');
    // get either the page's signup form or return null
    $signup_form  = (is_object($the_page))? get_field('signup_form_id', $the_page->ID): null;
    // check if we can retrieve the signup form or if we need the global form
    $page_signup_form = ($signup_form !== false && $signup_form !== null)? 
                         $signup_form: get_field('global_signup_form', 'options');
    // enqueue the scripts and set up the form
    gravity_form_enqueue_scripts($page_signup_form['id'], true);
    gravity_form($page_signup_form['id'], false, false, false, null, false, 90, true );
    // handy code for debugging, shows what statement it's in and the id of the form
    //echo "global or page form, if statement: ".$page_signup_form['id'];
  }
  else{
    // get the global signup form
    $page_signup_form = get_field('global_signup_form', 'options');
    // enqueue the scripts and set up the form
    gravity_form_enqueue_scripts($page_signup_form['id'], true);
    gravity_form($page_signup_form['id'], false, false, false, null, false, 90, true );
    // handy code for debugging, shows what statement it's in and the id of the form
    //echo "global form, else statement: ".$page_signup_form['id'];
  }
}
?>