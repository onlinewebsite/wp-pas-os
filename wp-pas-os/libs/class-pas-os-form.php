<?php
class MyForms {
    protected $_inputs;
    private $_method, $_action;

    public function __construct($method = NULL,$action = NULL,$before = NULL ,$after = NULL, $between = NULL, $disabel='',$id='pas-os-form')
    {
        $this->_method = $method;
        $this->_action = $action;
        $this->_before = $before;
        $this->_after = $after;
        $this->_disabel = $disabel;
        $this->_between = $between;
        $this->_id = $id;
    }
    public function addContent($content) {
        $this->_inputs[] = array (
                                'label' => '',
                                'type' => 'content',
                                'name' => '',
                                'option' => '',
                                'class' => '',
                                'value' => '',
                                'required' => '',
                                'placeholder' => '',
                                'multiple' => '',
                                'extra' => $content,
                                'labelbefore' => '',
                                'labelafter' => ''
        );
    }
    public function addInput($type,$name,$value,$required = NULL, $option=NULL, $label = NULL,$placeholder = NULL,$class=NULL,$extra = NULL, $labelbefore = NULL, $labelafter = NULL,$multiple=NULL) {
        $this->_inputs[] = array (
                                'label' => $label,
                                'type' => $type,
                                'name' => $name,
                                'option' => $option,
                                'class' => $class,
                                'value' => $value,
                                'required' => $required,
                                'placeholder' => $placeholder,
                                'multiple' => $multiple,
                                'extra' => $extra,
                                'labelbefore' => $labelbefore,
                                'labelafter' => $labelafter
        );
    }
    public function printForm() {
        $html = '';
        if($this->_method) $html = '<form action="'.$this->_action.'" id="'.$this->_id.'" method="'.$this->_method.'">';
        for ($i = 0; $i < count($this->_inputs); $i++) {
        if($this->_disabel) $this->_inputs[$i]['required'] = '';
        if($this->_before and !in_array($this->_inputs[$i]['type'],['hidden','submit','content'])) $html .= $this->_before;
            switch($this->_inputs[$i]['type']) {
                case "content":
                    $html .= ' '.$this->_inputs[$i]['extra'].'';
                    break;
                case "text":
                case "number":
                case "password":
                case "hidden":
                case "submit":
                case "email":
                    if($this->_inputs[$i]['label']) $html .= '<label for="owproman-'.$this->_inputs[$i]['name'].'">'.$this->_inputs[$i]['label'].'</label>';
                    if($this->_between) $html .= $this->_between;
                    $html .= '<input type="'.$this->_inputs[$i]['type'].'" ';
                    $html .= ' id="owproman-'.$this->_inputs[$i]['name'].'" ';
                    if($this->_inputs[$i]['class']) $html .= ' class="'.$this->_inputs[$i]['class'].'" ';
                    if($this->_inputs[$i]['required']) $html .= ' required="'.$this->_inputs[$i]['required'].'" ';
                    if($this->_disabel) $html .= ' disabled="'.$this->_disabel.'"';
                    $html .= ' name="'.$this->_inputs[$i]['name'].'" ';
                    $html .= ' value="'.$this->_inputs[$i]['value'].'" ';
                    $html.= ' placeholder="'.$this->_inputs[$i]['placeholder'].'" > ';
                    $html .= ' '.$this->_inputs[$i]['extra'].'';
                    break;
                case "editor":
                    if($this->_inputs[$i]['label']) $html .= '<label for="owproman-'.$this->_inputs[$i]['name'].'">'.$this->_inputs[$i]['label'].'</label>';
                    if($this->_between) $html .= $this->_between;
                    ob_start();
                     wp_editor( html_entity_decode($this->_inputs[$i]['value']) , $this->_inputs[$i]['name'] , array('textarea_rows'=>'5','drag_drop_upload'=>true, 'media_buttons'=> false) );
                    $html .=  ob_get_clean();
                    $html .= ' '.$this->_inputs[$i]['extra'].'';
                    break;
                case "textarea":
                    if($this->_inputs[$i]['label']) $html .= '<label for="owproman-'.$this->_inputs[$i]['name'].'">'.$this->_inputs[$i]['label'].'</label>';
                    if($this->_between) $html .= $this->_between;
                    $html .= '<textarea name="'.$this->_inputs[$i]['name'].'" id="owproman-'.$this->_inputs[$i]['name'].'"';
                    if($this->_inputs[$i]['required']) $html .= ' required="'.$this->_inputs[$i]['required'].'" ';
                    if($this->_disabel) $html .= ' disabled="'.$this->_disabel.'"';
                    if($this->_inputs[$i]['class']) $html .= ' class="'.$this->_inputs[$i]['class'].'" ';
                    $html.= ' placeholder="'.$this->_inputs[$i]['placeholder'].'" style="width:99%">'.$this->_inputs[$i]['value'].'</textarea>';
                    $html .= ' '.$this->_inputs[$i]['extra'].'';
                    break;
                case "radio":
                case "checkbox":
                     $html .= '<input type="hidden" name="'.$this->_inputs[$i]['name'].'" value="" />';
                    if($this->_inputs[$i]['label']) $html .= '<label for="owproman-'.$this->_inputs[$i]['name'].'">'.$this->_inputs[$i]['label'].'</label>';
                   $option =1;
                   foreach($this->_inputs[$i]['option'] as $formvalue=>$formlabel){
                    if($this->_between) $html .= $this->_between;
                    $html .= ' <label  class="form-check-label" for="'.$this->_inputs[$i]['name'].'-option-'.$option.'">'.$this->_inputs[$i]['labelbefore'].$formlabel.$this->_inputs[$i]['labelafter'].'<input type="'.$this->_inputs[$i]['type'].'" ';
                    $html .= ' id="'.$this->_inputs[$i]['name'].'-option-'.$option.'"';
                    if($this->_inputs[$i]['required']) $html .= ' required="'.$this->_inputs[$i]['required'].'" ';
                    if($this->_disabel) $html .= ' disabled="'.$this->_disabel.'"';
                    if($this->_inputs[$i]['type'] == 'radio') $html .= ' name="'.$this->_inputs[$i]['name'].'"';
                    else $html .= ' name="0'.$this->_inputs[$i]['name'].'[]"';
                    if($this->_inputs[$i]['class']) $html .= ' class="'.$this->_inputs[$i]['class'].'"';
                    if($this->_inputs[$i]['value'] AND in_array($formvalue , explode('|', $this->_inputs[$i]['value']))) $html .= ' checked="checked"';
                    $html .= ' value="'.$formvalue.'" > </label>';
                    $option ++;
                   }
                    $html .= ' '.$this->_inputs[$i]['extra'].'';
                    break;
                case "select":
                    if($this->_inputs[$i]['label']) $html .= '<label for="owproman-'.$this->_inputs[$i]['name'].'">'.$this->_inputs[$i]['label'].'</label>';
                    if($this->_between) $html .= $this->_between;
                    $html .= ' <select name="'.$this->_inputs[$i]['name'].'" class="'.$this->_inputs[$i]['class'].'" '.$this->_inputs[$i]['multiple'].' id="owproman-'.$this->_inputs[$i]['name'].'" ';
                    if($this->_inputs[$i]['required']) $html .= ' required="'.$this->_inputs[$i]['required'].'" ';
                    if($this->_disabel) $html .= ' disabled="'.$this->_disabel.'"';
                    $html .= ' > ';
                    if($this->_inputs[$i]['placeholder']) $html .= '<option value="">'.$this->_inputs[$i]['placeholder'].'</option>';
                   foreach($this->_inputs[$i]['option'] as $formvalue=>$formlabel){
                    $html .= '<option value="'.$formvalue.'" ';
                    if($this->_inputs[$i]['value'] AND in_array($formvalue , explode('|', $this->_inputs[$i]['value']))) $html .= ' selected="selected"';
                    $html .= ' >';
                    $html .= $formlabel.'</option>';
                   }
                    $html .= '</select>';
                    $html .= ' '.$this->_inputs[$i]['extra'].'';
                    break;
           }
                    $html .= "\n";
                   // $html .= "<input type='hidden' name='owproman_form[]' value='iii' />";
        if($this->_after and !in_array($this->_inputs[$i]['type'],['hidden','submit','content'])) $html .= $this->_after;
        }
          if($this->_method) $html .= '</form>';
        return $html;
    }
 }