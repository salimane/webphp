<?php 


/**
* RecordSetPager
*/
class RecordSetPager
{   
    private $rs          = null;
    public $pager_id     = 'pager_cp'; // pager id sent in url 
    public $more_links   = '&hellip;';
	public $start_links  = '&hellip;';
	public $first        = '|&laquo;';
	public $previous     = '&laquo;';
	public $next         = '&raquo;';
	public $last         = '&raquo;|';
    public $page         = 'Page'; // displays Page 1 of n
	public $cache        = 0;  #secs to cache with CachePageExecute()
	public $show_page_links = true;
	private $total_rows;
    private $_get_string = '';
    private $current_page;
    private $links_per_page = 10;
    public $rows;
    
     
    public function __construct(&$db, $sql, $numrows=30, $location=null, $pager_id='pager_cp', $use_session=false)
    {
		if (empty($location)) {
		    $this->location = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] 
		                                                     : $_SERVER['REDIRECT_URL'];
	    }
        $pos = strpos($this->location,'?');
        if($pos !== false){
            $this->location = substr($this->location,0,$pos);
        }
        $this->location = htmlspecialchars($this->location)	;
        
        $this->rows     = $numrows;
        
		$this->sql      = $sql;
		$this->db       = $db;
		
	    $this->pager_id = $pager_id;
		$next_page      = $pager_id.'_next_page';	                                                   
		$current_page   = $pager_id.'_current_page';
		
		if ($use_session) {
		    if (isset($_GET[$next_page])) {
    			$_SESSION[$current_page] = (int) $_GET[$next_page];
    		}
    		if (empty($_SESSION[$current_page])) $_SESSION[$current_page] = 1; ## at first page

    		$this->current_page = $_SESSION[$current_page];
		} else {		
		    if (isset($_GET[$next_page])) {
    			$_GET[$current_page] = (int) $_GET[$next_page];
    		}
    		if (empty($_GET[$current_page])) $_GET[$current_page] = 1; ## at first page
		
    		$this->current_page = (int) $_GET[$current_page];
		}
    }
    
    public function first($anchor=true)
	{           
	    echo '<span class="first-page page-nav">';
		if ($anchor) {
		    echo "<a href='{$this->location}?{$this->pager_id}_next_page=1{$this->_get_string}'>{$this->first}</a>";
		} else {
			echo "{$this->first}";
		}                       
		echo "</span>";
	}
	
	public function next($anchor=true)
	{
	    echo '<span class="next-page page-nav">';
		if ($anchor) {
		    $extras = $this->buildQuery($_GET);
		    echo "<a href='{$this->location}?{$this->pager_id}_next_page=".
		          ($this->rs->AbsolutePage() + 1).
		          "{$this->_get_string}'>{$this->next}</a>";		
		} else {
			print "{$this->next}";
		}
		echo '</span>';
	}
	
	private function buildQuery($a)
    {
        unset($a[$this->pager_id . "_next_page"]);
        $this->_get_string =  '&'.http_build_query($a);
    }       
    
    function previous($anchor=true)
	{  
	    echo '<span class="previous-page page-nav">';
		if ($anchor) {
		    echo "<a href='{$this->location}?{$this->pager_id}_next_page=".
		         ($this->rs->AbsolutePage() - 1).
		         "{$this->_get_string}'>{$this->previous}</a>";
		} else {
			print "{$this->previous}";
		}                           
		echo '</span>';
	}
	
    function pageLinks()
    {
        $pages        = $this->rs->LastPageNo();
        $links_per_page = $this->links_per_page ? $this->links_per_page : $pages;
        $start = 1;
        for($i=1; $i <= $pages; $i+=$links_per_page)
        {
            if($this->rs->AbsolutePage() >= $i)
            {
                $start = $i;
            }
        }
        $numbers = '';
        $end = $start+$links_per_page-1;
        $link = $this->pager_id . "_next_page";
        if($end > $pages) $end = $pages;					

        if ($this->start_links && $start > 1) {
            $pos = $start - 1;
            $numbers .= '<span class="page-nav">';
            $numbers .= "<a href={$this->location}?$link=$pos{$this->_get_string}>$this->start_links</a>";
            $numbers .= '</span>';
        } 

        for($i=$start; $i <= $end; $i++) {                                                
            $numbers .= '<span class="page-nav">';
            if ($this->rs->AbsolutePage() == $i){
                $numbers .= "<b>$i</b>";                       
            } else {
                $numbers .= "<a href={$this->location}?$link=$i{$this->_get_string}>$i</a>";
            }
            $numbers .= '</span>';

        }
        if ($this->more_links && $end < $pages) {
            $numbers .= '<span class="page-nav">';
            $numbers .= "<a href={$this->location}?$link=$i{$this->_get_string}>$this->more_links</a>";
        }
        echo $numbers;
    }
	
	function last($anchor=true)
	{
		if (!$this->db->pageExecuteCountRows) return;
		echo '<span class="last-page page-nav">';
		if ($anchor) {
		    $extras = $this->buildQuery($_GET);
		    echo "<a href='{$this->location}?{$this->pager_id}_next_page=".
		        $this->rs->LastPageNo().
		        "{$this->_get_string}'>{$this->last}</a>";		
		} else {
			print "{$this->last}";
		}                       
		echo '</span>';
	}
	
	function navigation()
	{	    	    
	    $this->buildQuery($_GET);
		ob_start();
		if (!$this->rs->AtFirstPage()) {
			$this->first();
			$this->previous();
		} else {
			$this->first(false);
			$this->previous(false);
		}
        if ($this->show_page_links){
            $this->pageLinks();
        }
		if (!$this->rs->AtLastPage()) {
			$this->next();
			$this->last();
		} else {
			$this->next(false);
			$this->last(false);
		}
		$s = ob_get_contents();
		ob_end_clean();
		return $s;
	}
	
	function pageCount()
	{
		if (!$this->db->pageExecuteCountRows) return '';
		$lastPage = $this->rs->LastPageNo();
		if ($lastPage == -1) $lastPage = 1; // check for empty rs.
		if ($this->current_page > $lastPage) $this->current_page = 1;
		return $this->page." ".$this->current_page."/".$lastPage;
	}
	
	function &getRecords()
	{
	    global $ADODB_COUNTRECS;	
		$savec = $ADODB_COUNTRECS;        
		if ($this->db->pageExecuteCountRows) {
            $ADODB_COUNTRECS = true;
        }
		if ($this->cache){
			$rs = &$this->db->CachePageExecute($this->cache, 
			                                   $this->sql, 
			                                   $this->rows, 
			                                   $this->current_page);
        } else {
			$rs = &$this->db->PageExecute($this->sql, 
			                              $this->rows, 
			                              $this->current_page);
        }
		$ADODB_COUNTRECS = $savec;
		
		$this->rs = &$rs;
		if ($rs === false) {
		    throw new Exception("Query Failed");
		    return;		    
		}
		
		$this->total_rows = $rs->MaxRecordCount();
		
		return $rs;
				
	}
	
	public function totalRows()
	{
	    return (int) $this->total_rows;
	}
    
}
