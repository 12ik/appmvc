<?
require_once 'APP/Pager/Abstract.php';
class APP_Pager_Simple extends APP_Pager_Abstract
{
	public $db = null;
	public $sql = null;

    // 设置数据总数
    protected function setItems()
	{
		$sql = preg_replace('/^\s*select\s+\*/i', 'SELECT COUNT(*) AS pcount', $this->sql);
		$this->items = $this->db->fetchOne($sql);
		return $this;
	}

    // 获取每页的数据
    protected function setData()
	{
		$start = ($this->page - 1) * $this->itemNum;
		$sql = $this->sql . " LIMIT $start, $this->itemNum";
		
		$this->data = $this->db->fetchAll($sql);
		return $this;
	}

    // 这是一个决定页码条在前台显示的页码范围
    protected function setPageBar()
    {
        $first =  floor( ($this->page - 1) / $this->pageNum ) * $this->pageNum  + 1 ;
        $last = $first + $this->pageNum - 1 ;
        if( $this->pages <= $last ){
            $this->pageBar = range($first,$this->pages);
        }
        else{
            $this->pageBar = range($first,$last);
        }
    }


    // 设置查询sql
	// 例如: select * from tbl_test where year='2009';
	public function setSql($db,$sql)
	{
		$this->db = $db;
		$this->sql = $sql;
		return $this;
	}
}

