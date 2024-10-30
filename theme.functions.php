<?php
// Set up custom taxonomies for Map Navigator. You
// can copy-and-paste the code below to your theme's functions.php file.

add_action('init', 'map_navigator_taxonomies', 0);

function map_navigator_taxonomies() 
{
    register_taxonomy('maps', 'post', array(
        'hierarchical' => true,
        'label' => 'Maps',
    ));
}
?>