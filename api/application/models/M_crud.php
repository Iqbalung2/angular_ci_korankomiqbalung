<?php
class M_crud extends CI_Model{	
	function __construct(){
		parent::__construct();
		$this->load->database();
	}
	
	function get($data)
	{
		$res = $this->db->query("SELECT * FROM items LIMIT ".$data['start'].",".$data['limit']."   ")->result_array();
		return $res;
	}
	function total(){
		$res = $this->db->query("SELECT * from items")->result_object();
		return $res;
	}
	function insert($data){
		
		$res = $this->db->query("insert into items (title , description) values (".$data['title']." , ".$data['title'].")");
	}

}