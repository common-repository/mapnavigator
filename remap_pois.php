<?php
include "misc_func.php";
global $wpdb;


function remap_pois ($clean_flag, $relink_flag)
{
	$map_count = 0;
	
	$maps = Mappress_Map::get_list();
	
	//loop through all the maps inthe DB
	foreach ($maps as $map) 
	{
		// get the postid associatd with the map
		$postid = get_map_post($map->mapid);
		
		$map_count++;
	
		if ($clean_flag == 1)
		{
			$invalid_map = cleanup_orphan_maps ($postid, $map->mapid);
		}
		
		if ( $postid > 0 && $invalid_map == false && is_mapnav_post($postid) == false &&  $relink_flag == 1) 
		{
			//Print("<br /> $map_count - Maptitle:$map->title,MapId:$map->mapid, PostId:$postid <br />");
			$found_post = 1;
			
			// Process the POIs looking for the Country, State and City
			$pois = $map->pois;
			foreach ($pois as $poi) 
			{
				//Print("poi title:$poi->title<br />");
				// stripos has problem, if the strings match exactly it returns the first position which is 0
				// .. since 0 means false we have to append the title with some text to account for this
				$correctedAddress = $poi->correctedAddress;
				$map_taxonomy = create_map_taxonomy_from_address ($correctedAddress);
				$map_tags = create_state_city_tag_from_address ($correctedAddress) ;
				
				if ($map_taxonomy && $map_tags)
				{
				     //print("Found a Post with a Map to Update:$postid <br />");
					// update the post for the taxonomy and tags
					$post_data_array = update_post_data_array ($map_taxonomy,$map_tags);
					update_post($postid,$post_data_array);
					break; // only 1 geographical taxonomy (continent->country->region->state-city) per post.
				}
			} // end for each POI on the map
		} // end if map associated with a post
	}	// end for loop on all maps inthe database
	
	
	//Print( "Complete");
	return;
	
}

	/**
	* Get a post attached to a single map  
	*
	* @param int $postid Post for which to get the list
	* 
	* @return a postid or FALSE if no post exist for the given mapid
	*/
	function get_map_post ($mapid) 
	{
		global $wpdb;
		$posts_table = $wpdb->prefix . 'mappress_posts';

		// Search by map ID
		if ($mapid) 
		{
			//$results = $wpdb->get_row($wpdb->prepare("SELECT postid, mapid FROM $posts_table WHERE  mapid = %d", $mapid));
			$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $posts_table "));
			if ($results != false)
			{
			
				//* testing *************
				// Fix up mapid
				foreach ($results as $result) {
					$map = unserialize($result->obj);
					$this_mapid = $result->mapid;
					$this_postid = $result->postid;
					if ($this_mapid == $mapid)
					{
						//print("get_map_post:FOUND mapId:$mapid, PostId:$this_postid,result=true<br />");
						return ($this_postid);
					}
				} 
			
				//$this_postid = Mappress_Map::get($results->postid);
				//print("get_map_post:FAILED to find a POst for mapId:$mapid,result=failse<br />");
				return (0);
			}
			else
			{
				//print("get_map_post:NO RESULT $mapid, result=false<br />");
				return false;
			}
		}
	}
	
	/**
	* Static function to parse an address into our Map Taxonomy.  
	* It will split the address into Country->Region->State->City
	* 	
	* @param mixed $address
	* @return a String containing a Map taxonomy where each item is separate by a \r\n as found in the map_navigation file column nav_ctax_map
	*     or return false if he address cannot be mapped to a Country->Region->State->City
	*/
	function create_map_taxonomy_from_address ($address) 
	{
	
		/*************************************************************************************************************************
		*
		*  Types of Google returned addresses from : http://code.google.com/apis/maps/documentation/geocoding/#ReverseGeocoding
		*        "275-291 Bedford Ave, Brooklyn, NY 11211, USA"
        *        "Williamsburg, NY, USA"
        *        "New York 11211, USA"
        *        "Kings, New York, USA"
        *        "Brooklyn, NY, USA"
        *        "New York, NY, USA"
        *        "New York, USA"
        *        "United States"
		*
		**************************************************************************************************************************/
		// USA Addresses
		// Look for 1, 2 and 3 commas to determine adddress type
		// 1 comma: we know we have State, Country
		// 2 comma: we know we have City, State, Country
		// 3 comma: we know we have Street, City, State, Country
		
		$comma_count = substr_count($address, ',');
		$country = "";
		$state = "";
		$city = "";
		$street = "";
		$place = "";
		$post_tax = false;
		
		//print("create_map_taxonomy_from_address ($address), NumCommas: $comma_count <br />");
		
		$token = strtok($address, ",");

		while ($token != false)
		{
			// assign the Place from this token
			if ($comma_count == 4)
			{
				$place = $token;
			}
			
			// assign the Street from this token
			if ($comma_count == 3)
			{
				$street = $token;
				//echo "Street=$street ,";
			}
			// assign the City from this token
			if ($comma_count == 2)
			{
				$city = $token;
				//echo "City=$city ,";
			}
			// assign the State from this token
			if ($comma_count == 1)
			{
				$state = $token;
				//echo "State=$state ,";
			}
			// assign the Country from this token
			if ($comma_count == 0)
			{
				$country = $token;
				//echo "Country=$country <br />";
			}
			$comma_count--;
			
			//echo "$token<br />";
			
			$token = strtok(",");
		}
  
		//print("create_map_taxonomy_from_address: parsed ($state,$city,$country) <br />");
		//get_state_name_by_abbrev ($astate)
	
		// stripos has problem, if the strings match exactky it returns the first position which is 0
		// .. since 0 means false we have to append the title with some text to account for this
		$country_temp = "*" . $country;
		if (stripos($country_temp,"USA") > 0 && strlen($state) > 0 && strlen($city) > 0)
		{
			$state_temp = $state;
			$state_len = strlen($state);
	
			if (strlen($state) <= 3)
			{
				$state_temp = get_state_name_by_abbrev($state);
				//print("create_map_taxonomy_from_address: called get_state_name_by_abbrev($state) returned '$state_temp' <br />");
			}
			
			//print("create_map_taxonomy_from_address: calling get_taxonomy_by_state($state_temp,$city) state_len = $state_len <br />");
			$post_tax = get_taxonomy_by_state($state_temp,$city);
		}
		
	  
		return ($post_tax);
	}
	/**
	* Static function to parse an address and build our Map tag that identifies all cities in a state for our State post mashup.  
	* It will parse the address into statename.city
	* 	
	* @param mixed $address
	* @return a String containing statename.city
	*     or return false if he address cannot be mapped to a state and city
	*/
	function create_state_city_tag_from_address ($address) 
	{
	
		/*************************************************************************************************************************
		*
		*  Types of Google returned addresses from : http://code.google.com/apis/maps/documentation/geocoding/#ReverseGeocoding
		*        "275-291 Bedford Ave, Brooklyn, NY 11211, USA"
        *        "Williamsburg, NY, USA"
        *        "New York 11211, USA"
        *        "Kings, New York, USA"
        *        "Brooklyn, NY, USA"
        *        "New York, NY, USA"
        *        "New York, USA"
        *        "United States"
		*
		**************************************************************************************************************************/
		// USA Addresses
		// Look for 1, 2 and 3 commas to determine adddress type
		// 1 comma: we know we have State, Country
		// 2 comma: we know we have City, State, Country
		// 3 comma: we know we have Street, City, State, Country
		
		$comma_count = substr_count($address, ',');
		$country = "";
		$state = "";
		$city = "";
		$street = "";
		$place = "";
		$post_tag = false;
		
		$token = strtok($address, ",");

		while ($token != false)
		{
			// assign the Place from this token
			if ($comma_count == 4)
			{
				$place = $token;
			}
			// assign the Street from this token
			if ($comma_count == 3)
			{
				$street = $token;
			}
			// assign the City from this token
			if ($comma_count == 2)
			{
				$city = $token;
			}
			// assign the State from this token
			if ($comma_count == 1)
			{
				$state = $token;
			}
			// assign the Country from this token
			if ($comma_count == 0)
			{
				$country = $token;
			}
			$comma_count--;
			
			//echo "$token<br />";
			
			$token = strtok(",");
		}
  
		//print("create_state_city_tag_from_address: parsed ($state,$city,$country) <br />");
		//$map_taxonomy['Country'] = $country;
		//$map_taxonomy['State'] = $state;
		//$map_taxonomy['City'] = $city;
		//$map_taxonomy['Street'] = $street;
		
		//print("poi title:$poi->title, Town:$atown");
		// stripos has problem, if the strings match exactky it returns the first position which is 0
		// .. since 0 means false we have to append the title with some text to account for this
		$country_temp = "*" . $country;
		if (stripos($country_temp,"USA") > 0 && strlen($state) > 0 && strlen($city) > 0)
		{
			$state_temp = $state;
			if (strlen($state) <= 3)
			{
				$state_temp = get_state_name_by_abbrev($state);
			}
			$post_tag = get_state_city_tag_by_state($state_temp);
		}
		
	  
		return ($post_tag);
	}
	
	
function update_post_data_array ($post_tax,$post_tags)
{
	 $post_array = array(
        'nav_post_tags'       => $post_tags,
        'nav_post_categories' => 'Maps > Continent > Country > Region > State > City, Map Navigator',
        'nav_post_parent'     => 0,
		'nav_ctax_maps'       => $post_tax,
    );
	
	//print("<br /> nav_post_tags = " . $post_array['nav_post_tags'] . " <br />");
	//print("<br /> nav_ctax_maps = " . $post_array['nav_ctax_maps'] . " <br />");
	return $post_array;
	
}	  

function update_post($postid,$data) 
{
		
        $updated_post = array(
		    'ID'           => $postid,
            'tax_input'    => get_map_taxonomies($data),
			//'tags_input'   => $data['nav_post_tags'],
        );

		// Setup categories before inserting - this should make insertion
		// faster, but I don't exactly remember why :) Most likely because
		// we don't assign default cat to post when nav_post_categories
		// is not empty.
		$cats = create_or_get_categories($data, 0);
		
		//get current categories of the post and merge with new
		$current_post_cats = wp_get_post_categories( $postid);
		$updated_post_cats = array_merge($cats['post'],$current_post_cats);
		
		$updated_post['post_category'] = $updated_post_cats;
		$tax_map = get_map_taxonomies($data);
		$tax_map = array_map('intval', $tax_map);
		wp_set_object_terms( $postid, $tax_map, 'maps',true);
		wp_set_object_terms( $postid, $data['nav_post_tags'], 'post_tag', true);
		wp_update_post( $updated_post );
	return;
}


/** 
* Remove orphan maps from database
* 
*/		
function cleanup_orphan_maps ($postid, $mapid)
{
	global $wpdb;
	$posts_table = $wpdb->prefix . 'mappress_posts';
	$maps_table = $wpdb->prefix . 'mappress_maps';
	$post_status = get_post_status( $postid );
	//Print ("<br /> post status for mapid:$mapid and postid:$postid is '$post_status' <br /> ");
	if ($post_status == '')
	{
		$thisSQL = sprintf("DELETE FROM $posts_table WHERE postid = %u",$postid);
		// delete all map posts without a post
		$wpdb->query($thisSQL);
	
		// delete all maps without a map post
		$thisSQL = sprintf("DELETE FROM $maps_table WHERE mapid= %u",$positd);
		$wpdb->query($thisSQL);
		return true;
	}
	
	return false;
}
	
function is_mapnav_post($postid) 
{
	foreach((get_the_category($postid)) as $category) 
	{
	  $postcat= $category->cat_ID;
	  $catname =$category->cat_name;
	  //Print ("<br /> category name is:$catname <br />");
	  if ($catname == "Map Navigator")
		return true;
	}
	return false;
}
@include_once dirname( __FILE__ ) . '../mappress-google-maps-for-wordpress/mappress.php';
@include_once dirname( __FILE__ ) . '../mappress-google-maps-for-wordpress/mappress_api.php';
@include_once dirname( __FILE__ ) . '../mappress-google-maps-for-wordpress/pro/mappress_pro.php';
@include_once dirname( __FILE__ ) . '../mappress-google-maps-for-wordpress/mappress_updater.php';
	
?>