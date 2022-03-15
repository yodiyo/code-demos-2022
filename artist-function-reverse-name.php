<?php

function yb_get_artists( $post_id=FALSE, $artist_ids=FALSE, $limit_artwork=FALSE ) {

	//set the variables
	$output = array();

	$args = array(
        'post_type' 		=> 'artist',
        'post_status' 		=> 'publish',
		'p'         		=> ($post_id) ? $post_id : '',
		'post__in'		 	=> ($artist_ids) ? $artist_ids : '',
	    'meta_key' 			=> 'tombstone_last',
        'orderby' 			=> 'meta_value',
        'order' 			=> 'ASC',
		'posts_per_page' 	=> -1
    );

    //process the post query
    $query = new WP_Query( $args );

    while ( $query->have_posts() ){
        $query->the_post();
		$id = get_the_ID();

		// for names where the surname is first, we are indexing by first name.
		$reverse_name = get_field( 'reverse_name', $id ); // ACF
		$firstname    = get_post_meta( $id, 'tombstone_first', true );
		$surname      = get_post_meta( $id, 'tombstone_last', true );

		if ( $reverse_name ) {
			$surname   = get_post_meta( $id, 'tombstone_first', true );
			$firstname = get_post_meta( $id, 'tombstone_last', true );
		}

		$surname_uc = ucfirst( $surname );

		$output[] = array(
      		"id"			=> $id,
			"full_name"		=> get_the_title( $id ),
			"link"			=> get_the_permalink($id),
			"featured_img"	=> wp_get_attachment_image_src(get_post_thumbnail_id($id),'gs_feature_1276')[0], //$primary_image_lg[0],
			"hero_artwork" 	=> get_post_meta( $id, 'hero_artwork', 		true ),
			"artwork"		=> array(), //$artwork,
			"first_name"	=> $firstname,
			"last_name"		=> $surname,
			"last_name_uc"	=> $surname_uc, // for sorting names that have lowercase prefixes, eg Willem de Kooning.
			"dob"			=> get_post_meta( $id, 'tombstone_dob', 		true ),
			"pob"			=> get_post_meta( $id, 'tombstone_pob', 		true ),
			"dod"			=> get_post_meta( $id, 'tombstone_dod', 		true ),
			"pod"			=> get_post_meta( $id, 'tombstone_pod', 		true ),
			"events"		=> get_post_meta( $id, 'related_event', 		true ),
			"publications"	=> get_post_meta( $id, 'related_publication', 	true ),
			"news"			=> get_post_meta( $id, 'related_news',			true ),
    	);
    }

	wp_reset_postdata();

    //get all artwork here instead of getting individual artworks as a subquery
    $returned_artists = (array) array_column( $output, 'id');

    foreach( yb_get_artwork(array( 'artist_num' => $returned_artists, 'post_status' => 'publish', 'ids_only' => $limit_artwork )) as $artwork){
        $output[ array_search($artwork['artist_num'],$returned_artists) ]['artwork'][] = $artwork;
    }

	// re-sort the list according to updating last names uppercase.
	$output = wp_list_sort( $output, 'last_name_uc' , 'ASC', true );

    // return the output
    return $output;
}