<?php

/**
 * 
 */
class InmoLinkSearch
{
    static public function form_shortcode($atts = array(), $content = NULL)
    {  
        $form = new self();

        $defaults = array (
            'class' => '',
            'action' => '',
            'method' => 'get',
            'addurlparams' => '',
        );
        $atts = shortcode_atts($defaults,$atts);

        add_shortcode("inmolink_property_search_field", array($form, 'field'));

        $return = '<form id="searchform" '.
            'class="'.$atts['class'].'" '.
            'action="'.$atts['action'].'" '.
            'method="'.$atts['method'].'" '.
            '>';

        $fields = do_shortcode($content);

        // add hidden fields with currently searched parameters to allow filtering
        if( $atts['addurlparams'] != '' ) {
            foreach($_REQUEST as $k => $v) {

                //skip fields which are already added to the form
                if(strpos($fields, 'name="'.$k))
                    continue;

                if(!is_array($v)) {
                    $return .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
                } else {
                    foreach($v as $v2) {
                        $return .= '<input type="hidden" name="'.$k.'[]" value="'.$v2.'" />';
                    }
                }
            }
        }

        $return .= $fields;
        $return .= '</form>';

        return $return;
    }    

    public function field($atts = array(), $content = NULL )
    {
        $atts2 = $atts;
        $defaults = array (
            'field' => '',
            'label' => '',
            'data' => '',
            'thousands' => ',',
            'slug' => '',
            'parent' => '',
            'class' => ''
        );
        $atts = shortcode_atts($defaults,$atts);
    
        $script = "";

        $postfield = $atts['field'];
        $postlabel = $atts['label'];
        $postdata = $atts['data'];

		foreach($atts2 as $k => $v){
			if(strpos($k,'data_')===0)
			{
				list($field,$value) = explode('__',substr($k,5),2);
				$d = explode(',',$v);

				if(isset($_GET[$field]) && $_GET[$field]==$value)
					$postdata = $v;

				$options_string = '<option value="">'.$postlabel.'</option>';
				foreach($d as $v2){
					$k2 = $v2;
					$options_string .= '<option value="'.$k2.'">'.$v2.'</option>';
				}

				$script .= '<script>
				jQuery("[name=\''.$field.'\']").change(function(){
					var value = jQuery(this).children(\'option:selected\').first().val();
					console.log(value);
					if(value == "'.$value.'"){
						jQuery("[name=\''.$postfield.'\']").children("option").remove();
						jQuery("[name=\''.$postfield.'\']").append(\''.$options_string.'\');

						if(typeof(jQuery("select[name=\''.$postfield.'\']").multiselect) == "function"){
							jQuery("select[name=\''.$postfield.'\']").multiselect("refresh");
						}
					}
				});
				</script>';
			}
		}
    
        $dispalydata = '';
    
        if($postfield == 'ref_no'){
            $dispalydata .='<input type="text" placeholder="'.$postlabel.'" id="ref_no" name="ref_no" value="">';
        }

        if($postfield == 'listing_type'){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="listing_type">';
    
            if(!empty($postlabel))
                $dispalydata .='<option value="">'.$postlabel.'</option>';
    
            foreach ($postdata as $v)
            {
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        } 
    
        if($postfield == 'furnished'){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="furnished">';
    
            if(!empty($postlabel))
                $dispalydata .='<option value="">'.$postlabel.'</option>';
    
            foreach ($postdata as $v)
            {
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        } 
    
        if($postfield == 'location'){
			echo 'fddg';
            $args = array(
                'show_option_all'   => $postlabel,
                'option_none_value' => 0,
                'hide_empty'        => 0,
                'selected'          => isset( $_GET['location'] ) ? $_GET['location'] : '' ,
                'hierarchical'      => 1,
                'parent'            => '',
                'echo'              => 0,
                'name'              => 'location',
                'taxonomy'          => 'locations',
                'multiple'          => true,
                'hide_if_empty'     => false,
                'value_field'       => 'term_id',
            );

            if($atts['parent']!='' && $parent_term = get_term_by('slug', $atts['parent'], 'locations')){
                $args['parent'] = $parent_term->term_id;
                $args['option_none_value'] = $parent_term->term_id;
                $args['selected'] = isset( $_GET['location'] ) ? $_GET['location'] : $parent_term->term_id;
                $args['show_option_none'] = $parent_term->name;
                $args['show_option_all'] = '';
            }

            $dispalydata .= wp_dropdown_categories( $args );
        }

        if($postfield == 'type'){
            $args = array(
                'show_option_all'   => $postlabel,
                'option_none_value' => 0,
                'hide_empty'        => 0,
                'selected'          => isset( $_GET['type'] ) ? $_GET['type'] : '' ,
                'hierarchical'      => 1,
                'parent'            => '',
                'echo'              => 0,
                'name'              => 'type',
                'taxonomy'          => 'types',
                'multiple'          => true,
                'hide_if_empty'     => false,
                'value_field'       => 'term_id',
            );

            if($atts['parent']!='' && $parent_term = get_term_by('slug', $atts['parent'], 'types')){
                $args['parent'] = $parent_term->term_id;
                $args['option_none_value'] = $parent_term->term_id;
                $args['selected'] = isset( $_GET['type'] ) ? $_GET['type'] : $parent_term->term_id;
                $args['show_option_none'] = $parent_term->name;
                $args['show_option_all'] = '';
            }

            $dispalydata .= wp_dropdown_categories( $args );
        }

        if($postfield == 'feature' && $term = get_term_by('slug', $atts['slug'], 'features')){
            static $checkbox_id = 0;
            $checkbox_id++;

            if(!$postlabel)
                $postlabel = $term->name;

            $selected = '';
            if(is_array($_GET['features']) && in_array($term->term_id,$_GET['features']))
                $selected = ' checked ';

            $dispalydata .= '<span class="'.$atts['class'].'">';
            $dispalydata .= '<input id="feature_'.$checkbox_id.'" type="checkbox" name="features[]" '.$selected.' value="'.$term->term_id.'" >';
            $dispalydata .= '<label for="feature_'.$checkbox_id.'">'.$postlabel.'</label>';
            $dispalydata .= '</span>';
        }
    
        if($postfield == 'bedrooms_min'){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="bedrooms_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $v){
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
    
        if($postfield == 'bathrooms_min'){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="bathrooms_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $v){
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
    
        if($postfield == 'list_price_min' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="list_price_min" name="list_price_min">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }
    
        if($postfield == 'list_price_max' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<select id="list_price_max" name="list_price_max">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }

        /* ARJUN MUKATI CUSTMIZATON */
        if($postfield == 'list_price_min_ajax' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
        }
    
        if($postfield == 'list_price_max_ajax' && !empty($postdata)){
            $postdata = explode(',', $postdata);
            $dispalydata .='<option value="">'.$postlabel.'</option>';
            foreach ($postdata as $k){
                $v = number_format($k, 0, '',$atts['thousands']);
                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
        }
        /* ARJUN MUKATI CUSTMIZATON */

        if($postfield == 'order' && !empty($postdata))
        {
            $postdata = explode(',', $postdata);
            $dispalydata .='<select name="'.$postfield.'">
            <option value="">'.$postlabel.'</option>';
            foreach ($postdata as $v)
            {
                if(strpos($v,'|'))
                    list($k,$v) = explode('|',$v,2);
                else
                    $k = $v;

                $selected = isset($_GET[$postfield]) && $_GET[$postfield] == $k ? ' selected ' : ' ';
                $dispalydata .='<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            }
            $dispalydata .='</select>';
        }

        if($postfield == 'reset'){
            $dispalydata .= '<input type="reset" value="'.$atts['label'].'" class="'.$atts['class'].' inmoReset" />';
        }
    
        if($postfield == 'submit'){
            $dispalydata .= '<input type="submit" data-label="'.$atts['label'].'" value="'.$atts['label'].'" class="'.$atts['class'].' inmoSubmit" />';
        }

        $dispalydata .= $script ;

        return $dispalydata;
    }
}