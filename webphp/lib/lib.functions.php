<?php 

/**
 * some mildly helpful functions.
 *
 * @author Kenrick Buchanan
 */


/**
 * creates html tag
 *
 * @param string $name 
 * @param array $options 
 * @param string $open 
 * @return string html
 * @author Kenrick Buchanan
 */

function tag($name, $options=null, $open=false)
{
    $attrs = '';
	if($options) $attrs = tag_options($options);
	return "<$name $attrs ".($open ? '>' : '/>');
}


/**
 * returns a open/close html tag pair
 *
 * @param string $name 
 * @param string $content 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function content_tag($name, $content, $options=null)
{
    $attrs = '';
	if($options) $attrs = tag_options($options);
	return "<$name $attrs>".$content."</$name>";
}

/**
 * returns tag attributes, escaped if necessary
 * autofill option will prepoulate control with
 * value from POST/GET
 * @param array $options 
 * @return array options
 * @author Kenrick Buchanan
 */

function tag_options($options=array())
{   
    if (empty($options)) {
        return;
    }
    $s = '';
    
    // by default, autofill is going to strip out html
    if (isset($options['autofill'])) {
        $filter = isset($options['filter']) ? $options['filter'] : FILTER_SANITIZE_STRING;
        $flag  = isset($options['flag']) ? $options['flag'] : NULL;
        switch (strtolower($options['autofill'])) {
            case 'get':
                if (filter_has_var(INPUT_GET, $options['name'])) {
                    $options['value'] = filter_var($_GET[$options['name']], $filter, $flag);
                }
                break;
            case 'post':
                if (filter_has_var(INPUT_POST, $options['name'])) {
                    $options['value'] = filter_var($_POST[$options['name']], $filter, $flag);
                }
                break;
            default:
                break;
        }
    }
    
    if(isset($options['value'])) {
       $options['value'] = htmlspecialchars($options['value'], ENT_QUOTES);
    }
    
    if (isset($options['name']) && !isset($options['id'])) {
       $options['id'] = to_css_id($options['name']);
    }
    
	foreach( $options as $key => $value ) {
		$s .= " $key=\"$value\"";
	}
	return $s;
}

/**
 * creates an img  tag
 *
 * @param string $src 
 * @param array $options 
 * @return string img tag
 * @author Kenrick Buchanan
 */

function image_tag($src, $options)
{
    $options['src'] = $src;
    return tag('img', $options);
}  


/**
 * creates a check box tag
 *
 * @param string $name 
 * @param string $value 
 * @param string $checked 
 * @param array $options 
 * @return string html tag
 * @author Kenrick Buchanan
 */

function check_box_tag($name, $value=1, $checked=false, $options=array())
{
	$options['type']  = 'checkbox';
	$options['name']  = $name;
	$options['value'] = $value;
	if($checked) $options['checked'] = 'checked';
	return tag('input',$options);
}

/**
 * returns html form tag end
 *
 * @return string html
 * @author Kenrick Buchanan
 */

function end_form_tag()
{
	return '</form>';
}

/**
 * returns an html file upload field
 *
 * @param string $name 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function file_field_tag($name, $options=array())
{
    $options['type'] = 'file';
	return text_field_tag($name, null, $options);
}

/**
 * returns an opening form tag
 *
 * @param string $url 
 * @param array $options 
 * @return string html tag
 * @author Kenrick Buchanan
 */

function form_tag($url, $options=array())
{
	$options['method'] = isset($options['method']) ? $options['method'] : 'post';		
	if ( isset($options['multipart']) )
	{
	    $options['enctype'] = 'multipart/form-data';
	    unset($options['multipart']);
	}

	$options['action'] = $url;		
	return tag('form', $options, true);
}

/**
 * returns a hidden field tag
 *
 * @param string $name 
 * @param string $value 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function hidden_field_tag($name,$value=null, $options=array())
{
    $options['type'] = 'hidden';
	return text_field_tag($name, $value, $options);
}

/**
 * returns image submit tag
 *
 * @param string $name 
 * @param string $src 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function image_submit_tag($name, $src, $options=array())
{
    $options['type'] = 'image';
    $options['src']  = $src;
	return text_field_tag($name, null, $options);
}


/**
 * returns password field html tag
 *
 * @param string $name 
 * @param string $value 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function password_field_tag($name, $value=null, $options=array())
{
	$options['type'] = 'password';
	return text_field_tag($name,$value,$options);
}


/**
 * returns radio button form tag
 *
 * @param string $name 
 * @param string $value 
 * @param string $checked 
 * @param array $options 
 * @return void
 * @author Kenrick Buchanan
 */

function radio_button_tag($name, $value=1, $checked=false, $options=array())
{
	$options['type']  = 'radio';
	$options['name']  = $name;
	$options['value'] = $value;
	if($checked) $options['checked'] = 'checked';
	return tag('input',$options);
}


/**
 * by default it thinks option_tags is a string of <option></option> tags
 * usually created using options_for_select();
 * but if you want you can just pass an array and it will call options_for_select()
 * for you on that array.
 *
 * @param string $name 
 * @param string $option_tags 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_tag($name,$option_tags=null,$options=array())
{
	$options['name'] = $name;
	if(isset($options['field_name'])) unset($options['field_name']);
	// cheating.
	if (is_array($option_tags)) {
	   $option_tags = options_for_select($option_tags);
	}
    if(isset($options['include_blank']) && $options['include_blank']) 
    {
        $option_tags = "<option value=''></option>".$option_tags;
        unset($options['include_blank']);
    }
	return content_tag('select',$option_tags,$options);
}        

/**
 * returns html tag, but with different input options
 *
 * @param string $type 
 * @param array $options 
 * @param string $prefix 
 * @param string $include_blank 
 * @param string $discard_type 
 * @param string $disabled 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_html($type, $options,$prefix=null,$include_blank=false,$discard_type=false,$disabled=false)
{
    $html  = "<select name='";
    $html .= $prefix ? $prefix : 'date' ;
    $html .= $discard_type ? '' : "[$type]";
    $html .= "'";
    $html .= $disabled ? "disabled='disabled" : '';
    $html .= ">\n";
    $html .= $include_blank ? "<option value=''></option>" : '';
    $html .= $options;
    $html .= "</select>\n";
    return $html;
}


/**
 * returns submit button html
 *
 * @param string $value 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function submit_tag($value,$options=array())
{
	$options['type']  = 'submit';
	$options['name']  = isset($options['name']) ? $options['name'] : 'commit';
	$options['value'] = $value;
	if (isset($options['confirm'])) $options['onclick']= "return confirm('".escape_javascript($options['confirm'])."')";
	unset($options['confirm']);
	return tag('input',$options);
}


/**
 * returns plain button tag
 *
 * @param string $value 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function button_tag($value,$options=array())
{
	$options['type'] = 'button';
	$options['name'] = 'commit';
	$options['value'] = $value;
	return tag('input',$options);
}

/**
 * returns textarea tag
 *
 * @param string $name 
 * @param string $content 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function text_area_tag($name,$content=null,$options=array())
{
	if ( isset($options['size']) )
	{
        list($options['cols'],$options['rows']) = explode('x',$options['size']);
        unset($options['size']);
	}
	$options['name'] = $name;
	return content_tag('textarea',htmlspecialchars($content,ENT_QUOTES),$options);
}

/**
 * alias of text_area_tag
 *
 * @param string $name 
 * @param string $content 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function textarea_tag($name,$content=null,$options=array())
{
	return text_area_tag($name,$content,$options);
}

/**
 * returns text field tag
 *
 * @param string $name 
 * @param string $value 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function text_field_tag($name, $value=null,$options=array())
{
    $options['type'] = isset($options['type']) ? $options['type'] : 'text';
    $options['name'] = $name;
    $options['value'] = $value;
    // write an id if no id explicitly set
	return tag('input',$options);
}

/**
 * take any kind of string and make it css id worth
 *
 * @param string $id 
 * @return string html
 * @author Kenrick Buchanan
 */

function to_css_id($id)
{
    return trim(preg_replace("![\W_-]+!","-",$id),'-');
}


/**
 * take in an array and set default key vaules.
 *
 * @param string $defaults 
 * @param array $options 
 * @return void
 * @author Kenrick Buchanan
 */

function set_options_defaults($defaults, &$options)
{
    foreach ($defaults as $key => $value) {
        if (!array_key_exists($key, $options)) {
            $options[$key] = $value;
        }
    }                               
}

/**
 * creates the option tags for select form controls 
 *
 * @param array $options 
 * @param string $selected 
 * @return string html of option tags
 * @author Kenrick Buchanan
 */

function options_for_select($options, $selected=null)
{
    $str = '';
    if (!is_array($selected)) {
        $_o = $selected;
        $selected = array();
        $selected[] = $_o;
        unset($_o);
    }
    foreach($options as $key=>$value) {
        if ( is_array($value) ) {
            $str .= sprintf("<optgroup label=\"%s\">",$key);
            foreach( $value as $k=>$v) {
                $is_selected = (in_array($k,$selected)) ? "selected=\"selected\"" : '';
              $str .= sprintf("<option value=\"%s\" %s>%s</option>",
                      htmlspecialchars($k, ENT_QUOTES),
                      $is_selected,
                      htmlspecialchars($v, ENT_QUOTES));
            }
            $str .= '</optgroup>';
        } else {
            $is_selected = (in_array($key,$selected)) ? "selected=\"selected\"" : '';
            $str .= sprintf("<option value=\"%s\" %s>%s</option>",
                            htmlspecialchars($key, ENT_QUOTES),
                            $is_selected,
                            htmlspecialchars($value, ENT_QUOTES));
        }
    }
    return $str;
}

/**
 * returns a select form element of datetime elements
 *
 * @param string $date a getdate array
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_datetime($date, $options=array())
{
	if ( !is_array($date) || empty($date) ) 
	{
		$date = getdate();
	}
	
	// order the symbols
	$order = array('year','month','day','hour','minute');
	if ( isset($options['discard']) )
	{
	    $discard_order = array();
	    foreach($order as $o){
	        if ( $o != $options['discard'] )
	        {
	            $discard_order[] = $o;
	        } else {
	            break;
	        }
	    }
	    $order = $discard_order;
	    unset($options['discard']);
	}
	
	// now compare arrays with symbol order, making sure to chop off ones
	// that they discarded
	
	// go through order
	if ( isset($options['symbol_order']) )
	{
	    $user_order = $options['symbol_order'];
	    $order  =  array_intersect($user_order, $order);
	    unset($options['symbol_order']);
	}
	
	
	// loop through and create the elements
	$selects = '';
	foreach( $order as $func )
	{
	   $call = "select_{$func}";
	   $selects .= $call($date,$options);
	}
	
	return $selects;

}

/**
 * returns a form select tag populated with years
 * by default counts years +/- passed year
 * options are name, start_year, end_year
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_year($date=null, $options=array())
{
	$options['field_name'] = isset($options['field_name']) ? $options['field_name'] : 'year';
	
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	$year       = $date['year'];
	$start_year = isset($options['start_year']) ? $options['start_year'] : $year -5;
	$end_year   = isset($options['end_year']) ? $options['end_year'] : $year +5;
	$step 		= ($start_year < $end_year) ? 1 : -1;
	$years 		= array();
	while ( $start_year != $end_year )
	{
		$years[$start_year] = $start_year;
		$start_year = $start_year + $step;
	}
	
	if (isset($options['include_blank']) 
	    && $options['include_blank']
	    && isset($options['select_blank'])
	    && $options['select_blank']) {
	    $year = '';
	}
	 
	set_options_defaults( array('prefix', 'include_blank', 'discard_type', 'disabled'),
	                     $options);   	
	return select_html($options['field_name'],
					  options_for_select($years,$year),
					  $options['prefix'],
					  $options['include_blank'],
					  $options['discard_type'],
					  $options['disabled']);
}

/**
 * returns select tag of months
 *                    
 * returns select menu of months, specify in the options array if you 
 * want to use the month names instead of just the numbers
 *
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_month($date=null, $options=array())
{
	$m = 1;
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	$month  = $date['mon'];
	$options['field_name'] = isset($options['field_name']) ? $options['field_name'] : 'mon';
	$months = array();
	while ( $m <= 12 )
	{
	    if ( isset($options['use_month_names']) )
		{
		    $month_name = get_month($m);
		}
		$months[sprintf("%02d",$m)] = isset($options['use_month_names']) ? 
		              $month_name : 
		              sprintf("%02d",$m);
		$m++;
	}
	unset($options['use_month_names']);
	
	if (isset($options['include_blank']) 
	    && $options['include_blank']
	    && isset($options['select_blank'])
	    && $options['select_blank']) {
	    $selected = '';
	} else {
	    $selected = sprintf("%02d",$month);
	}
	set_options_defaults( array('prefix', 'include_blank', 'discard_type', 'disabled'),
	                     $options);
	return select_html($options['field_name'], 
					   options_for_select($months, $selected), 
					   $options['prefix'],
					   $options['include_blank'],
					   $options['discard_type'],
					   $options['disabled']);
}

/**
 * returns select menu of days of week
 *
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_day($date=null, $options=array())
{
	$d = 1;
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	$day = $date['mday'];
	$options['field_name'] = isset($options['field_name']) ? $options['field_name'] : 'mday';
	$days = array();
	while ( $d <= 31 )
	{
	    $df = sprintf("%02d",$d);
		$days[$df] = $df;
		$d++;
	}
	
	if (isset($options['include_blank']) 
	    && $options['include_blank']
	    && isset($options['select_blank'])
	    && $options['select_blank']) {
	    $selected = '';
	} else {
	    $selected = sprintf("%02d",$day);
	}
	set_options_defaults( array('prefix', 'include_blank', 'discard_type', 'disabled'),
	                     $options);
	return select_html($options['field_name'], 
					   options_for_select($days, $selected), 
					   $options['prefix'],
					   $options['include_blank'],
					   $options['discard_type'],
					   $options['disabled']);		                                    						
}

/**
 * returns select menu of hours in 24 hour format 
 *
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_hour($date=null, $options=array())
{
	$h = 0;
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	$hour = $date['hours'];
	$options['field_name'] = isset($options['field_name']) ? $options['field_name'] : 'hours';
	$hours = array();
	while ( $h <= 23 )
	{
	    $hf = sprintf("%02d",$h);
		$hours[$hf] = $hf;
		$h++;
	}
	
	if (isset($options['include_blank']) 
	    && $options['include_blank']
	    && isset($options['select_blank'])
	    && $options['select_blank']) {
	    $selected = '';
	} else {
	    $selected = sprintf("%02d",$hour);
	}
	
	set_options_defaults( array('prefix', 'include_blank', 'discard_type', 'disabled'),
	                     $options);
	                     
	return select_html($options['field_name'], 
					   options_for_select($hours, $selected), 
					   $options['prefix'],
					   $options['include_blank'],
					   $options['discard_type'],
					   $options['disabled']);
}
	
/**
 * returns select menu of minutes
 *
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_minute($date=null, $options=array())
{
	$m = 0;
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	$min = $date['minutes'];
	$options['field_name'] = isset($options['field_name']) ? $options['field_name'] : 'minutes';
	$step = isset($options['minute_step']) ? $options['minute_step'] : 1;
	$mins = array();
	while ( $m <= 59 )
	{
	    $mf     = sprintf("%02d",$m);
		$mins[$mf] = $mf;
		
		$m = $m + $step;
	}
	unset($options['minute_step']);
	
	if (isset($options['include_blank']) 
	    && $options['include_blank']
	    && isset($options['select_blank'])
	    && $options['select_blank']) {
	    $selected = '';
	} else {
	    $selected = sprintf("%02d",$min);
	}
	
	set_options_defaults( array('prefix', 'include_blank', 'discard_type', 'disabled'),
	                     $options);
	
	return select_html($options['field_name'], 
					   options_for_select($mins, $selected), 
					   $options['prefix'],
					   $options['include_blank'],
					   $options['discard_type'],
					   $options['disabled']);
}

/**
 *  returns select menu of seconds
 *
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_second($date=null, $options=array())
{
	$s = 0;
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	$second = $date['seconds'];
	$options['field_name'] = isset($options['field_name']) ? $options['field_name'] : 'seconds';
	$seconds = array();
	while ( $s <= 59 )
	{
	    $sf = sprintf("%02d",$s);
		$seconds[$sf] = $sf;
		$s++;
	}
	
	if (isset($options['include_blank']) 
	    && $options['include_blank']
	    && isset($options['select_blank'])
	    && $options['select_blank']) {
	    $selected = '';
	} else {
	    $selected = sprintf("%02d",$second);
	}
	
	set_options_defaults( array('prefix', 'include_blank', 'discard_type', 'disabled'),
	                     $options);
	                     
	return select_html($options['field_name'], 
					   options_for_select($seconds, $selected), 
					   $options['prefix'],
					   $options['include_blank'],
					   $options['discard_type'],
					   $options['disabled']);
}

/**
 * datetime should be a getdate() array
 * returns a select menu of just h/m/s
 *
 * @param string $datetime 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_time($datetime=null, $options=array())
{
	if ( !is_array($datetime)  || empty($datetime)) 
	{
		$datetime = getdate();
	}
	$seconds = isset($options['include_seconds']) ? 
			   select_seconds($datetime,$options) 
			   : '';
	return select_hour($datetime,$options).select_minute($datetime,$options).$seconds;
}

/**
 * returns a select menu of only m/d/y
 *
 * @param string $date 
 * @param array $options 
 * @return string html
 * @author Kenrick Buchanan
 */

function select_date($date=null, $options=array())
{
	if ( !is_array($date)  || empty($date)) 
	{
		$date = getdate();
	}
	
	return select_month($date,$options).select_day($date,$options).select_year($date,$options);
}

/**
 * creates an anchor tag
 *
 * @param string $title 
 * @param string $url 
 * @param string $html_options 
 * @param string $confirm_message 
 * @return string html
 * @author Kenrick Buchanan
 */

function link_to($title, $url, $html_options=null, $confirm_message=false)
{
	$confirm_message ? $html_options['onClick'] = "return confirm('{$confirm_message}')": null;
	return sprintf('<a href="%s" %s>%s</a>', $url, tag_options($html_options), $title);
}

/**
 * creates a javascript tag for $script
 *
 * @param string $script 
 * @return string html
 * @author Kenrick Buchanan
 */

function javascript_tag($script) 
{
    return sprintf('<script language="javascript" type="text/javascript">%s</script>', $script);
}

/**
 * creates a javascript source include tag for $url file
 *
 * @param string $url 
 * @return string html
 * @author Kenrick Buchanan
 */

function javascript_include_tag($url) 
{
    return sprintf('<script language="javascript" type="text/javascript" src="%s"></script>', $url);
}

/**
 * creates an anchor tag with an onclick mouse event
 *
 * @param string $title 
 * @param string $func 
 * @param string $html_options 
 * @return string html
 * @author Kenrick Buchanan
 */

function link_to_function($title, $func, $html_options = null) 
{
    $html_options['onClick'] = "$func; return false;";
    return link_to($title, '#', $html_options);
}

/**
 * returns string of file size with acronyms for integer $size
 *
 * @param string $size 
 * @return string file size
 * @author Kenrick Buchanan
 */

function string_format_file_size($size) 
{      
	$sizes = Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
	$ext   = $sizes[0];
	$count = count($sizes); 
	for ($i=1; (($i < $count) && ($size >= 1024)); $i++) {
		$size = $size / 1024;
		$ext  = $sizes[$i];
	}
	return round($size, 2)." ".$ext;
}

/**
 * return array of date parts from 14chr string $date
 *
 * @param string $date 
 * @return array date parts
 * @author Kenrick Buchanan
 */

function date_parts($date)
{    
	if ( !preg_match("!^\d{14}$!",$date) )
	{
		return false;
	}
	$d			= array();
	$d["year"]  = substr($date, 0, 4);
	$d["month"] = substr($date, 4, 2);
	$d["day"]   = substr($date, 6, 2);
	$d["hour"]  = substr($date, 8, 2);
	$d["min"]   = substr($date, 10, 2);
	$d["sec"]   = substr($date, 12, 2);

	return $d;
}

/**
 * returns get_date array from passed $date string
 *
 * @param string $date 
 * @return array getdate array
 * @author Kenrick Buchanan
 */

function date_to_getdate($date)
{
	return getdate(strtotime($date));
}

/**
 * returns strftime formatted date
 *
 * @param string $string 
 * @param string $format 
 * @param string $Y 
 * @param string $default_date 
 * @return mixed either string of formatted time or nothing
 * @author Kenrick Buchanan
 */

function format_date_string($string, $format="%b %e, %Y", $default_date=null)
{
	if (substr(PHP_OS,0,3) == 'WIN') {
		$_win_from = array ('%e',  '%T',       '%D');
		$_win_to   = array ('%#d', '%H:%M:%S', '%m/%d/%y');
		$format = str_replace($_win_from, $_win_to, $format);
	}
	if($string != '') {
		return strftime($format, date_make_timestamp($string));
	} elseif (isset($default_date) && $default_date != '') {
		return strftime($format, date_make_timestamp($default_date));
	} else {
		return;
	}
}

/**
 * returns a UNIX Epoch timestamp generated from $string
 *
 * @param string $string 
 * @return string
 * @author Kenrick Buchanan
 */

function date_make_timestamp($string=null)
{
	if(empty($string)) {
		$string = "now";
	}
	$time = strtotime($string);
	if (is_numeric($time) && $time != -1)
	return $time;

	// is mysql timestamp format of YYYYMMDDHHMMSS?
	if (preg_match('/^\d{14}$/', $string)) {
		$time = mktime(substr($string,8,2),substr($string,10,2),substr($string,12,2),
		substr($string,4,2),substr($string,6,2),substr($string,0,4));

		return $time;
	}

	// couldn't recognize it, try to return a time
	$time = (int) $string;
	if ($time == -1 || $time === false) {
        // strtotime() was not able to parse $string, use "now":
        $time = time();
    }
    return $time;
}

/**
* @param	int $start unix timestamp
* @param	string $end unix timestamp
* @return	array weeks, days, hours, min, sec
*/
// returns array of weeks, days, hours etc of time difference
// between $start and $end
function date_calculate_time_diff($start, $end) 
{
	if($end < $start){
		trigger_error('End time passed to calculate duration must be greater then start time', WARNING);   
		return;
	}
	$diff   = $end - $start;
	// Force the seconds to be numeric
	$seconds = (int)$diff;

	// Define our periods
	$periods = array (
		'years'     => 31556926,
		'months'    => 2629743,
		'weeks'     => 604800,
		'days'      => 86400,
		'hours'     => 3600,
		'minutes'   => 60,
		'seconds'   => 1
		);

	// Loop through
	foreach ($periods as $period => $value){
		$count = floor($seconds / $value);

		if ($count == 0) {
			continue;
		}

		$values[$period] = $count;
		$seconds = $seconds % $value;
	}

	return $values;
}

/**
 * get array of month names localized
 *
 * @param string $m 
 * @return array month names
 * @author Kenrick Buchanan
 */

function get_month($m=0) 
{
   return (($m==0 ) ? date("F") : date("F", mktime(9,0,0,$m,1)));
}

/**
 * used for escaping strings going into the database, 
 * only good for mysql
 *
 * @param string $given 
 * @return mixed
 * @author Kenrick Buchanan
 */

function db_escape($given)
{    
    if (get_magic_quotes_gpc()==1) {
          $given = is_array( $given ) ? array_map( 'stripslashes_array', $given ) 
                                      : stripslashes( $given );
      }
    return is_array( $given ) ? array_map( 'db_escape', $given ) 
                            : mysql_real_escape_string( $given );   
}





