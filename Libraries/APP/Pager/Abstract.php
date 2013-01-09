<?php
abstract class APP_Pager_Abstract
{
    public $page = 1;                          // 当前页
    public $next = 1;                          // 上一页
    public $prev = 1;                          // 下一页
    public $first = 1;                         // 第一页
    public $last = 1;                          // 最尾页

    public $pages = 0;                         // 页码总数
    public $items = 0;                         // 数据总数

    public $url = "/";                         // 当前页url
    public $data = array();                    // 当前页data
    public $pageBar = array();                 // 前台应显示的页码数组 (1,2,3 ... 10)

    protected $pageNum = 10;                   // 每页显示多少条页码(*) pages
    protected $itemNum = 20;                   // 每页显示多少条记录(*)

    public function __construct()
    {
        /**
         * 需要设置的数据有以下：
         * 1. pageNum (默认页条中显示10个页面链接)
         * 2. itemNum (默认每页中显示20个数据条目)
         * 3. page (必须给出当前页数码)
         * 4. url  (必须给出当前页)
         * 5. 查询数据的方式(实际是两条sql语句 a)查询数量 b) 查询data )
         */
    }

    public function generate()
    {
        // 全部记录数量(查询)
//      $this->items = $this->getItems();
		$this->setItems();

        // 全部页码数量(计算)
        $this->pages = (int) ceil($this->items / $this->itemNum);

		// 如果没有查询结果，直接退出 TODO 还有一点点bug. "共0条记录,1/0页"
		if($this->pages == 0)
		{
			return false;
		}

        // 五种页码数值(计算)
        $this->first = 1;
        $this->last = $this->pages;
        $this->page = $this->page < $this->pages ? $this->page : $this->pages;
        $this->next = ($this->page + 1) > $this->last  ? $this->last : ($this->page + 1);
        $this->prev = ($this->page - 1) < $this->first ? $this->first : ($this->page - 1);

       // 获取当前页数据(查询)
        $this->setData();

        // 设置当前页
        $this->setPageBar();
		
		return $this;
    }

    // 返回本页页码
    public function getPage()
    {
        return $this->page;
    }

    // 设置本页页码
    public function setPage($PageNo)
    {
        $this->page = (int)$PageNo > 0 ? (int)$PageNo : 1;
        return $this;
    }

    // 获取上一页页码
    public function getPrev()
    {
        return $this->prev;
    }

    // 获取下一页页码
    public function getNext()
    {
        return $this->next;
    }

    // 获取第一页页码
    public function getFirst()
    {
        return $this->first;
    }

    // 获取最尾页页码
    public function getLast()
    {
        return $this->last;
    }

    // 获取总页数
    public function getPages()
    {
        return $this->pages;
    }

    // 获取每页页码数
    public function getPageNum()
    {
        return $this->pageNum;
    }

    // 设置每页页码数
    public function setPageNum($num)
    {
        $this->pageNum = (int)$num;
        return $this;
    }

    // 获取每页记录数
    public function getItemNum()
    {
        return $this->itemNum;
    }

    // 设置每页记录数
    public function setItemNum($num)
    {
        $this->itemNum = (int)$num;
        return $this;
    }

    // 获取总记录条数
    public function getItems()
    {
        return $this->items;
    }

    // 设置总记录条数
    abstract protected function setItems();

	// 获取每页的数据
    public function getData()
    {
		return $this->data;
    }

    // 获取每页的数据
    abstract protected function setData();


    public function getUrl()
    {
        return htmlspecialchars($this->url);
    }

    public function setUrl($url="/")
    {
        $this->url = $url;
        return $this;
    }

    // 这是一个决定分页形式的过程, 可以通过在子类中实现重载以改变在前台显示分页的范围
    protected function setPageBar()
    {
        $start_page_no =  floor( ($this->page - 1) / $this->pageNum ) * $this->pageNum  + 1 ;
        $end_page_no = $start_page_no + $this->pageNum - 1 ;
        if( $this->pages <= $end_page_no ){
            $this->pageBar = range($start_page_no,$this->pages);
        }
        else{
            $this->pageBar = range($start_page_no,$end_page_no);
        }
    }

    public function getPageBar()
    {
        return $this->pageBar;
    }
}