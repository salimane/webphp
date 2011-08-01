<?php 

/**
* RecordSetTable
*/
class RecordSetTable
{   
    protected $RecordSetPager;
    private $rs; 
    private $show_record_count  = true;
    private $fields_to_hide     = array();
    private $header_names       = array();
    private $actions            = array();
    private $primary_key        = false;
    private $rs_callback        = false;
    private $escape_html        = true;
    private $max_download_rows  = 1000; // max no of rows to download
    private $num_block_rows     = 20;   // number of rows for rstohtml
    private $no_escape_fields   = array(); // fields you do not want to htmlspecialchars 
    //private $sortable_columns   = false;
       
    
    function __construct(RecordSetPager $RecordSetPager)
    {                                                  
        $this->RecordSetPager = $RecordSetPager;
        $this->num_block_rows = $RecordSetPager->rows;
    }
    
    /**
     * show or hide record count at bottom
     *
     * @param string $onoff 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function showRecordCount($onoff = true)
    {
        $this->show_record_count = $onoff;
    }
    
    
    /**
     * set num of rows to download
     *
     * @param string $num 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function setMaxDownloadRows($num)
    {
        $this->max_download_rows = $num;
    }
    
    
    /**
     * set a function to be used on escaping fields upon display 
     * by defaults uses htmlspecialchars
     *
     * @param string $escape_function 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function escapeHtml($onoff=true)
    {
        $this->escape_html = $onoff;
    }
    
    /**
     * set which fields to not run htmlspecialchars on
     *
     * @param array $fields
     * @return void
     * @author Kenrick Buchanan
     */
    
    function setNoEscape(array $fields)
    {
        $this->no_escape_fields = $fields;
    }
    
    
    /**
     * use this function to hide certain fields from becoming
     * columns in the table
     *
     * @param string $array 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function hideFields(array $fields)
    {
        $this->fields_to_hide = $fields;
    }
    
    
    /**
     * in case it is a pain to alias your SQL and give nice names
     * you can achieve the same thing here:
     * array('sql key name' => 'new header name');
     * array('created' => 'Time Form Submitted');
     *
     * @param string $array 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function humanHeaders(array $headers)
    {
        $this->header_names = $headers;
    }
    
    
    /**
     * specify a function to be called on each row.
     *
     * @param string $callback 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function setCallback($callback)
    {
        $this->rs_callback = $callback;
    }
    
    
    /**
     * call RecordSetPager's method to gen the records
     *
     * @author Kenrick Buchanan
     */
    
    protected function getRecords()
    {
        $this->rs = $this->RecordSetPager->getRecords();                
    }

    /**
     * set the primary key for the action forms
     *
     * @param string $key 
     * @return void
     * @author Kenrick Buchanan
     */
    
    function setPrimaryKey($key)
    {
        $this->primary_key = $key;
    }
    
    
    /**
     * if there are actions, new cell on the row is created
     * where in a form is created with a submit button
     * if their is not a primary key set for the record set
     * the actions will not be rendered. the primary key can be
     * singular or an array, and it will be written as hidden
     * fields in this form
     *
     * @param string $type 
     * @param string $action 
     * @param string $method 
     * @return void
     * @author Kenrick Buchanan
     */
                                        
    public function setAction($type, $action, $method='get')
    {
        $this->actions[$type] = array('action' => $action,
                                      'method' => $method); 
    }
    
    
    /**
     * write the forms to the table row
     *
     * @param string $rs 
     * @param string $rowid 
     * @return $s string html
     * @author Kenrick Buchanan
     */
    
    function writeActions($fields)
    {
        $s = '';
        if(!empty($this->actions)){            
            foreach($this->actions as $k=>$v){
                $class = 'submit';
                $onclick = '';
                $method = $v['method'];
                
                if ( strcasecmp(strtolower($k),'delete') == 0 )
                {
                    $class   = 'submit_delete';
                    $onclick = "onclick=\"return confirm('Are you sure you want to delete this?');\"";
                    $method  = 'post';
                } 
                
                $s .="<form method='$method' action='{$v['action']}' style='display:inline'>";
                
                if(!empty($this->primary_key)){
                    if(is_array($this->primary_key)){
                        foreach($this->primary_key as $key){
                            $s .= "<input type='hidden' name='$key' value='{$fields[$key]}' />";       
                        }
                    } else {
                        $s .= "<input type='hidden' name='{$this->primary_key}' value='".$fields[$this->primary_key]."' />";
                    }
                }
                $s .= "<input type='submit' name='submit' value='$k' class='$class' $onclick /></form>";
            }
        }
        return $s;
    }
    
    /**
     * render an HTML table
     * most of this was ripped from adodb_pager, but made
     * for my tastes.
     *
     * @return string $html table
     * @author Kenrick Buchanan
     */
        
    private function renderTable()
    { 
        $s       = '';
        $rows    = 0;
        $temphdr = '';
        
        if (!$this->rs) {
            throw new Exception("Bad recordset given to ".__CLASS__);
            return false;
        }
        
        $rowclass  = array('at-row0','at-row1');
        $rowcounter = 0;
        
        $typearr = array();
        $ncols = $this->rs->FieldCount();               
    
        // write column headers
        $temp_col_attribute = array();
        #$col_sort_options   = array();
        for ($i=0; $i < $ncols; $i++) {	
            $field = $this->rs->FetchField($i);
            if(!empty($this->fields_to_hide)){
                if(in_array($field->name, $this->fields_to_hide)) continue; // move to next iteration
            }
            
            // get meta type of column
            $typearr[$i] = $this->rs->MetaType($field->type,$field->max_length);
            
            // rename headers using header_names
            if (isset($this->header_names[$field->name])){
                $fname = $this->header_names[$field->name];                
            }else{                
                $fname = htmlspecialchars($field->name);
            }            
            
            if (strlen($fname)==0) $fname = '';
            
            // sorting 
            /*
            if ($this->sortable_columns) {
                if (in_array($field->name, $this->sortable_columns)) {
                    $_GET['sort_by'] = htmlspecialchars($field->name);
                    $_GET['dir'] =  
                    $fname = "<a href=\"\">$fname</a>";
                }
            } 
            */
            $temp_col_attribute[] = '<col />';
            $temphdr  .= "<td>$fname</td>\n";
        }
        
        if(!empty($this->actions)){
            #$col_sort_options[] = 'None';
            $temphdr .= "<td>Actions</td>\n";
        }
        $html = "<table class='at-table'>".
                implode("\n",$temp_col_attribute).
                "<thead><tr>$temphdr</tr></thead>\n";

        // smart algorithm - handles ADODB_FETCH_MODE's correctly by probing...
        $numoffset = isset($this->rs->fields[0]) || isset($this->rs->fields[1]) || isset($this->rs->fields[2]);
        $idcounter = 1;
        while (!$this->rs->EOF) {
            $myrowclass = $rowclass[$rowcounter];
            $rowcounter = 1 - $rowcounter;
            $rowid = "at-{$idcounter}";
            $s .= "\n<tr class='$myrowclass' id='$rowid'>\n";
            
            // user defined Call back function
            if($this->rs_callback){                
                call_user_func_array($this->rs_callback, array(&$this->rs->fields));           
            }
            for ($i=0; $i < $ncols; $i++) {
                
                if ($i===0) {
                    $v = ($numoffset) ? $this->rs->fields[0] : reset($this->rs->fields);
                } else {
                    $v = ($numoffset) ? $this->rs->fields[$i] : next($this->rs->fields);
                }
              
                // skip ones to be hidden
                $field = $this->rs->FetchField($i);
                if(!empty($this->fields_to_hide)){
                    if(in_array($field->name, $this->fields_to_hide)) continue;
                }
                
                $type = isset($typearr[$i]) ? $typearr[$i] : '';
                $fieldval = '';
                switch($type) {
                case 'D':
                    if (!strpos($v,':')) {
                        $fieldval = $this->rs->UserDate($v,"Y-m-d");
                        break;
                    }
                case 'T':
                    $fieldval = $this->rs->UserTimeStamp($v,"Y-m-d h:i:s");
                break;
                case 'I':
                case 'N':
                    $fieldval = trim($v);
                    
                break;

                default:
                    $v = trim($v);
                    
                    // call user defined escape function
                    if ($this->escape_html) {
                        if (!in_array($field->name, $this->no_escape_fields)) {
                            $v = htmlspecialchars($v, ENT_QUOTES);
                        }                        
                    }                    
                    
                    if (strlen($v) == 0) $v = '';
                    $fieldval = $v;                  
                }                
                $s .= "<td>$fieldval</td>\n";
            } // for 
            
            // actions
            if(!empty($this->actions)){
                $s .= "<td nowrap='nowrap' width='1%'>".$this->writeActions($this->rs->fields)."</td>";
            }
            $s .= "</tr>\n";
                  
            $rows += 1;
            if ($rows >= $this->max_download_rows) {
                $rows = "<p>Truncated at {$this->max_download_rows}</p>";
                break;
            } // switch

            $this->rs->MoveNext();
        
            // additional EOF check to prevent a widow header
            if (!$this->rs->EOF && $rows % $this->num_block_rows == 0) {        
                $html .= "<tbody>\n".$s."</tbody>\n</table>\n\n";
                $s = $hdr;
            }
            $idcounter++;
        } // while

        $html .= "<tbody>\n".$s."</tbody>\n</table>\n\n";

        if ($this->show_record_count) $html .= "<h2 class='at-rows'>".$this->RecordSetPager->totalRows()." Rows</h2>";
        
        return $html;
    }
    
    function __toString()
    {   
        try {
            $this->getRecords(); 
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $header = $this->RecordSetPager->navigation();
        $footer = $this->RecordSetPager->pageCount().' '.$header;
        $grid   = $this->renderTable();
        
        ob_start();
		echo "<div class='at-container'><div class='at-header'>",
				$header,
			"</div>",
				$grid,
			"<div class='at-footer'>",
				$footer,
			"</div></div>\n";
		$table = ob_get_contents();
		ob_end_clean();
		return $table;
    } 
    
    /**
     * sortableColumns
     * needs to be an array of the SQL columns according to
     * your SQL statement that you want to order by
     *
     * @param array $sortable array of headers you want to be able to sort by
     * @return void
     * @author Kenrick Buchanan
     */
    /*
    function sortableColumns(array $sortable)
    {                                      
        if (empty($sortable)) return;
        $this->sortable_columns = $sortable;
    } 
    */
}


