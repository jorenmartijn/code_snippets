<?php
/**
*   Modified code based on: https://www.mightyminnow.com/2015/10/dynamically-populate-gravity-forms-drop-down-fields/
*   This code can be used to fill a dropdown field in Gravity Forms with the contents of a custom post type.
*   Usage: enter {custom_post_type_name}-dropdown into the CSS field of the dropdown box. Other CSS classes can still be used.
**/
// filter the Gravity Forms button type
add_filter( 'gform_submit_button', 'form_submit_button', 10, 2 );
function form_submit_button( $button, $form ) {
    return "<button class='btn btn-default btn-default-alt' id='gform_submit_button_{$form['id']}'><span>Versturen</span></button>";
}

add_filter('gform_pre_render', 'populate_post_types');
//Note: when changing drop down values, we also need to use the gform_pre_validation so that the new values are available when validating the field.
add_filter( 'gform_pre_validation', 'populate_post_types' );

//Note: when changing drop down values, we also need to use the gform_admin_pre_render so that the right values are displayed when editing the entry.
add_filter( 'gform_admin_pre_render', 'populate_post_types' );

//Note: this will allow for the labels to be used during the submission process in case values are enabled
add_filter( 'gform_pre_submission_filter', 'populate_post_types' );

function populate_post_types( $form ) {

    //if ( $form['title'] != "Movies" ) return $form;
    foreach ( $form['fields'] as &$field ) {
    	$cssClasses = explode(' ', $field->cssClass); //split all the available css classes
    	$post_type = null; // set post type variable

        if ( $field->type != 'select' || strpos( $field->cssClass, '-dropdown' ) === false ) {
            continue;
        }
    	for($i = 0; $i < count($cssClasses);$i++){
    		if(strpos($cssClasses[$i], '-dropdown')){ // check if the string contains 'dropdown'
    			$post_type = explode('-', $cssClasses[$i])[0]; // fill the post type variable with the first item of the exploded array
    			continue; // no need to continue with the loop.
    		}
    	}
        // you can add additional parameters here to alter the posts that are retrieved
        // more info: http://codex.wordpress.org/Template_Tags/get_posts
        // generalised the below code by making $post_type dynamic
        $post_ids = get_posts('fields=ids&posts_per_page=-1&post_status=publish&post_type='.$post_type.'&order=asc&orderby=title');

        // update 'Not listed Here' to whatever you'd like the instructive option to be
        $choices = array(array('text' => 'Geen keuze gemaakt', 'value' => 0 ));
        $field->allowsPrepopulate = true;
        $field->inputName = $post_type."_id";  // this is the uri parameter it looks for when pre-populating the dropdown box
        foreach ( $post_ids as $post_id ) {
            $choices[] = array( 'text' => get_the_title( $post_id ), 'value' => get_the_title( $post_id ), 'isSelected' => false );
        }
        $field['choices'] = $choices;
        //var_dump($field);
    }
    return $form;
}

?>