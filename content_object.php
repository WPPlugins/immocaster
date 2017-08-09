<?php

// Output for objects
function immocaster_content_show_object()
{
	global $post;
	global $wp_query;
  	if(isset($wp_query->query[IMMOCASTER_POST_TYPE_NAME]) && isset($wp_query->query[IMMOCASTER_GET_PARAM_EXPOSE]))
	{
		$aParameters = array('exposeid'=>$wp_query->query[IMMOCASTER_GET_PARAM_EXPOSE]);
		$oImmocasterSDK = ImmocasterSDK::getInstance('is24');
		if($oExpose = $oImmocasterSDK->getExpose($aParameters))
		{
			status_header(200);
			$post = new stdClass();
			$post->ID = 999999999;
			$post->ancestors = array();
			$post->post_category = array('uncategorized');
			$post->post_content = immocaster_theme('object',array($oExpose));
			$post->post_excerpt = $post->post_content;
			$post->post_status = 'pending';
			$post->post_title = $oExpose['main']['title'];
			$post->post_type = 'page';
			$post->post_name = $post->post_title;
			$post->comment_status = 'closed';
			$post->comment_count = 0;
			$post->post_author = 1;
			$post->post_date = date('Y-m-d H:i:s');
			$post->post_date_gmt = $post->post_date;
			$post->post_modified = $post->post_date;
			$post->post_modified_gmt = $post->post_date;
			$post->ping_status = 'closed';
			$wp_query->queried_object=$post;
			$wp_query->queried_object_id = 999999999;			
			$wp_query->post=$post;
			$wp_query->found_posts = 1;
			$wp_query->post_count = 1;
			$wp_query->max_num_pages = 1;
			$wp_query->is_single = 1;
			$wp_query->is_home = 0;
			$wp_query->is_404 = false;
			$wp_query->is_posts_page = 1;
			$wp_query->posts = array($post);
			$wp_query->page=false;
			$wp_query->is_post=false;
			$wp_query->is_page=true;
		}
	}
}
add_action('wp','immocaster_content_show_object');