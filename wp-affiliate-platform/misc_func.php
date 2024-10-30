<?php
include_once('../../../wp-load.php');
include_once('../../../wp-admin/includes/taxonomy.php');
include "states.php";
include "state-city-tags.php";
include "state-slugs.php";
include "state-city-taxonomy.php";

global $wpdb;
define('WP_AFF_CLICKTHROUGH_TABLE', $wpdb->prefix . "affiliates_clickthroughs_tbl");
define('WP_AFF_AFFILIATES_TABLE', $wpdb->prefix . "affiliates_tbl");
define('WP_AFF_SALES_TABLE', $wpdb->prefix . "affiliates_sales_tbl");
define('WP_AFF_PAYOUTS_TABLE', $wpdb->prefix . "affiliates_payouts_tbl");
define('WP_AFF_BANNERS_TABLE', $wpdb->prefix . "affiliates_banners_tbl");

function aff_check_security()
{
	session_start();
	//check for cookies
	if(isset($_COOKIE['user_id'])){
	      $_SESSION['user_id'] = $_COOKIE['user_id'];
	}	
	if (!isset($_SESSION['user_id']))
	{
	   return false;	   
	}
	else
	{
		return true;
	}
}
function page_protect1() {
	session_start();
	//check for cookies
	if(isset($_COOKIE['user_id'])){
	      $_SESSION['user_id'] = $_COOKIE['user_id'];
	   }
	
	if (!isset($_SESSION['user_id']))
	{
	    header("Location: login.php");
	}
}
function aff_redirect($url, $time = 0)
{
  echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$time;URL=$url\">";
  echo "If you are not redirected within a few seconds then please click <a class=leftLink href=$url>".here.'</a>';
}

function add_affiliate_map_marker ($acompany,$astreet,$atown,$astate,$apostcode,$acountry,$affiliate_login_url,$affiliate_image_url, $affiliate_icon)
{
	//strip the town and state of blanks and special chars
	$atown = trim($atown);
	$astate = trim($astate);
	$affiliate_login_url = trim($affiliate_login_url);

	$post_data_array = build_post_data_array ($acompany,$astreet,$atown,$astate,$apostcode,$acountry,$affiliate_login_url,$affiliate_image_url, $affiliate_icon);
	
	$post_id = create_post($post_data_array);
	if ($post_id)
		return 0;
	else 
		return $post_id;
}

function create_post($data) 
{
        $type = $data['nav_post_type'] ? $data['nav_post_type'] : 'post';
        $valid_type = (function_exists('post_type_exists') &&
            post_type_exists($type)) || in_array($type, array('post', 'page'));

        if (!$valid_type) {
            //log['error']["type-{$type}"] = sprintf(
            //    'Unknown post type "%s".', $type);
        }
		
        $new_post = array(
            'post_title'   => convert_chars($data['nav_post_title']),
            'post_content' => wpautop(convert_chars($data['nav_post_post'])),
            'post_status'  => 'publish',
            'post_type'    => $type,
            'post_excerpt' => convert_chars($data['nav_post_excerpt']),   
            'post_name'    => $data['nav_post_slug'],
            'tax_input'    => get_map_taxonomies($data),
            'post_parent'  => $data['nav_post_parent'],
        );

        // pages don't have tags or categories
        if ('page' !== $type) {
            $new_post['tags_input'] = $data['nav_post_tags'];   

            // Setup categories before inserting - this should make insertion
            // faster, but I don't exactly remember why :) Most likely because
            // we don't assign default cat to post when nav_post_categories
            // is not empty.
            $cats = create_or_get_categories($data, $opt_cat);
            $new_post['post_category'] = $cats['post'];
        }

        // create!
        $post_id = wp_insert_post($new_post);
		
		create_custom_fields($post_id, $data);
		
		// Update the post so the maps are created from the custom mappress field map_address that we just added
		$my_post = array();
		$my_post['ID'] = $post_id;
		wp_update_post( $my_post );

        if ('page' !== $type && !$post_id) {
            // cleanup new categories on failure
            foreach ($cats['cleanup'] as $c) {
                wp_delete_term($c, 'category');
            }
        }
        return $post_id;
    }
	
function build_post_data_array ($acompany,$astreet,$atown,$astate,$apostcode,$acountry,$affiliate_login_url,$affiliate_image_url,$affiliate_icon)
{

	  $body_title = sprintf("%s affiliate for %s, %s ",$acompany,$atown,$astate);
	  $body_image = sprintf(" %s <a href='%s'><img src='%s' style='width:100px;height:100px'></a> ",$body_title, $affiliate_login_url, $affiliate_image_url);
	  $post_slug = get_slug_by_state($astate);
	  if (!$post_slug)
	  {
		$post_slug = sprintf("error-state-%s-%s",strtolower($astate),strtolower($atown));
	  }
		
	  $post_tax = get_taxonomy_by_state($astate,$atown);
	  if (!$post_tax)
	  {
		$post_tax = "";
	  }
	
	 $post_title = sprintf("%s, %s Map",$atown,$astate);
	 $post_post = sprintf("<p>%s affiliate for %s, %s </p>
[mappress zoom=5]",$acompany,$atown,$astate);
	 $post_tags = sprintf("%s-city,%s-affiliates",strtolower($astate),strtolower($acompany));
	 $map_address = sprintf("address=\"%s,%s\" title=\"%s\"  iconid=\"%s\"  body=\"%s \"",$atown,$astate,$acompany,$affiliate_icon,$body_image);
	 
	 $post_array = array(
        'nav_post_title'      => $post_title,
        'nav_post_post'       => $post_post,
        'nav_post_type'       => 'post',
        'nav_post_excerpt'    => $body_title,
        'nav_post_tags'       => $post_tags,
        'nav_post_categories' => 'Maps > Continent > Country > Region > State > City, MapNavigator',
        'nav_post_slug'       => $post_slug,
        'nav_post_parent'     => 0,
		'nav_ctax_maps'       => $post_tax,
		'map_address'         => $map_address,
    );
	
	
	return $post_array;
	
}	  

function get_slug_by_state ($astate)
{

	$states_slugs_lower = array_change_key_case($states_slugs_mixed); //convert key sto lower case
	if (!array_key_exists(strtolower($astate),$states_slugs_lower) )
	{
		return 0;
	}
	//print("this is the state keyname:" . strtolower($astate));
	$slug_name = $states_slugs_lower[strtolower($astate)];
	//print("this is the slugname:" . $slug_name);
	return $slug_name;
}

function get_state_city_tag_by_state ($astate)
{
	$states_tags_lower = array_change_key_case($states_tags_mixed); //convert keys to lower case
	if (!array_key_exists(strtolower($astate),$states_tags_lower) )
	{
		return 0;
	}
	//print("this is the state keyname:" . strtolower($astate));
	$state_city_tag = $states_tags_lower[strtolower($astate)];
	//print("this is the slugname:" . $slug_name);
	return $state_city_tag;
}

function get_state_name_by_abbrev ($astate)
{
	$states_abbrev_lower = array_change_key_case($states); //convert keys to lower case
	if (!array_key_exists(strtolower($astate),$states_abbrev_lower) )
	{
		return 0;
	}
	//print("this is the state keyname:" . strtolower($astate));
	$state_name = $states_abbrev_lower[strtolower($astate)];
	//print("this is the slugname:" . $slug_name);
	return $state_name;
}

function get_taxonomy_by_state ($astate, $atown)
{

	$states_taxonomies_lower = array_change_key_case($states_taxonomies_mixed); //convert keys to lower case
	if (!array_key_exists(strtolower($astate),$states_taxonomies_lower) )
	{
		return 0;
	}
	
	else
	{
		$taxonomy = $states_taxonomies_lower[strtolower($astate)];
		$taxonomy = $taxonomy . "
0,City
City," . $atown;
		
	}
	return $taxonomy ;
}

/**
     * Return an array of category ids for a post.
     *
     * @param string  $data nav_post_categories cell contents
     * @param integer $common_parent_id common parent id for all categories
     * @return array category ids
     */
    function create_or_get_categories($data, $common_parent_id) {
        $ids = array(
            'post' => array(),
            'cleanup' => array(),
        );
        $items = array_map('trim', explode(',', $data['nav_post_categories']));
        foreach ($items as $item) {
            if (is_numeric($item)) {
                if (get_category($item) !== null) {
                    $ids['post'][] = $item;
                } else {
                    //log['error'][] = "Category ID {$item} does not exist, skipping.";
                }
            } else {
                $parent_id = $common_parent_id;
                // item can be a single category name or a string such as
                // Parent > Child > Grandchild
                $categories = array_map('trim', explode('>', $item));
                if (count($categories) > 1 && is_numeric($categories[0])) {
                    $parent_id = $categories[0];
                    if (get_category($parent_id) !== null) {
                        // valid id, everything's ok
                        $categories = array_slice($categories, 1);
                    } else {
                        //log['error'][] = "Category ID {$parent_id} does not exist, skipping.";
                        continue;
                    }
                }
                foreach ($categories as $category) {
                    if ($category) {
                        $term = map_term_exists($category, 'category', $parent_id);
                        if ($term) {
                            $term_id = $term['term_id'];
                        } else {
                            $term_id = wp_insert_category(array(
                                'cat_name' => $category,
                                'category_parent' => $parent_id,
                            ));
                            $ids['cleanup'][] = $term_id;
                        }
                        $parent_id = $term_id;
                    }
                }
                $ids['post'][] = $term_id;
            }
        }
        return $ids;
    }

    /**
     * Parse taxonomy data from the file
     *
     * array(
     *      // hierarchical taxonomy name => ID array
     *      'my taxonomy 1' => array(1, 2, 3, ...),
     *      // non-hierarchical taxonomy name => term names string
     *      'my taxonomy 2' => array('term1', 'term2', ...),
     * )
     *
     * @param array $data
     * @return array
     */
    function get_map_taxonomies($data) {
        $taxonomies = array();
        foreach ($data as $k => $v) {
            if (preg_match('/^nav_ctax_(.*)$/', $k, $matches)) {
                $t_name = $matches[1];
                if (map_taxonomy_exists($t_name)) {
                    $taxonomies[$t_name] = create_terms($t_name,
                        $data[$k]);
                } else {
                    //log['error'][] = "Unknown taxonomy $t_name";
                }
            }
        }
        return $taxonomies;
    }

    /**
     * Return an array of term IDs for hierarchical taxonomies or the original
     * string from CSV for non-hierarchical taxonomies. The original string
     * should have the same format as nav_post_tags.
     *
     * @param string $taxonomy
     * @param string $field
     * @return mixed
     */
    function create_terms($taxonomy, $field) {
        if (is_taxonomy_hierarchical($taxonomy)) {
            $term_ids = array();
            foreach (_parse_tax($field) as $row) {
                list($parent, $child) = $row;
                $parent_ok = true;
                if ($parent) {
                    $parent_info = map_term_exists($parent, $taxonomy);
                    if (!$parent_info) {
                        // create parent
                        $parent_info = wp_insert_term($parent, $taxonomy);
                    }
                    if (!is_wp_error($parent_info)) {
                        $parent_id = $parent_info['term_id'];
                    } else {
                        // could not find or create parent
                        $parent_ok = false;
                    }
                } else {
                    $parent_id = 0;
                }

                if ($parent_ok) {
                    $child_info = map_term_exists($child, $taxonomy, $parent_id);
                    if (!$child_info) {
                        // create child
                        $child_info = wp_insert_term($child, $taxonomy,
                            array('parent' => $parent_id));
                    }
                    if (!is_wp_error($child_info)) {
                        $term_ids[] = $child_info['term_id'];
                    }
                }
            }
            return $term_ids;
        } else {
            return $field;
        }
    }

    /**
     * Compatibility wrapper for WordPress term lookup.
     */
    function map_term_exists($term, $taxonomy = '', $parent = 0) {
        if (function_exists('term_exists')) { // 3.0 or later
            return term_exists($term, $taxonomy, $parent);
        } else {
            return is_term($term, $taxonomy, $parent);
        }
    }

    /**
     * Compatibility wrapper for WordPress taxonomy lookup.
     */
    function map_taxonomy_exists($taxonomy) {
        if (function_exists('taxonomy_exists')) { // 3.0 or later
            return taxonomy_exists($taxonomy);
        } else {
            return is_taxonomy($taxonomy);
        }
    }

    /**
     * Hierarchical taxonomy fields are tiny CSV files in their own right.
     *
     * @param string $field
     * @return array
     */
    function _parse_tax($field) {
        $data = array();
        if (function_exists('str_getcsv')) { // PHP 5 >= 5.3.0
            $lines = split_lines($field);

            foreach ($lines as $line) {
                $data[] = str_getcsv($line, ',', '"');
            }
        } else {
            // Use temp files for older PHP versions. Reusing the tmp file for
            // the duration of the script might be faster, but not necessarily
            // significant.
            $handle = tmpfile();
            fwrite($handle, $field);
            fseek($handle, 0);

            while (($r = fgetcsv($handle, 999999, ',', '"')) !== false) {
                $data[] = $r;
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Try to split lines of text correctly regardless of the platform the text
     * is coming from.
     */
    function split_lines($text) {
        $lines = preg_split("/(\r\n|\n|\r)/", $text);
        return $lines;
    }


    function create_custom_fields($post_id, $data) 
	{
        foreach ($data as $k => $v) {
			
            // anything that doesn't start with nav_ is a custom field
            if (!preg_match('/^nav_/', $k) && $v != '') 
			{
                add_post_meta($post_id, $k, $v);
            }
        }
    }
	
    /**
     * Convert date in CSV file to 1999-12-31 23:52:00 format
     *
     * @param string $data
     * @return string
     */
    function parse_date($data) {
        $timestamp = strtotime($data);
        if (false === $timestamp) {
            return '';
        } else {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }

   

//$language = "eng.php";
$aff_language = get_option('wp_aff_language');
if (!empty($aff_language))
	$language_file = "lang/".$aff_language;
else
	$language_file = "lang/eng.php";
include_once($language_file);

$clientdate = (date ("Y-m-d"));
$clienttime	= (date ("H:i:s"));
$clientbrowser = getenv("HTTP_USER_AGENT");
$clientip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
$clienturl = getenv("HTTP_REFERER");

	
?>