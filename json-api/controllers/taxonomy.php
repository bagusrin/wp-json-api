<?php

class JSON_API_Taxonomy_Controller {

  public function get() {
    global $json_api;
    global $post;
    global $post_count;

    $taxonomy = $json_api->query->taxonomy;
    $slug = $json_api->query->slug;

    if (empty($taxonomy)) {
      $json_api->error("Include a 'taxonomy' on query var.");
    }
    if (empty($slug)) {
      $json_api->error("Include a 'slug' on query var.");
    }

    $args = array(
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $slug,
            )
        )
    );
    $query = new WP_Query($args);

    $return = array();
    if ($query->have_posts()) {
      $return = array(
          'status' => 'ok',
          'count' => $query->post_count,
          'count_total' => $query->found_posts,
          'pages' => $query->max_num_pages,
      );
      $arrPosts = array();
      while ($query->have_posts()) {
        $query->the_post();

        $attachment = array();
        $thumbnail = null;
        if (has_post_thumbnail(get_the_id())) {
          $tag_img = get_the_post_thumbnail(get_the_id());
          preg_match_all('/<img[^>]*src=\"(.*?)\"[^>]*>/i', $tag_img, $result);
          if (array_key_exists(1, $result) && count($result[1]) > 0) {
            $thumbnail = $result[1][0];
          }
        }


        $meta = get_post_meta(get_the_id());
        $custom_fields = array();
        foreach ($meta as $key => $value) {
          if (substr($key, 0, 1) != '_') {
            $custom_fields[] = array($key => $value);
          }
        }

        $tmp = $post->to_array();
        $arrPosts[] = array(
            'id' => get_the_id(),
            'type' => $tmp['post_type'],
            'slug' => $tmp['post_name'],
            'url' => '',
            'status' => $tmp['post_status'],
            'title' => $tmp['post_title'],
            'title_plain' => html_entity_decode($tmp['post_title']),
            'content' => $tmp['post_content'],
            'excerpt' => $tmp['post_excerpt'],
            'date' => $tmp['post_date_gmt'],
            'modified' => $tmp['post_modified_gmt'],
            'categories' => $tmp['post_category'],
            'tags' => $tmp['tags_input'],
            'author' => array(
                'id' => get_the_author_meta('ID'),
                'slug' => get_the_author_meta('user_nicename'),
                'name' => get_the_author(),
                'first_name' => get_the_author_meta('first_name'),
                'last_name' => get_the_author_meta('last_name'),
                'nickname' => get_the_author_meta('user_nicename'),
                'url' => get_the_author_meta('url'),
                'description' => get_the_author_meta('description'),
            ),
            'thumbnail' => $thumbnail,
            'custom_fields' => $custom_fields,
        );
      }
      $return['posts'] = $arrPosts;
    } else {
      $json_api->error("No data found");
    }

    return $return;
  }

}

?>
