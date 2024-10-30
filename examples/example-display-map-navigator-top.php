<?php
		global $mappress; 
		echo $mappress->shortcode_mashup (array(
							'zoom' => 3,
							'directions' => 'false',
							'width' => 600,
							'marker_title' => 'marker',
							'marker_link' => 'true',
							'marker_body' => 'excerpt',
							'show' => 'query',
							'show_query' => 'category=region&tag=usa-region' ) ); 

?>