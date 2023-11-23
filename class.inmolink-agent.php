<?php
class InmolinkAgent
{

    public function __construct(int $id = NULL)
    {
        if(is_null($id))
            return;

        $this->id = $id;
        $data = inmolink_fetch_properties('GET', 'v1/agent' , ['id'=>$this->id]);
        $this->data = $data->data[0];
        add_shortcode('inmolink_agent_detail',[$this,'agent_detail']);
        add_shortcode('inmolink_agent_link',[$this,'agent_link']);
    }

    function agent_detail($atts=array(),$content=NULL){
        $defaults = [
            'field' => ''
        ];
        $atts = shortcode_atts($defaults, $atts);
        return isset($this->data->{$atts['field']}) ? $this->data->{$atts['field']} : '';
    }

    function agent_link($atts=array(),$content=NULL){
        $defaults = [
            'field' => 'website',
            'target' => '_blank',
            'class' => ''
        ];
        $atts = shortcode_atts($defaults, $atts);

        $return = '';
        if(isset($this->data->{$atts['field']})){
            $return .= '<a ';
            $return .= 'href="'.$this->data->{$atts['field']}.'" ';
            $return .= 'class="'.$atts['class'].'" ';
            $return .= 'target="'.$atts['target'].'" ';
            $return .= '>'.do_shortcode($content).'</a>';
        }
        return $return;
    }
}
