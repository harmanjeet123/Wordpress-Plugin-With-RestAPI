<?php
/**
 * Plugin Name: Inmolink
 * Plugin URI: http://inmolink.es
 * Description: Set of shortcodes to display InmoLink property search and results.
 * Version: 2.07.0
 * Author: Innotech
 * Author URI: https://innotech.com.es
 */

remove_filter('the_content', 'wptexturize');

require_once(dirname(__FILE__).'/legacy.php');
require_once(dirname(__FILE__).'/class.inmolink-search.php');
require_once(dirname(__FILE__).'/class.inmolink-results.php');
require_once(dirname(__FILE__).'/class.inmolink-settings.php');
require_once(dirname(__FILE__).'/class.inmolink-contact.php');
require_once(dirname(__FILE__).'/class.inmolink-agent.php');

add_action('wp_head','inmolink_og_fix',0,1);
function inmolink_og_fix(){
    global $wp; global $post;
    $thisUrl = home_url( $wp->request ); 

    $refs = get_query_var('ref_no');
    if(empty($refs))
      return;

    if(!isset($post))
      return;

    if(stripos($post->post_content,'[inmolink_property') === false)
      return;

    $results = new InmoLinkResults();
  
    $results->fetch_properties(array('ref_no'=>$refs));
    
    if($results->count != 1)
      echo '<script type="text/javascript">window.location.href="'.site_url().'";</script>';

  
    $property = $results->results[0];

    $title = $property->location_id->name . ' ' . $property->type_id->name;
    $description = $property->desc;
    $images = $property->images;

    $active_plugins = get_option('active_plugins');

    if(in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', $active_plugins))  OR in_array('seo-by-rank-math/rank-math.php', apply_filters('active_plugins', $active_plugins))){  
        add_filter('wpseo_canonical',function(){return false;});
        add_filter('wpseo_opengraph_title',function() use (&$title){return $title;});
        add_filter('wpseo_opengraph_desc',function() use (&$description){return $description;});
        add_filter('wpseo_opengraph_url',function() {return false;});
        add_action('wpseo_add_opengraph_images', function($object) use (&$images){ foreach ($images as $image) { $object->add_image( $image->src ) ; } } );

        add_filter('rank_math/frontend/title',function($a) use (&$title){
        return $title;
        });
        add_filter('rank_math/frontend/description',function($a) use (&$description){
        return substr($description,0,400);
        });
        add_filter('rank_math/opengraph/pre_set_default_image', '__return_true' );
        add_filter('rank_math/opengraph/canonical',function($a){return ''; });
        add_filter('rank_math/opengraph/url',function($a){return ''; });
        add_filter('rank_math/frontend/show_keywords',function($a){return false; });
    }
    else{
      remove_action( 'wp_head', 'rel_canonical' );
      echo "\n". '<link rel="canonical" href="' . esc_url( $thisUrl, null, 'other' ) . '" />' . "\n";
      // echo 'else '.$thisUrl;
      echo '<meta property="og:url" content="'.$thisUrl.'"/>' . "\n";
      echo '<meta property="og:title" content="'.$title.'"/>' . "\n";
      echo '<meta property="og:description" content="'.wp_trim_words( $description, 400 ).'"/>' . "\n";
    }
  
  echo '<meta property="og:image" content="'.$images[0]->src.'"/>' . "\n";
  echo '<meta property="og:image:src" content="'.$images[0]->src.'"/>' . "\n";
  echo '<meta property="og:image:width" content="640"/>' . "\n";
  echo '<meta property="og:image:height" content="480"/>' . "\n";
  echo '<meta name="twitter:card" content="summary_large_image"/>' . "\n";
  echo "\n".'<meta name="twitter:image" content="http://109.228.15.38/webkit_image.php?src='.$images[0]->src.'" />';   
}






/*
** INMOLINK CUSTMIZATION TO ADD CORRECT CANONICAL URL RANK MATH SEO
** Desc - if this custmization remove then all seo link for social sharing will be lost 
** @author - NIKHIL BAKODIA
**/
add_filter( 'rank_math/frontend/canonical', function( $canonical ) {
  global $wp;
  $thisUrl = home_url( $wp->request );  
  return $thisUrl;
});


function redirectIfPropertyEmpty() { 
  $classes = get_body_class();
  if (in_array('et-fb',$classes)) {

  } else {

  global $wp;
  $thisUrl = home_url( $wp->request );
  $uri_segments = explode('/', $thisUrl);

  if( have_rows('property_listing' , 'option') ): 
    while( have_rows('property_listing' , 'option') ): the_row();
      $langSlug = get_sub_field('lang_slug', 'option');
      if (pll_current_language() == $langSlug ) {
        if( !isset($_GET['ref_no']) && $_GET['ref_no'] =='' ){
            if (pll_current_language() == 'en' ) {
                $detailPage = get_sub_field('detail_page_url', 'option');
                if($uri_segments[3] == $detailPage){
                    if( !isset($uri_segments[4]) && $uri_segments[4] == ''){
                      echo '<script type="text/javascript">window.location.href="'.site_url().'";</script>';
                    }
                }
            }

            if (pll_current_language() == 'es' ) {
                $detailPage = get_sub_field('detail_page_url', 'option');
                $esUriSegment = $uri_segments[3].'/'.$uri_segments[4];
                if ($esUriSegment == $detailPage) {
                    if( !isset($uri_segments[5]) && $uri_segments[5] == ''){
                      echo '<script type="text/javascript">window.location.href="'.site_url().'";</script>';
                    }
                }
            }
        }     
      }
    endwhile; 
  endif;    

  }
}
add_action('wp_head', 'redirectIfPropertyEmpty');

/* CUSTMIZATION END */

add_action('init', 'register_script');
function register_script() {
    $v = "20200826";
    wp_register_style( 'Stylesheet', plugins_url( '/assets/css/style.css', __FILE__ ), array(), $v, 'all' );
    wp_register_style( 'lightslider', plugins_url( '/assets/css/lightslider.css', __FILE__ ), array(), $v, 'all' );
    wp_register_style( 'magnific-popup', plugins_url( '/assets/css/magnific-popup.css', __FILE__ ), array(), $v, 'all' );
    wp_register_script( 'js_cookie', plugins_url( '/assets/js/js.cookie.js', __FILE__ ), array(), $v );
    wp_register_script( 'validate_min', plugins_url( '/assets/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), $v );
    wp_register_script( 'number_min', plugins_url( '/assets/js/jquery.number.min.js', __FILE__ ), array( 'jquery' ), $v );
    wp_register_script( 'ajaxHandle', plugins_url('/assets/js/inmolink-ajax.js', __FILE__), array('jquery'), $v );
    wp_localize_script( 'ajaxHandle', 'myAjax',  array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    wp_register_script( 'lightslider_script', plugins_url( '/assets/js/lightslider.js', __FILE__ ), array( 'jquery' ), $v );
    wp_register_script( 'custom_script', plugins_url( '/assets/js/inmolink-custom.js', __FILE__ ), array( 'jquery', 'js_cookie' ), $v );
    wp_register_script( 'jquery_magnific_popup', plugins_url( '/assets/js/jquery.magnific-popup.js', __FILE__ ), array( 'jquery' ), $v );
}

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'enqueue_style');
function enqueue_style(){
  wp_enqueue_style( 'Stylesheet');
  wp_enqueue_style( 'lightslider');
  wp_enqueue_style( 'magnific-popup');

  wp_enqueue_script( 'validate_min');
  wp_enqueue_script( 'number_min');
  wp_enqueue_script( 'ajaxHandle');
  wp_enqueue_script( 'lightslider_script');
  wp_enqueue_script( 'custom_script');
  wp_enqueue_script( 'jquery_magnific_popup');
  wp_enqueue_script( 'js_cookie');
  wp_enqueue_script( 'vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.min.js' );
}

if( is_admin() ) {
  $my_settings_page = new InmolinkSettings();
}

add_shortcode("inmolink_property_search_form", array('InmoLinkSearch','form_shortcode'));

add_shortcode('inmolink_property_results',array('InmoLinkResults','properties_shortcode'));
add_shortcode('inmolink_properties',array('InmoLinkResults','properties_shortcode'));
add_shortcode('inmolink_introtext',array('InmoLinkResults','introtext_shortcode'));
add_shortcode('inmolink_noresults',array('InmoLinkResults','noresults_shortcode'));
add_shortcode('inmolink_property',array('InmoLinkResults','property_shortcode'));

add_shortcode("inmolink_property_contact_form", array('InmoLinkContact','contactform_shortcode'));
add_shortcode("inmolink_property_contact_field", array('InmoLinkContact','contactform_field'));

/*
* Create custom post type for listing type
*/
add_action( 'init', 'activate_inmolink_listing_plugin' );
function activate_inmolink_listing_plugin() {
    register_post_type( 'inmolink_listing',
        array(
            'labels' => array(
                'name' => 'Inmolink Listing',
            ),
            'public' => true,
            'menu_icon' => 'dashicons-location-alt',
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => false, // Removes the "Add New" function
                'edit_posts' => false,
            ),
            'map_meta_cap' => false,
            'taxonomies' => array(''),
            'has_archive' => true
        )
    );
}

/*
* Create custom taxonomy for listing types
*/
add_action( 'init', 'activate_inmolink_create_types_custom_taxonomy', 0 );
function activate_inmolink_create_types_custom_taxonomy() {
  $labels = array(
    'name' => _x( 'Types', 'taxonomy general name' ),
    'parent_item' => __( 'Parent Type' ),
    'parent_item_colon' => __( 'Parent Type:' ),
    'edit_item' => __( 'Edit Type' ),
    'add_new_item' => __( 'Add New Type' ),
    'menu_name' => __( 'Types' ),
  );
  register_taxonomy('types',array('inmolink_listing','page'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'rewrite' => array( 'slug' => 'type' ),
  ));
}

/*
* Create custom taxonomy for listing location
*/
add_action( 'init', 'activate_inmolink_listingLocation_custom_taxonomy' );
function activate_inmolink_listingLocation_custom_taxonomy() {
  $labels = array(
    'name' => _x( 'Locations', 'taxonomy general name' ),
    'parent_item' => __( 'Parent Location' ),
    'parent_item_colon' => __( 'Parent Location:' ),
    'edit_item' => __( 'Edit Location' ),
    'add_new_item' => __( 'Add New Location' ),
    'menu_name' => __( 'Locations' ),
  );
  register_taxonomy('locations',array('inmolink_listing','page'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'rewrite' => array( 'slug' => 'location' ),
  ));
}

/*
* Create custom taxonomy for listing features
*/
add_action( 'init', 'activate_inmolink_listingFeature_custom_taxonomy' );
function activate_inmolink_listingFeature_custom_taxonomy() {
  $labels = array(
    'name' => _x( 'Features', 'taxonomy general name' ),
    'parent_item' => __( 'Parent Feature' ),
    'parent_item_colon' => __( 'Parent Feature:' ),
    'edit_item' => __( 'Edit Feature' ),
    'add_new_item' => __( 'Add New Feature' ),
    'menu_name' => __( 'Features' ),
  );
  register_taxonomy('features',array('inmolink_listing','page'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'rewrite' => array( 'slug' => 'features' ),
  ));
}

add_action('init','inmolink_rewrite_rule', 10, 0);
function inmolink_rewrite_rule()
{
  add_rewrite_tag('%ref_no%', '([^&]+)');

  $languages = inmolink_get_languages();
  foreach($languages as $language){
    add_rewrite_rule('^'.$language['dir'].$language['single_property_slug'].'/([^_]+)','index.php?pagename='.$language['single_property_slug'].'&ref_no=$matches[1]','top');
  }
}

/*
* Activation Hook for CPT and all Taxonomies
*/
function inmolink_plugin_flush_rewrites() {
  activate_inmolink_listing_plugin();
  flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'inmolink_plugin_flush_rewrites' );

/*
* Deactivation Hook for CPT
*/
function inmolink_plugin_uninstall() {
  unregister_post_type( 'inmolink_listing' );
}
register_uninstall_hook( __FILE__, 'inmolink_plugin_uninstall' );

/*
* create function for add meta field
*/
add_action( 'created_locations', 'save_location_meta', 10, 2 );
function save_location_meta( $term_id, $tt_id ){
  if( isset( $_POST['location_type'] ) && ’ !== $_POST['location_type'] ){
    $type = sanitize_title( $_POST['location_type'] );
    add_term_meta( $term_id, 'location_type', $type, true );
  }
}

/*
* create function for edit meta field
*/
add_action( 'locations_edit_form_fields', 'edit_locations_field', 10, 2 );
function edit_locations_field( $term, $taxonomy ){
  $location_type = get_term_meta( $term->term_id, 'location_type', true ); ?>
  <tr class="form-field term-group-wrap">
    <th scope="row">
      <label for="type-location"><?php _e( 'Type', 'inmolink' ); ?></label>
    </th>
    <td>
      <input type="text" class="postform" id="type-location" name="location_type" value="<?php echo $location_type; ?>">
    </td>
  </tr>
  <?php
}

/*
* create function for update meta field
*/
add_action( 'edited_locations', 'update_location_meta', 10, 2 );
function update_location_meta( $term_id, $tt_id ){
  if( isset( $_POST['location_type'] ) && ’ !== $_POST['location_type'] ){
    $group = sanitize_title( $_POST['location_type'] );
    update_term_meta( $term_id, 'location_type', $group );
  }
}

/*
* create function for add column in taxonomy section
*/
add_filter('manage_edit-locations_columns', 'add_locations_column' );
function add_locations_column( $columns ){
  $columns['location_type'] = __( 'Type', 'inmolink' );
  $columns['location_remote_id'] = __( 'Remote Id', 'inmolink' );
  return $columns;
}
/*
* create function for display list of type meta field
*/
add_filter('manage_locations_custom_column', 'add_locations_column_content', 10, 3 );
function add_locations_column_content( $content, $column_name, $term_id ){
  $term_id = absint( $term_id );
  if( $column_name == 'location_type' ){
      $location_type = get_term_meta( $term_id, 'location_type', true );
      if( !empty( $location_type ) ){
        $content .= esc_attr( $location_type );
      }
  }
  if( $column_name == 'location_remote_id' ){
      $location_remote_id = get_term_meta( $term_id, 'location_id', true );
      if( !empty( $location_remote_id ) ){
        $content .= esc_attr( $location_remote_id );
      }
  }
  return $content;
}
/*
* create function for import location
*/
function inmolink_import_location(){
  $post = array('limit'=>1000);
  $inmolinkDataLocation = inmolink_fetch_properties('GET', 'v2/location' , $post);
  $locData = $inmolinkDataLocation->data;
  foreach ($locData as $key => $value) {
    $api_id = $value->id;
    $api_name = $value->name;
    $api_type = $value->type;
    $api_parent_id = $value->parent_id;

    $terms = get_terms(array(
        'taxonomy' => 'locations',
        'hide_empty' => false, // also retrieve terms which are not used yet
        'meta_query' => array(
            array(
                'key' => 'location_id',
                'value' => $api_id,
                'compare' => '='
            )
        )
    ));

    if(!empty($terms))
    {
        //skip if location already exists
        continue;
    }

    $args = array(
    'taxonomy' => 'locations',
    'hide_empty' => false, // also retrieve terms which are not used yet
    'meta_query' => array(
      array(
         'key' => 'location_id',
         'value' => $api_parent_id,
         'compare' => '='
        )
      )
    );
    $terms = get_terms($args);
    if (!empty($terms)){

      $parent_term_id = $terms[0]->term_id;
      $location = wp_insert_term(
        $api_name,   // the term
        'locations', // the taxonomy
        array(
          'description' => '',
          'parent' => $parent_term_id,
          'slug' => $api_name,
        )
      );
      $lastid = $location['term_id'];
      update_term_meta($lastid, 'location_type', $api_type);
      update_term_meta($lastid, 'location_id', $api_id);
    } else {
      $location = wp_insert_term(
        $api_name,   // the term
        'locations', // the taxonomy
        array(
          'description' => '',
          'parent' => 0,
          'slug' => $api_name,
        )
      );

      if(is_wp_error($location))
      {
        wp_die("There was an error importing $api_name");
        break;
      }

      $lastid = $location['term_id'];
      update_term_meta($lastid, 'location_type', $api_type);
      update_term_meta($lastid, 'location_id', $api_id);
    }
  }
}

/*
* create function for import features
*/
function inmolink_import_features()
{
  global $polylang;
  $languages = inmolink_get_languages();
  foreach($languages as $ln => $args)
  {
    $locale = $args['locale'];

    $data = array(
        'ln' => $locale,
        'limit' => 1000
    );
    $typesData = inmolink_fetch_properties('GET', 'v1/property_features' , $data);

    foreach ($typesData->data as $type)
    {
      $taxonomy = 'features';
      $name = $type->name;
      $meta_key = 'category_id';
      $meta_value = $type->id;

      $args = array(
          'taxonomy' => $taxonomy,
          'hide_empty' => false,
          'meta_key' => $meta_key,
          'meta_value' => $meta_value,
          'lang' => $ln
      );

      $terms = get_terms( $args );
      
      if(empty($terms))
      {
        $args['slug'] = sanitize_title($name . '_' . $ln);
        $term = wp_insert_term(
            $name, // the term
            $taxonomy, // the taxonomy
            $args
        );
        if(!is_wp_error($term)){
          $term_id = $term['term_id'];
          update_term_meta($term_id, $meta_key, $meta_value);

          if(isset($polylang))
            $polylang->model->term->set_language($term_id, $ln);

          $parent_id = $term_id;
          $parent_slug = sanitize_title($name);
        } else {
          $parent_id = 0;
        }
      }
      else
      {
        $parent_id = $terms[0]->term_id;
        $parent_slug = sanitize_title($terms[0]->name);
      }

      if($parent_id){
        foreach($type->value_ids as $feature)
        {
          $name = $feature->name;
          $meta_key = 'feature_id';
          $meta_value = $feature->id;

          $args = array(
              'taxonomy' => $taxonomy,
              'hide_empty' => false,
              'meta_key' => $meta_key,
              'meta_value' => $meta_value,
              'lang' => $ln
          );

          $terms = get_terms( $args );

          if(empty($terms))
          {
              $args['parent'] = $parent_id;
              $args['slug'] = sanitize_title($parent_slug.'_'.$name . '_' . $ln);
              $term = wp_insert_term(
                  $name, // the term
                  $taxonomy, // the taxonomy
                  $args
              );
              if(!is_wp_error($term)){
                  $term_id = $term['term_id'];
                  update_term_meta($term_id, $meta_key, $meta_value);

                  if(isset($polylang))
                      $polylang->model->term->set_language($term_id, $ln);
              }
          }

        }
      }
      
    }
  }
}

/*
* create function for import types
*/
function inmolink_import_type()
{
    global $polylang;
    $languages = inmolink_get_languages();
    foreach($languages as $ln => $args)
    {
        $locale = $args['locale'];

        $data = array(
            'ln' => $locale,
            'limit' => 1000
        );
        $typesData = inmolink_fetch_properties('GET', 'v1/property_types' , $data);

        foreach ($typesData->data as $type)
        {
            $taxonomy = 'types';
            $name = $type->name;
            $meta_key = 'type_id';
            $meta_value = $type->id;
            $remote_parent_id = $type->parent_id;
            $local_parent_id = 0;

            if($remote_parent_id){
                $args = array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'meta_key' => $meta_key,
                    'meta_value' => $remote_parent_id,
                    'lang' => $ln
                );

                $terms = get_terms( $args );
                if(!empty($terms)){
                    $local_parent_id = $terms[0]->term_id;
                }
            }

            $args = array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value,
                'lang' => $ln
            );

            $terms = get_terms( $args );

            $args['parent'] = $local_parent_id;

            if(empty($terms))
            {
                $args['slug'] = sanitize_title($name . '_' . $ln);
                $term = wp_insert_term(
                    $name, // the term
                    $taxonomy, // the taxonomy
                    $args
                );
                if(!is_wp_error($term)){
                    $term_id = $term['term_id'];
                    update_term_meta($term_id, $meta_key, $meta_value);

                    if(isset($polylang))
                        $polylang->model->term->set_language($term_id, $ln);
                }
            }
            else
            {
                wp_update_term($terms[0]->term_id,$taxonomy,$args);
            }
        }
    }
}

function inmolink_get_languages()
{
    global $polylang;
    $return  = array();
    $settings = get_option('inmolink_option_name');

    if (isset($polylang))
    {
      $hide_default = $polylang->links_model->model->options['hide_default'];
      $default_lang = $polylang->links_model->model->options['default_lang'];

      $languages = $polylang->model->get_languages_list();

      foreach($languages as $language){
          $slug = (string)$language->slug;
          $locale = (string)$language->locale;

          if($hide_default && $slug == $default_lang)
          {
            $dir = '';
            $page = 'property';
          }
          else
          {
            $dir = $slug.'/';
            $page = 'property-'.$slug;
          }

          $return[$slug] = array(
              'locale' => $locale,
              'dir' => $dir,
              'single_property_slug' => isset($settings['single_property_slug'][$slug]) ? $settings['single_property_slug'][$slug] : $page
          );
      }
    }
    else
    {
        $locale = get_option('WPLANG',get_locale());
        list($slug,)=explode('_',$locale,2);

        $return[$slug]  = array(
            'locale' => $locale
        );
    }
    return $return;
}

add_action("wp_ajax_inmolink_ajaxsearch", "inmolink_ajaxsearch");
add_action("wp_ajax_nopriv_inmolink_ajaxsearch", "inmolink_ajaxsearch");

function inmolink_ajaxsearch(){
  parse_str($_POST['get_args'], $_GET);
   
  if(isset($_POST['priceToggle'])){
    if( have_rows('property_search' , 'option') ){ 
      while( have_rows('property_search' , 'option') ): the_row();
          $lang_slug = get_sub_field('lang_slug', 'option');
          $sale_price = get_sub_field('sale_price', 'option');
          $rent_price = get_sub_field('rent_price', 'option');
          $holiday_price = get_sub_field('holiday_price', 'option');
          if (pll_current_language() == $lang_slug ) {
            if( have_rows('labels' , 'option') ){ 
              while( have_rows('labels' , 'option') ): the_row();
                $label_8 = get_sub_field('min_price', 'option');
                $label_9 = get_sub_field('max_price', 'option');
                $status = $_POST['status'];
                
                $getStatus = new InmoLinkSearch();

                $getPriceOf = $sale_price;
                if($status == 'resale'){ $getPriceOf = $sale_price; }
                elseif ($status == 'development'){ $getPriceOf = $sale_price; }
                elseif ($status == 'long_rental'){ $getPriceOf = $rent_price; }
                elseif ($status == 'short_rental'){ $getPriceOf = $holiday_price; }

                $list_price_min = array(
                  'field'=>"list_price_min_ajax",
                  'label'=>$label_8,
                  'data'=>$getPriceOf,
                  'format'=>"€%s",
                  'thousands'=>","
                );

                $list_price_max = array(
                  'field'=>"list_price_max_ajax",
                  'label'=>$label_9,
                  'data'=>$getPriceOf,
                  'format'=>"€%s",
                  'thousands'=>","
                );

                $pricesToggled = array(
                'list_price_min' => $getStatus->field($list_price_min),
                'list_price_max' => $getStatus->field($list_price_max)
                );

                echo json_encode($pricesToggled);

              endwhile;
            }   
          }
      endwhile; 
    }
    wp_die();  
  }
  else{
    $results = new InmoLinkResults();
    
    $args = $results->parseGetParams();
    $args = array_filter($args);
	echo '<pre>';
	print_r($args);
    $results->fetch_properties($args);

    $stats = array(
      'count' => $results->count,
      'params' => $args,
      'get' => $_GET
    );
    echo json_encode($stats);
    wp_die();
  }
}

/*
* Create Function to contact form Ajax
*/
add_action("wp_ajax_inmolink_propertydetail_datapost", "inmolink_propertydetail_formdata");
add_action("wp_ajax_nopriv_inmolink_propertydetail_datapost", "inmolink_propertydetail_formdata");

function inmolink_propertydetail_formdata() {

    parse_str($_POST['formData'], $formData);//This will convert the string to array

    $inmolinkData = inmolink_fetch_properties('POST', 'v1/contact' , $formData);

    if($inmolinkData->data->contact_ref != 'None')
    {
        echo '<span style="color:green">'.__('Your message was sent successfully!','inmolink').'</span>';
    }
    else
    {
        echo '<span style="color:red">'.__('En error ocurred, please try again later.','inmolink').'</span>';
    }
    wp_die();
}

/*
* Create Function to locate Template
*/
function inmolink_locate_template( $template_name, $template_path = '', $default_path = '' ) {
  // Set variable to search in woocommerce-plugin-templates folder of theme.
  if ( ! $template_path ) :
    $template_path = 'inmolink/';
  endif;

    // Set default plugin templates path.
    if ( ! $default_path ) :
      $default_path = plugin_dir_path( __FILE__ ) . 'templates/'; // Path to the template folder
    endif;
    // Search template file in theme folder.
    $template = locate_template( array(
      $template_path . $template_name,
      $template_name
    ) );
  // Get plugins template file.
    if ( ! $template ) :
    $template = $default_path . $template_name;
  endif;
  return apply_filters( 'inmolink_locate_template', $template, $template_name, $template_path, $default_path );
}

/**
 * Get template.
 * Search for the template and include the file.
 * @see inmolink_locate_template()
 *
 */
function inmolink_get_template($template_name, $args = array(), $tempate_path = '', $default_path = '' )
{
    if(!is_array($args))
        return;

    extract($args);

    $template_file = inmolink_locate_template( $template_name, $tempate_path, $default_path );
    if ( ! file_exists( $template_file ) ) {
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
        return;
    }
    include $template_file;
}


add_filter('pll_translation_url', 'inmolink_pll_translation_url', 10, 2);
function inmolink_pll_translation_url($url, $ln) {
  $refs = get_query_var('ref_no');
  if(empty($refs))
    return $url;
  
  $url .= strpos($url,'?') === false ? '?' : '&';
  $url .= 'ref_no=' . $refs;

  return $url;
}