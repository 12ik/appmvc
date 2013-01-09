<?php
class IndexModel extends APP_Model
{
    public function __construct () {
		$this->db = $this->loadDb('code');
    }
   	public function insert_info($name,$number) {
		$sql = "insert into u_info(name,id_number) values('$name','$number')";
		$this->db->query($sql);
		return $this->db->LastInsertId();
	}
	public function get_image($id,$step)
	{
		$sql = "select image".$step." from u_info where id='$id'";
		return $this->db->fetchOne($sql);
	}
	public function get_info($id)
	{
		$sql = "select id,name,id_number from u_info where id='$id'";
		return $this->db->fetchRow($sql);
	}
   	
}