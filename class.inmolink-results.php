<?php

/**
 *
 */
class InmoLinkResults
{
    function __construct()
    {
        $default = array(
            'api_base_url' => 'http://cp.inmolink.es/',
            'api_access_token' => ''
        );
        $settings = get_option('inmolink_option_name');
        if(!is_array($settings))
            $settings = array();

        $settings = array_merge($default, $settings);

        $this->api_base_url = trim($settings['api_base_url'],'/ ').'/';
        $this->api_access_token = $settings['api_access_token'];

        $this->reset();
    }

    public function reset()
    {
        $this->requestUrl = '';
        $this->count = 0;
        $this->page = 1;
        $this->i = 0;
        $this->results = array();
    }

    private function get_locale()
    {
        if(function_exists('pll_current_language'))
            $locale = pll_current_language('locale');
        else
            $locale = get_option('WPLANG',get_locale());

        return $locale;
	}

    public function fetch_properties($atts = array())
    {
        static $cache = array();
        
        if(!isset($atts['locale']))
            $atts['ln'] =  $this->get_locale();

        $url = $this->api_base_url . 'v1/property';
        $url.= '?'.http_build_query($atts);

        $this->requestUrl = $url;

        $transient_key = 'url_json_'.md5($url);

        //returned previously memory cached result
        if(isset($cache[$transient_key]))
        {
            $this->log("curl cached: $url");

            $return = json_decode($cache[$transient_key]);
        }
        else
        {
            $this->log("curl start: $url");

            $curl = curl_init();
    
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "access_token: ".$this->api_access_token),
                    "User-Agent: wp-inmolink-property",
                    "Accept: */*",
                    "WP-Inmolink-Token: ".md5("Inmolink" . microtime()),
                    "Referer: ".$_SERVER['SERVER_NAME'],
                    "Cache-Control: no-cache",
                    "Accept-Encoding: gzip, deflate, br"
                )
            );

            $responseData = curl_exec($curl);
            $json = json_decode($responseData);
    
            //if successful then save to cache and set transient
            if(!curl_errno($curl) && json_last_error() == JSON_ERROR_NONE)
            {
                $this->log($json);
                $cache[$transient_key] = $responseData;
            }
            else
            {
                $this->log("curl error: ".curl_error($curl));
            }
            
            curl_close($curl);
    
            $return = $json;
        }

        $this->results = is_array($return->data) ? $return->data : array();
        $this->count = isset($return->count) ? $return->count : 0 ;
        $this->page = isset($return->page) ? $return->page : 1 ;
        $this->pages = isset($return->pages) ? $return->pages : 0 ;
    }

    public function parseGetParams($atts = array(), $post_id = 0)
    {
        if($post_id == 0)
            $post_id = get_the_id();
        
        if(isset($_GET['il_page']) && is_numeric($_GET['il_page']))
        {
            $args['page'] = (int)$_GET['il_page'];
        }
        if(isset($_GET['order']))
        {
            $args['order'] = $_GET['order'];
        }
        $args['limit'] = $atts['perpage'];
        
        if(!empty($atts['shortlist']))
        {
            if(!isset($_COOKIE['shortlist']) || empty($_COOKIE['shortlist']))
                $args['ref_no'] = 'noref';
            else
                $args['ref_no'] = $_COOKIE['shortlist'];
            
            return $args;
        }
        
        /*
        * Filter locations
        */

        $term_ids = array();
        
        // keep backward compatibility with single slug parameter
        if(empty($atts['locations']) && !empty($atts['location']))
            $atts['locations'] = $atts['location'];

        if(!empty($atts['locations'])){
            $term_slugs = explode(',',$atts['locations']);

            foreach($term_slugs as $slug){
                $term = get_term_by('slug', $slug, 'locations');
                if($term !== false)
                    $term_ids[] = $term->term_id;
            }
        }

        if(empty($term_ids) && isset($_GET['location']))
        {
            $term_ids = $_GET['location'];
            if(!is_array($term_ids)){
                $term_ids = array($term_ids);
            }
        }
        if(empty($term_ids))
        {
            $term_ids = array_map( function($term){ return $term->term_id; }, wp_get_post_terms($post_id, 'locations') );
        }

        if(!empty($term_ids))
        {
            $remote_ids = array();

            foreach ($term_ids as $term_id) {
                if($remote_id = get_term_meta((int)$term_id, 'location_id', true))
                    $remote_ids[] = $remote_id;
                else
                {
                    $child_terms = get_term_children( $term_id, 'locations' );
                    foreach($child_terms as $child_term_id)
                    {
                        if($remote_id = get_term_meta((int)$child_term_id, 'location_id', true))
                            $remote_ids[] = $remote_id;
                    }
                }
            }

            $args['location_id'] = implode(',', $remote_ids);
        }

        /**
         * Filter property types
         */

        $term_ids = array();
        
        // keep backward compatibility with single slug parameter
        if(empty($atts['types']) && !empty($atts['type']))
            $atts['types'] = $atts['type'];

        if(!empty($atts['types'])){
            $term_slugs = explode(',',$atts['types']);

            foreach($term_slugs as $slug){
                $term = get_term_by('slug', $slug, 'types');
                if($term !== false)
                    $term_ids[] = $term->term_id;
            }
        }

        if(empty($term_ids) && isset($_GET['type']))
        {
            $term_ids = $_GET['type'];
            if(!is_array($term_ids)){
                $term_ids = array($term_ids);
            }
        }
        if(empty($term_ids))
        {
            $term_ids = array_map( function($term){ return $term->term_id; }, wp_get_post_terms($post_id, 'types') );
        }

        if(!empty($term_ids))
        {
            $remote_ids = array();
            foreach ($term_ids as $term_id) {
                if($remote_id = get_term_meta((int)$term_id, 'type_id', true))
                    $remote_ids[] = $remote_id;
                else
                {
                    $child_terms = get_term_children( $term_id, 'types' );
                    foreach($child_terms as $child_term_id)
                    {
                        if($remote_id = get_term_meta((int)$child_term_id, 'type_id', true))
                            $remote_ids[] = $remote_id;
                    }
                }
            }

            $args['type_id'] = implode(',', $remote_ids);
        }
        /**
         * Filter features
         */
        $term_ids = array();
        if(!empty($atts['features'])){
            $term_slugs = explode(',',$atts['features']);

            foreach($term_slugs as $slug){
                $term = get_term_by('slug', $slug, 'features');
                if($term !== false)
                    $term_ids[] = $term->term_id;
            }
        }

        if(empty($term_ids) && isset($_GET['features']))
        {
            $term_ids = $_GET['features'];
            if(!is_array($term_ids)){
                $term_ids = array($term_ids);
            }
        }
        if(empty($term_ids))
        {
            $term_ids = array_map( function($term){ return $term->term_id; }, wp_get_post_terms($post_id, 'features') );
        }

        if(!empty($term_ids))
        {
            $remote_ids = array();
            foreach ($term_ids as $term_id) {
                if($remote_id = get_term_meta((int)$term_id, 'feature_id', true))
                    $remote_ids[] = $remote_id;
                else
                {
                    $child_terms = get_term_children( $term_id, 'features' );
                    foreach($child_terms as $child_term_id)
                    {
                        if($remote_id = get_term_meta((int)$child_term_id, 'feature_id', true))
                            $remote_ids[] = $remote_id;
                    }
                }
            }

            $args['features'] = implode(',', $remote_ids);
        }

        if($refs = get_query_var('ref_no'))
        {
            $args['ref_no'] = $refs;
        }

        $params = array(
            'listing_type',
            'bedrooms_min',
            'bedrooms_max',
            'bathrooms_min',
            'bathrooms_max',
            'list_price_min',
            'list_price_max',
            'ownonly'
        );

        foreach ($params as $param)
        {
            if(isset($_GET[$param]) && !empty($_GET[$param]))
            {
                $args[$param] = $_GET[$param];
            }
        }

        return $args;
    }

    public static function property_shortcode($atts = array(), $content = NULL)
    {
        $results = new self();

        $defaults = array(
            'ref_no' => get_query_var('ref_no')
        );
        $defaults = shortcode_atts($defaults, $_GET);
        $atts = shortcode_atts($defaults, $atts);

        $results->fetch_properties($atts);
        if($results->count != 1){
            $results->reset();
        }
        add_shortcode('property_field',array($results,'property_field'));
        add_shortcode('inmolink_property_detail',array($results,'property_field'));
        add_shortcode('inmolink_property_result_field',array($results,'property_field'));
        add_shortcode('inmolink_shortlist_button',array($results,'shortlist_button'));
        add_shortcode('inmolink_property_detail_slider',array($results,'property_slider'));
        add_shortcode('inmolink_property_detail_gallery',array($results,'property_gallery'));
        add_shortcode('inmolink_property_detail_agentlogo',array($results,'property_agent_logo'));
        add_shortcode('inmolink_agent',array($results,'agent_details'));
        add_shortcode( 'inmolink_property_detail_map', array($results, 'property_location_map') );
        add_shortcode( 'inmolink_property_detail_features', array($results, 'property_featuressection') );

        if(empty($atts['ref_no']))
            return '';

        $return = '';
        if($pixel_url = $results->get_tracking_pixel_url())
            $return .= '<img src="'.$pixel_url.'" class="inmolink_pixel" />';
            
        $return .= do_shortcode($content);

        return $return;
    }

    public function agent_details($atts = array(), $content = NULL)
    { 
        
        $property = &$this->results[$this->i];
        
        if(!isset($property->agent_id) || !isset($property->agent_id->id) || (int)$property->agent_id->id == 0){
            return '';
        }

        $property_agent = new InmolinkAgent($property->agent_id->id);
        return do_shortcode($content);
    }

    public function get_tracking_pixel_url()
    {
        $property = &$this->results[$this->i];

        if(!isset($property) || !isset($property->ref_no) ||  !isset($property->id))
            return false;

        $url = $this->api_base_url . 'inmolink/property/'.$property->id.'/'.md5(time()).'/'.$property->ref_no.'.gif';
    
        return $url;
    }

    private function prepare_results_args($atts=array())
    {
        $defaults = array(
            'type' => '',
            'location' => '',
            'types' => '',
            'locations' => '',
            'features' => '',
            'pagination_class' => '',
            'listing_type' => '',
            'perpage' => 12,
            'shortlist' => is_int(array_search('shortlist',$atts)) ? '1' : '',
        );
        $args = $this->parseGetParams(shortcode_atts($defaults, $atts));

        // pass-through values will be sent directly as-is to the API
        $apiDefaults = array(
            'ref_no' => '',
            'bedrooms_min' => '',
            'bedrooms_max' => '',
            'bathrooms_min' => '',
            'bathrooms_max' => '',
            'list_price_min' => '',
            'list_price_max' => '',
            'order' => '',
            'ownfirst' => '',
            'ownonly' => '',
            'listing_type' => ''
        );

        $apiAtts = shortcode_atts($apiDefaults, $atts);

        // merge direct pass-through values with parsed values
        // user-submitted (non-empty) values will take preference over pass-through values
        // TODO: 
        // - also user submitted values also to override non-passthrough values in parseGetParams()
        $args = array_merge(array_filter($apiAtts),array_filter($args));

        $args = array_filter($args);
        return $args;
    }

    public function noresults_shortcode($atts = array(), $content = NULL)
    {
        $results = new self();
        $args = $results->prepare_results_args($atts);
        $results->fetch_properties($args);

        if(count($results->results) == 0){
            return do_shortcode($content);
        }
        return '';
    }
    
    public static function introtext_shortcode($atts = array(), $content = NULL)
    {
        $content = empty($content) ? "<p>At the moment we can offer you a selection ".
            "of #COUNT# #TYPE#s for sale in #LOCATION#. Although you will find some smaller ".
            "#LOCATION# #TYPE#s for as little as #MINPRICE#, the average price for #TYPE#s ".
            "for sale in or around #LOCATION# is #AVGPRICE#.</p><p>Browse through the #LOCATION# ".
            "#TYPE#s on this page and create a list of favourites to review later in more detail. ".
            "You can send the list to yourself or request more detailed information from one of our ".
            "#LOCATION# Estate agents.</p>" : $content;
        
        $vars = array(
            'TYPE' => '',
            'LOCATION' => '',
            'COUNT' => '',
            'MINPRICE' => '',
            'MAXPRICE' => '',
            'AVGPRICE' => '',
        );
        
        $results = new self();
        $args = $results->prepare_results_args($atts);
        $args['order'] = 'list_price_asc';
        $results->fetch_properties($args);
        if($results->count == 0){
            return '';
        }
        $vars['COUNT'] = $results->count;
        $price = $results->results[0]->list_price;
        $vars['MINPRICE'] = number_format((int)$price,0,'',',');
        $vars['AVGPRICE'] = number_format((int)$price*2.5,0,'',',');

        $results = new self();
        $args['order'] = 'list_price_desc';
        $price = $results->results[0]->list_price;
        $vars['MAXPRICE'] = number_format((int)$price,0,'',',');

        $post_id = get_the_ID();

        $locations = wp_get_post_terms( $post_id, 'locations', array('fields'=>'names') );
        $vars['LOCATION'] = implode(', ',$locations);

        $types = wp_get_post_terms( $post_id, 'types', array('fields'=>'names') );
        $vars['TYPE'] = implode(', ',$types);

        foreach($vars as $k => $v){
            $content = str_replace('#'.$k.'#',$v,$content);
        }
        return $content;
    }

    public static function properties_shortcode($atts = array(), $content = NULL)
    {
        $results = new self();
        $args = $results->prepare_results_args($atts);
        $results->fetch_properties($args);

        add_shortcode('property_field',array($results,'property_field'));
        add_shortcode('inmolink_property_result_field',array($results,'property_field'));
        add_shortcode('property_permalink',array($results,'permalink'));
        add_shortcode('inmolink_property_permalink',array($results,'permalink'));
        add_shortcode('inmolink_shortlist_button',array($results,'shortlist_button'));

        $return = '';
        for ($i=0; $i < count($results->results); $i++) {
            $results->i = $i;
            $return .= do_shortcode($content);
            // $return .= $results->results[$i]->ref_no;
        }

        if($results->pages > 1)
        {
            $return .= '<div class="'.$atts['pagination_class'].'" style="clear:both;">';
            $return .= paginate_links(array(
                'total' => $results->pages,
                'format' => '?il_page=%#%',
                'current' => $results->page,
                'mid_size' => 2
            ));
            $return .= '</div>';
        }
        return $return;
    }

    public function permalink($atts = array(), $content = NULL)
    {
        $property = &$this->results[$this->i];
        $defaults = array(
            'target' => '',
            'class'  => '',
            'rel'    => '',
            'href'   => 'property-details',
            'pretty' => '',
        );
        $atts = wp_parse_args( $atts, $defaults );

        $target = $atts['target'];
        $class = $atts['class'];
        $rel = $atts['rel'];
        $href = get_site_url().'/'.$atts['href'] . '/';

        if($atts['pretty'] != '')
            $href .= $property->ref_no.'_'.sanitize_title($property->type_id->name . ' ' . $property->location_id->name).'/';
        else
            $href .= '?ref_no='.$property->ref_no;

        $link = '<a href="'.$href.'" class="'.$class.'" rel="'.$rel.'" target="'.$target.'">'.do_shortcode($content).'</a>';
        return $link;
    }

    public function property_field($atts = array(), $content = NULL)
    {
        $property = &$this->results[$this->i];
        $l = $this->i;

       
        //language issue  
        $week = get_sub_field('per_week', 'option');
        $month = get_sub_field('per_month', 'option');
        $from = get_sub_field('from', 'option');
        if ($month == '' || $week == '' ) {
            if( have_rows('property_listing' , 'option') ): 
                while( have_rows('property_listing' , 'option') ): the_row();
                    $lang_slug = get_sub_field('lang_slug', 'option');
                    if (pll_current_language() == $lang_slug ) {
                        $month = get_sub_field('per_month', 'option');
                        $week = get_sub_field('per_week', 'option');
                        $from = get_sub_field('from', 'option');
                    }
                endwhile;
            endif;
        }

        //language issue
        
         $atts = shortcode_atts( array (
            'field'  => 'ref_no',
            'format'  => '%s',
            'maxlength' => '',
            'class'  => '',
            'from'  => $from.' ',
            'separator' => ' - ',
            'per_month' => ' / '.$month,
            'per_week' => ' / '.$week,
            'thousands' => ''
        ), $atts );

        $displaydata = '';

        if(isset($property->is_own) && $property->is_own == true)
        {
            $atts['class'] .= ' own';
        }

        if(isset($property->is_featured) && $property->is_featured == true)
        {
            $atts['class'] .= ' featured';
        }

        if(isset($property->is_onsale) && $property->is_onsale == true)
        {
            $atts['class'] .= ' onsale';
        }

        $field = $atts['field'];
        if($field == 'slider')
        {
        	$displaydata .= '<div id="inmolink-slider_'.$l.'" class="'.$atts['class'].'">';
        		$displaydata .= '<ul id="content-slider" class="content-slider">';
        			foreach($property->images as $value) {
        				$displaydata .= '<li style="background:none;">';
        					$displaydata .= '<span class="MainImage">';
        						$displaydata .= '<img data-src="'.$value->src.'" class="MainImage sliderHiddenSrc" alt="'.$value->name.'">';
        					$displaydata .= '</span>';
        				$displaydata .= '</li>';
        			}
        		$displaydata .= '</ul>';
        	$displaydata .= '</div>';
        }
        elseif($field == 'image')
        {
        	$displaydata .= '<span id="inmolink-image_'.$l.'" class="MainImage '.$atts['class'].'">';
        		$displaydata .= '<img src="'.$property->images[0]->src.'" class="MainImage" alt="'.$property->images[0]->name.'">';
        	$displaydata .= '</span>';
        }
        elseif($field == 'ref_no')
        {
            $displaydata .= $property->ref_no;
        }
        elseif($field == 'bedrooms' AND isset($atts['class']) AND $atts['class'] !='' AND $atts['class'] !='px-2 Bedrooms')
        {
            //if($property->bedrooms != 0){
                $displaydata .='<span class="'.$atts['class'].'">';
                $displaydata .=$property->bedrooms;
                $displaydata .='</span>';
            //}
        }
        elseif($field == 'pdf')
        {
        	$displaydata .= '<span id="inmolink-pdf_'.$l.'">';
        		$displaydata .= '<a href="'.$property->pdf.'" target="_blank" class="btn btn-floating btn-small hidden-sm bg_color btn_link pdf_btn waves-effect waves-light '.$atts['class'].'">PDF</a>';
        	$displaydata .= '</span>';
        }
        elseif( $field == 'seo_page_title' ){
            $displaydata .= $property->seo_info->page_title;
        }
        elseif( $field == 'seo_meta_title' ){
            $displaydata .= $property->seo_info->meta_title;
        }
        elseif( $field == 'seo_meta_keywords' ){
            $displaydata .= $property->seo_info->meta_keywords;
        }
        elseif( $field == 'seo_meta_description' ){
            $displaydata .= $property->seo_info->meta_description;
        }
        elseif($field == 'virtualtour' )
        {
            if(isset($property->virtual_tour_url) && $property->virtual_tour_url != '')
            {
                $url = (string)$property->virtual_tour_url;

                $matches = array();
                preg_match('~vimeo.com/(\d+)/?~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://player.vimeo.com/video/'.$matches[1];
                }
                preg_match('~(?:youtube\.com/watch[\?&]v=|youtu\.be/)([\w\-\_]+)~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://www.youtube.com/embed/'.$matches[1];
                }
                $displaydata .= '<span id="inmolink-virtualtour_'.$l.'" class="'.$atts['class'].'">';
                $displaydata .= '<iframe src="'.$url.'" width="100%" height="100%" ></iframe>';
                $displaydata .= '</span>';
            }
        }
        elseif($field == 'video' )
        {
            if(isset($property->video_url) && $property->video_url != '')
            {
                $url = (string)$property->video_url;

                $matches = array();
                preg_match('~vimeo.com/(\d+)/?~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://player.vimeo.com/video/'.$matches[1];
                }
                preg_match('~(?:youtube\.com/watch[\?&]v=|youtu\.be/)([\w\-\_]+)~',$url,$matches);
                if(isset($matches[1])){
                    $url = 'https://www.youtube.com/embed/'.$matches[1];
                }
                $displaydata .= '<span id="inmolink-video_'.$l.'" class="'.$atts['class'].'">';
                $displaydata .= '<iframe src="'.$url.'" width="100%" height="100%" ></iframe>';
                $displaydata .= '</span>';
            }
        }
        elseif(isset($property->{$field}) && !is_array($property->{$field}))
        {
            $value = $property->{$field};

            $style='';
            if($field == 'bedrooms' && $value ==0){
                $style='style="display:none;"';
            }

        	$displaydata .= '<span class="'.$atts['class'].'" '.$style.'>';
            
            $formatted = apply_filters('property_field_value',$value,$field,$atts,$property);

            if($field == 'list_price')
                $formatted = apply_filters('printed_price_value', $formatted, $value, $atts);

            // if {field}_2 is 0, it means field is a starting value
            if((int)$property->{$field} != 0 && isset($property->{$field.'_2'}) && (int)$property->{$field.'_2'}  != 0 )
            {
                $displaydata .= $atts['from'];
            }

            if($field == 'list_price'){
                $displaydata .= '<span '.($field == 'list_price' ? 'data-price="'.$value.'"' : '' ).' >';
            }

            if (filter_var($formatted, FILTER_VALIDATE_URL)) { 
                $displaydata .= '<img src="'.$formatted.'" />';
            }
            else{
                //$displaydata .= $formatted;
            }

            if($field == 'list_price'){
                $displaydata .= '</span>';
            }
            
            // if {field}_2 is present, append with separator
            if(isset($property->{$field.'_2'}) && (int)$property->{$field.'_2'} > (int)$property->{$field} )
            {
                $value = $property->{$field.'_2'};
                $formatted = apply_filters('property_field_value',$value,$field,$atts,$property);
                
                if($field == 'list_price')
                    $formatted = apply_filters('printed_price_value', $formatted, $value, $atts);

                $displaydata .= $atts['separator'];
                $displaydata .= '<span '.($field == 'list_price' ? 'data-price="'.$value.'"' : '' ).' >';
                $displaydata .= $formatted;
                $displaydata .= '</span>';
            }

            if($field == 'list_price')
            {
                if($property->listing_type == 'long_rental')
                    $displaydata .= $atts['per_month'];

                if($property->listing_type == 'short_rental')
                    $displaydata .= $atts['per_week'];
            }

        	$displaydata .= '</span>';
        }
        elseif(isset($property->{$field.'_id'}->name))
        {
        	$displaydata .= '<span class="'.$atts['class'].'">';
            $value = $property->{$field.'_id'}->name;
            $value = apply_filters('property_field_value',$value,$field,$atts,$property);

            $displaydata .= sprintf($atts['format'],$value);
        	$displaydata .= '</span>';
        }

        return $displaydata;

    }

    public function shortlist_button($atts=array(), $content=NULL)
    {
        $defaults = array(
            'class' =>	'',
            'add' =>	'&#9734;',
            'remove' =>	'&#9733;',
        );
        $atts = shortcode_atts($defaults, $atts);
        
        $property = &$this->results[$this->i];

        $class = 'add_to_shortlist btn btn-floating btn-small bg_color btn_link border-0 p-0 '.$atts['class'];
        $label_add = $atts['add'];
        $label_remove = $atts['remove'];
    
        $ref = (string)$property->ref_no;
    
        if(isset($_COOKIE['shortlist']) && in_array($ref,explode(',',$_COOKIE['shortlist'])))
            $label = $label_remove;
        else
            $label = $label_add;
    
        return "<button class='$class' data-ref='". $ref ."' data-label-add='".$label_add."' data-label-remove='".$label_remove."'>".$label."</button>";
    }

    public function property_gallery($atts=array(), $content=NULL)
    {
        $defaults = array(
        );
        $atts = shortcode_atts( $defaults, $atts);

        $data = array(
            'property' => $this->results[$this->i],
        );

        $template = 'property-detail-grid.php';

        ob_start();
        inmolink_get_template($template, $data);
        $return_data = ob_get_clean();
        return $return_data;
    }
    public function property_slider($atts=array(), $content=NULL)
    {
        $defaults = array(
        );
        $atts = shortcode_atts( $defaults, $atts);

        $data = array(
            'property' => $this->results[$this->i],
        );

        $template = 'property-detail-slider.php';

        ob_start();
        inmolink_get_template($template, $data);
        $return_data = ob_get_clean();
        return $return_data;
    }
    
    function property_agent_logo( $atts=array() )
    {
        $defaults = array(
            'class'	=>	'',
            'default' => '',
            'class_no_logo' => 'no_logo'
        );
        $atts = shortcode_atts($defaults, $atts);
        
        $property = $this->results[$this->i];
        
        $class = $atts["class"];
        
        if(!isset($property) || !isset($property->agent_id->logo) || empty($property->agent_id->logo) ){
            $class .= " ".$atts["class_no_logo"];
            $img = !empty($atts["default"]) ? '<img src="' . (string)$atts["default"] . '" />' : '';
        }else{
            $img = '<img src="' . (string)$property->agent_id->logo . '" />';
        }
    
        return '<div class="' . $class . '">'.$img.'</div>';
    }

    function property_location_map( $atts=array(), $content=NULL )
    {
        $data = array(
            'property' => $this->results[$this->i],
        );

        ob_start();
        inmolink_get_template('property-detail-map.php', $data);
        $return_data = ob_get_clean();
        return $return_data;
    }
    
    function property_featuressection( $atts=array(), $content=NULL )
    {
        $data = array(
            'property' => $this->results[$this->i],
        );

        ob_start();
        inmolink_get_template('property-detail-features.php', $data);
        $return_data = ob_get_clean();
        return $return_data;
    }

    public function pagination($atts)
    {
        $atts = array(
            'total' => $as->pages,
            'current' => $as->page,
            'show_all' => false,
            'mid_size' => $mid,
            'show' => 2
        );
        return paginate_links($args);
    }

    private function log($message)
    {
        if(is_super_admin() && !wp_doing_ajax())
            echo "<script>console.log(".json_encode($message).")</script>";
    }
}

add_filter('property_field_value',function($value,$field,$atts)
{
    if(!isset($atts['format']))
        return $value;
        
    return sprintf($atts['format'],$value);
},15,3);

add_filter('property_field_value',function($value,$field,$atts)
{
    if(is_numeric($value) && isset($atts['thousands']) && !empty($atts['thousands']))
    {
        $value = number_format($value, 0, '',$atts['thousands']);
    }
    return $value;
},10,3);

add_filter('property_field_value',function($value,$field,$atts)
{
    if(isset($atts['maxlength']) && intval($atts['maxlength']) > 0 )
    {
        $value = mb_substr($value,0,(int)$atts['maxlength']);
    }
    return $value;
},10,3);
