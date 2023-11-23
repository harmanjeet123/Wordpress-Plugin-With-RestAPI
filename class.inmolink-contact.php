<?php

/**
 * 
 */
class InmoLinkContact
{
    public function contactform_shortcode($atts = array(), $content = NULL)
    {
        $defaults = array(
            'class'
        );
        $atts = shortcode_atts($defaults, $atts);

        $refs = get_query_var('ref_no');
        if (empty($refs) && isset($_COOKIE['shortlist'])) {
            $refs = $_COOKIE['shortlist'];
        }

        return '<div id="contactform"></div><form id="property_contact" method="post" class="'.$atts['class'].'">
        <input type="hidden" name="ref_no" id="ref_no" value="'.$refs.'">'.do_shortcode($content).'</form>';
    }

    function contactform_field($atts = array())
    {
        $defaults = array(
            'field' => '',
            'label' => ''
        );
        $atts = shortcode_atts($defaults, $atts);

        $dispalydata = '';
        if($atts['field'] == 'name'){
            $dispalydata .='<input type="text" id="name" name="name" placeholder="'.$atts['label'].'">';
        }
        if($atts['field'] == 'email'){
            $dispalydata .='<input type="email" id="email" name="email" placeholder="'.$atts['label'].'">';
        }
        if($atts['field'] == 'phone'){
            $dispalydata .='<input type="text" id="phone" name="phone" placeholder="'.$atts['label'].'">';
        }
        if($atts['field'] == 'type'){
            $dispalydata .='<select id="type" name="type">
            <option value="details">Details</option>
            <option value="call">Call</option>
            </select>';
        } else {
            $dispalydata .='<input type="hidden" id="type" name="type" value="details">';
        }
        if($atts['field'] == 'comment'){
            $dispalydata .='<textarea id="comment" name="comment" placeholder="'.$atts['label'].'" style="height:200px"></textarea>';
        }
        if($atts['field'] == 'submit'){
            $dispalydata .='<input type="submit" value="'.$atts['label'].'">';
        }
        return $dispalydata;
    }
}