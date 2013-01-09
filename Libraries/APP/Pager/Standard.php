<?php
require_once 'APP/Pager/Abstract.php';
class APP_Pager_Standard extends APP_Pager_Abstract
{
    public $table = null;

	public $where = null;

	public $order = null;

    // 设置数据总数
    protected function setItems()
    {
		$this->items = $this->table->count($this->where);

        return $this;
    }

    // 获取每页的数据
    protected function setData()
    {
        $start = ($this->page - 1) * $this->itemNum;

		if($this->order == null)
		{
 			$primary = $this->table->getPrimaryKey();
			foreach ($primary as $pk)
			{
				$this->order[] = $pk . ' ASC' ;
			}
		}

		$this->data = $this->table->fetchAll($this->where, $this->order, $this->itemNum, $start);

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

    // 设置查询APP_Table
    public function setTable($table , $where = null, $order = null)
    {
        if(!$table instanceof APP_Db_Table)
        {
            throw new APP_Exception('Pager::setTable is not an instance of APP_Db_Table');
        }

        $this->table = $table;

		$this->where = $where;

		$this->order = $order;

        return $this;
    }
}

