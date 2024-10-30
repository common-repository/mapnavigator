<?php
/*
Plugin Name: MapNavigator
Description: Create Map Navigation Pages with MapPress map markers <em>You can reach the author at <a href="mailto:rothmaniac@gmail.com">rothmaniac@gmail.com</a></em>.
Version: 1.1.0
Author: David Rothman
*/
/**
 * LICENSE: The MIT License {{{
 *
 * Copyright (c) <2011> <David Rothman>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    David Rothman <rothmaniac@gmail.com>
 * @copyright 2012 David Rothman
 * @license   The MIT License
 * }}}
 */
 
/**
 * LICENSE: The MIT License {{{
 *
 * Copyright (c) <2009> <Denis Kobozev>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Denis Kobozev <d.v.kobozev@gmail.com>
 * @copyright 2009 Denis Kobozev
 * @license   The MIT License
 * }}}
 */
include "map_icons.php";
include "remap_pois.php";
class MapNavigatorPlugin {
    var $defaults = array(
        'nav_post_title'      => null,
        'nav_post_post'       => null,
        'nav_post_type'       => null,
        'nav_post_excerpt'    => null,
        'nav_post_date'       => null,
        'nav_post_tags'       => null,
        'nav_post_categories' => null,
        'nav_post_author'     => null,
        'nav_post_slug'       => null,
        'nav_post_parent'     => 0,
		'navigator_version'   => null,
		);

    var $log = array();
	

    /**
     * Determine value of option $name from database, $default value or $params,
     * save it to the db if needed and return it.
     *
     * @param string $name
     * @param mixed  $default
     * @param array  $params
     * @return string
     */
    function process_option($name, $default, $params) {
        if (array_key_exists($name, $params)) {
            $value = stripslashes($params[$name]);
        } elseif (array_key_exists('_'.$name, $params)) {
            // unchecked checkbox value
            $value = stripslashes($params['_'.$name]);
        } else {
            $value = null;
        }
        $stored_value = get_option($name);
        if ($value == null) {
            if ($stored_value === false) {
                if (is_callable($default) &&
                    method_exists($default[0], $default[1])) {
                    $value = call_user_func($default);
                } else {
                    $value = $default;
                }
                add_option($name, $value);
            } else {
                $value = $stored_value;
            }
        } else {
            if ($stored_value === false) {
                add_option($name, $value);
            } elseif ($stored_value != $value) {
                update_option($name, $value);
            }
        }
        return $value;
    }

    /**
     * Plugin's interface
     *
     * @return void
     */
    function form() {
        $opt_draft = $this->process_option('nav_importer_import_as_draft','publish', $_POST);
		$opt_org = $this->process_option('nav_importer_organization_name','mapClick', $_POST);
		$opt_org_image = $this->process_option('nav_importer_organization_image','http://mapclick.org/wp-content/uploads/2012/01/mapa.jpg', $_POST);
		$opt_org_web_link = $this->process_option('nav_importer_organization_web_link','http://mapclick.org', $_POST);		
        $opt_cat = $this->process_option('nav_importer_cat', 0, $_POST);
		$opt_icon = $this->process_option('map_icon','red', $_POST);
		$opt_clean = $this->process_option('nav_importer_cleanup_maps_db', 0, $_POST);
		$opt_relink = $this->process_option('nav_importer_relink_maps_db', 0, $_POST);

        if ('POST' == $_SERVER['REQUEST_METHOD']) {
            $this->post(compact('opt_draft', 'opt_org', 'opt_org_image', 'opt_org_web_link', 'opt_cat', 'opt_icon', 'opt_clean', 'opt_relink'));
        }

        // form HTML {{{
?>

<div class="wrap">
    <img src='http://mapclick.org/wp-content/uploads/2012/01/mapnavigator_banner.png' style='width:1000px;height:100px'>
    <form class="add:the-list: validate" method="post" enctype="multipart/form-data">

		<h3><strong><?php echo "Customization for Organization"; ?></strong></h3>
		<!-- Organization Name -->
        <p>Organization Name: 
        <input name="_nav_importer_organization_name" size="80" type="hidden" value="publish"
        <label><input name="nav_importer_organization_name" type="text" value="mapClicks" /> <br /> <small>This will put your organization name into each of the Map markers generated for the Country, Regions, States and Cities.</small></label>
        </p>
		
		<!-- Organization Image Link -->
        <p>Organization Image Link: 
        <input name="_nav_importer_organization_image" size="120" type="hidden" value="publish"
        <label><input name="nav_importer_organization_image" type="text" size="120" value="http://mapclick.org/wp-content/uploads/2012/01/mapa.jpg" /> <br /> <small>This will put your organization's image into each of the Map markers generated for the Country, Regions, States and Cities.</small></label>
        </p>
		
		<!-- Organization Image Link -->
        <p>Organization Web Link: 
        <input name="_nav_importer_organization_web_link" size="120" type="hidden" value="publish"
        <label><input name="nav_importer_organization_web_link" type="text" size="120" value="http://mapclick.org" /> <br /> <small>This will put your organization's web link into each of the Map markers generated for the Country, Regions, States and Cities.</small></label>
        </p>

        <!-- Map Icon to use in markers -->
		<p>Organizaton Icon
		<select name=map_icon class=dropdown value="red" >
<?
            foreach($GLOBALS['icons'] as $key => $icon)
                print '<option value="'.$key.'" '.($_POST['map_icon'] == $key ? 'selected' : '').'>'.$icon.'</option>'."\n";
?>
        </select>
		 <br /> <small>This will put your organization icon into each of the Map markers generated for the Country, Regions, States and Cities.</small></p>
		 
		 <h3><strong><?php echo "Map Navigation Import"; ?></strong></h3>
		 
		  <!-- MapClick.org link -->
		 <p>Download a Map Navigation file for your location at <a href="http://mapclick.org">mapclick.org!</a><br </p>
		 
		 <!-- Parent category -->
        <p>Organize into category <?php wp_dropdown_categories(array('show_option_all' => 'Select one ...', 'hide_empty' => 0, 'hierarchical' => 1, 'show_count' => 0, 'name' => 'nav_importer_cat', 'orderby' => 'name', 'selected' => $opt_cat));?><br/>
            <small>This will create new categories inside the category parent you choose.</small></p>
			
		 <!-- Import as draft -->
        <p>
        <input name="_nav_importer_import_as_draft" type="hidden" value="publish" />
        <label><input name="nav_importer_import_as_draft" type="checkbox" <?php if ('draft' == $opt_draft) { echo 'checked="checked"'; } ?> value="draft" /> Create Map Navigation posts as draft posts</label>
        </p>
		
		<!-- File input -->
        <p><label for="nav_import">Import Map Navigation file:</label><br/>
            <input name="nav_import" id="nav_import" type="file" value="" aria-required="true" /></p>
        <p class="submit"><input type="submit" class="button" name="submit" value="Process Map Navigation File" />
		<br /> <small>This will build MapNavigator Hierarchy using the Map Navigation CSV file selected above.</small></p>
		
		<h3><strong><?php echo "MapPress Maps Import and Database Cleanup"; ?></strong></h3>
		 <!-- Maps Database Cleanup -->
        <p>
        <input name="_nav_importer_cleanup_maps_db" type="hidden" value="publish" />
        <label><input name="nav_importer_cleanup_maps_db" type="checkbox" <?php if ('clean' == $opt_clean) { echo 'checked="checked"'; } ?> value="clean" /> Cleanup MapPress Maps Database tables</label>
		<br /> <small>This will delete Maps and Map markers for Posts that have been deleted.</small> </p>
		
		<!-- Maps Database Import -->
        <p>
        <input name="_nav_importer_relink_maps_db" type="hidden" value="publish" />
        <label><input name="nav_importer_relink_maps_db" type="checkbox" <?php if ('relink' == $opt_relink) { echo 'checked="checked"'; } ?> value="relink" /> Link MapPress Maps into Map Navigator Hierarchy</label>
		<br /> <small>This will link Maps and Map Markers in database to the MapNavigator Hierarchy.</small> </p>
		
		<p class="dbprocess"><input type="submit" class="button" name="dbprocess" value="Perform Map Database Operations" />
		<br /> <small>This will Clean and/or Link MapPress maps as defined by the above checked boxes.</small> </p>
		 
    </form>
</div><!-- end wrap -->

<?php
        // end form HTML }}}

    }

    function print_messages() {
        if (!empty($this->log)) {

        // messages HTML {{{
?>

<div class="wrap">
    <?php if (!empty($this->log['error'])): ?>

    <div class="error">

        <?php foreach ($this->log['error'] as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <?php if (!empty($this->log['notice'])): ?>

    <div class="updated fade">

        <?php foreach ($this->log['notice'] as $notice): ?>
            <p><?php echo $notice; ?></p>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>
</div><!-- end wrap -->

<?php
        // end messages HTML }}}

            $this->log = array();
        }
    }

    /**
     * Handle POST submission
     *
     * @param array $options
     * @return void
     */
    function post($options) 
	{
	
		extract($options);
		 
		if(isset($_POST['dbprocess']))
		{
			if ($opt_clean == "clean")
				$clean_flag = 1;
			else 
				$clean_flag = 0;
				
			if ($opt_relink == "relink")
			{
				$relink_flag = 1;
				$opt_relink = ", link maps ";
			}
			else 
			{
				$opt_relink = "";
				$relink_flag = 0;
			}
				
			remap_pois ($clean_flag, $relink_flag);
			$this->log['notice'][] = sprintf("<b>Sucessfully completed the %s%s MapPress Database operations.</b>", $opt_clean, $opt_relink);
			$this->print_messages();
		}
		
		if(isset($_POST['submit']))
		{
			if (empty($_FILES['nav_import']['tmp_name'])) {
				$this->log['error'][] = 'No file uploaded, aborting.';
				$this->print_messages();
				return;
			}

			require_once 'File_CSV_DataSource/DataSource.php';

			$time_start = microtime(true);
			$csv = new File_CSV_DataSource;
			$file = $_FILES['nav_import']['tmp_name'];
			$this->stripBOM($file);

			if (!$csv->load($file)) {
				$this->log['error'][] = 'Failed to load file, aborting.';
				$this->print_messages();
				return;
			}

			// pad shorter rows with empty values
			$csv->symmetrize();

			// WordPress sets the correct timezone for date functions somewhere
			// in the bowels of wp_insert_post(). We need strtotime() to return
			// correct time before the call to wp_insert_post().
			$tz = get_option('timezone_string');
			if ($tz && function_exists('date_default_timezone_set')) {
				date_default_timezone_set($tz);
			}

			$skipped = 0;
			$imported = 0;
			$comments = 0;
			foreach ($csv->connect() as $nav_data) {
				if ($post_id = $this->create_post($nav_data, $options)) 
				{
					$imported++;
					$comments += $this->add_comments($post_id, $nav_data);
					$this->create_custom_fields($post_id, $nav_data);
					// Update the post so the maps are created from the custom mappress field map_address that we just added
					$my_post = array();
					$my_post['ID'] = $post_id;
					wp_update_post( $my_post );
				} else {
					$skipped++;
				}
			}

			if (file_exists($file)) {
				@unlink($file);
			}

			$exec_time = microtime(true) - $time_start;

			if ($skipped) {
				$this->log['notice'][] = "<b>Skipped {$skipped} posts (most likely due to empty title, body and excerpt).</b>";
			}
			$this->log['notice'][] = sprintf("<b>Created {$imported} Map Navigation posts and {$comments} comments in %.2f seconds.</b>", $exec_time);
			$this->print_messages();
		}
    }

    function create_post($data, $options) {
        extract($options);

        $data = array_merge($this->defaults, $data);
        $type = $data['nav_post_type'] ? $data['nav_post_type'] : 'post';
        $valid_type = (function_exists('post_type_exists') &&
            post_type_exists($type)) || in_array($type, array('post', 'page'));

        if (!$valid_type) {
            $this->log['error']["type-{$type}"] = sprintf(
                'Unknown post type "%s".', $type);
        }
		
		$org_value = get_option("nav_importer_organization_name","noOrgFound");
		$body_title = str_ireplace("mapClicks",$org_value,$data['nav_post_excerpt']);
		$image_value = get_option("nav_importer_organization_image","http://mapclick.org/wp-content/uploads/2012/01/mapa.jpg");
		$body_image = sprintf("<a href='%s'><img src='%s' style='width:100px;height:100px'></a> <br />  %s",$org_value, $image_value, $body_title);
		
        $new_post = array(
            'post_title'   => convert_chars($data['nav_post_title']),
            'post_content' => wpautop(convert_chars(str_ireplace("mapClicks",$opt_org,$data['nav_post_post']))),
            'post_status'  => $opt_draft,
            'post_type'    => $type,
            'post_date'    => $this->parse_date($data['nav_post_date']),
            'post_excerpt' => convert_chars($body_image),   
            'post_name'    => $data['nav_post_slug'],
            'post_author'  => $this->get_auth_id($data['nav_post_author']),
            'tax_input'    => $this->get_taxonomies($data),
            'post_parent'  => $data['nav_post_parent'],
        );

        // pages don't have tags or categories
        if ('page' !== $type) {
            $new_post['tags_input'] = str_ireplace("mapclick",strtolower($opt_org),$data['nav_post_tags']);   

            // Setup categories before inserting - this should make insertion
            // faster, but I don't exactly remember why :) Most likely because
            // we don't assign default cat to post when nav_post_categories
            // is not empty.
            $cats = $this->create_or_get_categories($data, $opt_cat);
            $new_post['post_category'] = $cats['post'];
        }

        // create!
        $id = wp_insert_post($new_post);

        if ('page' !== $type && !$id) {
            // cleanup new categories on failure
            foreach ($cats['cleanup'] as $c) {
                wp_delete_term($c, 'category');
            }
        }
        return $id;
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
                    $this->log['error'][] = "Category ID {$item} does not exist, skipping.";
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
                        $this->log['error'][] = "Category ID {$parent_id} does not exist, skipping.";
                        continue;
                    }
                }
                foreach ($categories as $category) {
                    if ($category) {
                        $term = $this->term_exists($category, 'category', $parent_id);
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
    function get_taxonomies($data) {
        $taxonomies = array();
        foreach ($data as $k => $v) {
            if (preg_match('/^nav_ctax_(.*)$/', $k, $matches)) {
                $t_name = $matches[1];
                if ($this->taxonomy_exists($t_name)) {
                    $taxonomies[$t_name] = $this->create_terms($t_name,
                        $data[$k]);
                } else {
                    $this->log['error'][] = "Unknown taxonomy $t_name";
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
            foreach ($this->_parse_tax($field) as $row) {
                list($parent, $child) = $row;
                $parent_ok = true;
                if ($parent) {
                    $parent_info = $this->term_exists($parent, $taxonomy);
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
                    $child_info = $this->term_exists($child, $taxonomy, $parent_id);
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
    function term_exists($term, $taxonomy = '', $parent = 0) {
        if (function_exists('term_exists')) { // 3.0 or later
            return term_exists($term, $taxonomy, $parent);
        } else {
            return is_term($term, $taxonomy, $parent);
        }
    }

    /**
     * Compatibility wrapper for WordPress taxonomy lookup.
     */
    function taxonomy_exists($taxonomy) {
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
            $lines = $this->split_lines($field);

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

    function add_comments($post_id, $data) {
        // First get a list of the comments for this post
        $comments = array();
        foreach ($data as $k => $v) {
            // comments start with nav_comment_
            if (    preg_match('/^nav_comment_([^_]+)_(.*)/', $k, $matches) &&
                    $v != '') {
                $comments[$matches[1]] = 1;
            }
        }
        // Sort this list which specifies the order they are inserted, in case
        // that matters somewhere
        ksort($comments);

        // Now go through each comment and insert it. More fields are possible
        // in principle (see docu of wp_insert_comment), but I didn't have data
        // for them so I didn't test them, so I didn't include them.
        $count = 0;
        foreach ($comments as $cid => $v) {
            $new_comment = array(
                'comment_post_ID' => $post_id,
                'comment_approved' => 1,
            );

            if (isset($data["nav_comment_{$cid}_author"])) {
                $new_comment['comment_author'] = convert_chars(
                    $data["nav_comment_{$cid}_author"]);
            }
            if (isset($data["nav_comment_{$cid}_author_email"])) {
                $new_comment['comment_author_email'] = convert_chars(
                    $data["nav_comment_{$cid}_author_email"]);
            }
            if (isset($data["nav_comment_{$cid}_url"])) {
                $new_comment['comment_author_url'] = convert_chars(
                    $data["nav_comment_{$cid}_url"]);
            }
            if (isset($data["nav_comment_{$cid}_content"])) {
                $new_comment['comment_content'] = convert_chars(
                    $data["nav_comment_{$cid}_content"]);
            }
            if (isset($data["nav_comment_{$cid}_date"])) {
                $new_comment['comment_date'] = $this->parse_date(
                    $data["nav_comment_{$cid}_date"]);
            }

            $id = wp_insert_comment($new_comment);
            if ($id) {
                $count++;
            } else {
                $this->log['error'][] = "Could not add comment $cid";
            }
        }
        return $count;
    }

    function create_custom_fields($post_id, $data) 
	{
        foreach ($data as $k => $v) {
			
            // anything that doesn't start with nav_ is a custom field
            if (!preg_match('/^nav_/', $k) && $v != '') 
			{
				if (strcasecmp($k,"map_address") == 0)
				{
					$v = $this->create_map_marker_body($v);
				}
                add_post_meta($post_id, $k, $v);
            }
        }
    }

	function create_map_marker_body($body_text) 
	{
		$org_value = get_option("nav_importer_organization_name","noOrgFound");
		$org_icon = get_option("map_icon","red-dot");
		$body_title = str_ireplace("mapClick",$org_value,$body_text);
		$image_value = get_option("nav_importer_organization_image","http://mapclick.org/wp-content/uploads/2012/01/mapa.jpg");
		$weblink_value = get_option("nav_importer_organization_web_link","http://mapclick.org");
		$body_image = sprintf("Affiliate <a href='%s'><img src='%s' style='width:100px;height:100px'></a> ",$weblink_value, $image_value);
		$body_text_and_image = str_ireplace("Affiliate",$body_image,$body_title);
		$body_text_and_image_and_icon = str_ireplace("red-dot",$org_icon,$body_text_and_image);
	
        return($body_text_and_image_and_icon);
    }
	
    function get_auth_id($author) {
        if (is_numeric($author)) {
            return $author;
        }
        $author_data = get_userdatabylogin($author);
        return ($author_data) ? $author_data->ID : 0;
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

    /**
     * Delete BOM from UTF-8 file.
     *
     * @param string $fname
     * @return void
     */
    function stripBOM($fname) {
        $res = fopen($fname, 'rb');
        if (false !== $res) {
            $bytes = fread($res, 3);
            if ($bytes == pack('CCC', 0xef, 0xbb, 0xbf)) {
                $this->log['notice'][] = 'Getting rid of byte order mark...';
                fclose($res);

                $contents = file_get_contents($fname);
                if (false === $contents) {
                    trigger_error('Failed to get file contents.', E_USER_WARNING);
                }
                $contents = substr($contents, 3);
                $success = file_put_contents($fname, $contents);
                if (false === $success) {
                    trigger_error('Failed to put file contents.', E_USER_WARNING);
                }
            } else {
                fclose($res);
            }
        } else {
            $this->log['error'][] = 'Failed to open file, aborting.';
        }
    }
}  // End MapNavigator class

function mapnav_admin_menu() {
    require_once ABSPATH . '/wp-admin/admin.php';
    $plugin = new MapNavigatorPlugin;
    add_management_page('edit.php', 'MapNavigator', 'manage_options', __FILE__,
        array($plugin, 'form'));
}

function mapnav_init ()
{
	$version = "1.1.0";
	
	// Check if functions.php for current theme needs to be updated
	$current_version = get_option('navigator_version');
	update_option('navigator_version', $version);

	if ($current_version != $version  && isActivated() == false)
	{
		activation_104();
	}
}

/** 
* Upgrade from version 1.0.4 and older 
* 
*/		
function isActivated() 
{
	$blog_theme_directory = get_stylesheet_directory(); 
	$append_to_file = $blog_theme_directory . '/functions.php';
	
	// Open the file to get existing content from themes function.php
	$maps_taxonomy_content = file_get_contents($append_to_file);
	$found_map_taxonomy = false;
	
	$handle = @fopen($append_to_file, "r");
	if ($handle) 
	{
		while (($buffer = fgets($handle, 4096)) !== false && $found_map_taxonomy == false ) 
		{
			if (strpos($buffer,"map_navigator_taxonomies") != FALSE)
			$found_map_taxonomy = true;
		}
		
		fclose($handle);
	}
	
	return($found_map_taxonomy );
}
/** 
* Upgrade from version 1.0.4 and older 
* 
*/		
function activation_104() 
{
	$blog_theme_directory = get_stylesheet_directory(); 
	$plugin_path = plugin_dir_path(  __FILE__ );
	
	$append_from_file = $plugin_path . 'theme.functions.php';
	$append_to_file = $blog_theme_directory . '/functions.php';
	
	// Open the file to get existing content from MapNavigator Plugin Install directory
	$maps_taxonomy_content = file_get_contents($append_from_file);

	// Write the contents to the file, 
	// using the FILE_APPEND flag to append the content to the end of the file
	// and the LOCK_EX flag to prevent anyone else writing to the file at the same time
	file_put_contents($append_to_file, $maps_taxonomy_content, FILE_APPEND | LOCK_EX);
}

add_action('admin_menu', 'mapnav_admin_menu');
add_action('init', 'mapnav_init');

?>
