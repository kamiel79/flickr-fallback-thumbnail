<?php
/**
Plugin Name: Flickr Fallback Thumbnails
Version: 1.0
Plugin URI: http://wordpress.creativechoice.org
Description: Automatically grab a fallback featured image if your post doesn't have one!
Author: Kamiel Choi
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author URI: http://creativechoice.org
 */

class flickr_thumb {

	var $api_key = "your_api_key";
	function __construct() {
			add_filter( 'post_thumbnail_html', array(&$this, 'flickr_thumb_init'),20,5);
	}
	
	function random_tag ($postid) {
		$tags = wp_get_post_tags( $postid, array( 'fields' => 'names' ) );
		if (count($tags)==0) return false;
		$r = rand(0, count($tags)-1);
		return $tags[$r];
	}

	function get_flickr_img ($tag, $count=1) {
	  $tag 			= urlencode($tag);
	  $thumb_url 	= "";
	  $url 			= 'https://api.flickr.com/services/rest/?';
	  $url 			.= 'method=flickr.photos.search&api_key='.$this->api_key.'&tags='.$tag.'&per_page='.$count;
	  $url 			.= 'format='.$format;

	  if (!wp_get_http( $url )) return "";

	  $xml = simplexml_load_file($url);
	  if (!$xml) return false;
	# http://www.flickr.com/services/api/misc.urls.html
	# http://farm{farm-id}.static.flickr.com/{server-id}/{id}_{secret}.jpg

	if (count ($xml->photos->photo)>0) {
		foreach ($xml->photos->photo as $photo) {
		  $title 	= $photo['title'];	
		  $farmid 	= $photo['farm'];
		  $serverid = $photo['server'];
		  $id		= $photo['id'];
		  $secret 	= $photo['secret'];
		  $owner 	= $photo['owner'];
		  if ($count>1) $thumb_url[] = "http://farm{$farmid}.static.flickr.com/{$serverid}/{$id}_{$secret}_m.jpg";
		  else $thumb_url = "http://farm{$farmid}.static.flickr.com/{$serverid}/{$id}_{$secret}_m.jpg";
		  }
		}

	return $thumb_url;
	} // get_flickr_img

	
	
	function flickr_thumb_init( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
			if ( empty( $html ) ) {
					return sprintf(
					'<img src="%s" height="%s" width="%s" />',
					$this->get_flickr_img ( $this->random_tag($post_id) ),
					get_option( 'thumbnail_size_w' ),
					get_option( 'thumbnail_size_h' ) );
			}
			return $html;
		}
} //flickr_thumb

$ft = new flickr_thumb();


?>
