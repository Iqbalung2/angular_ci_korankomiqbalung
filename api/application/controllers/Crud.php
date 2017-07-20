<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crud extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('M_crud');
		
	}
/*
	$num_rec_per_page = 2;
	if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; };
	$start_from = ($page-1) * $num_rec_per_page;

	if (!empty($_GET["search"])){
		$sqlTotal = "SELECT * FROM items 
			WHERE (title LIKE '%".$_GET["search"]."%' OR description LIKE '%".$_GET["search"]."%')"; 
		$sql = "SELECT * FROM items 
			WHERE (title LIKE '%".$_GET["search"]."%' OR description LIKE '%".$_GET["search"]."%') 
			LIMIT $start_from, $num_rec_per_page"; 
	}else{
		$sqlTotal = "SELECT * FROM items"; 
		$sql = "SELECT * FROM items LIMIT $start_from, $num_rec_per_page"; 
	}
	$result = $mysqli->query($sql);
	while($row = $result->fetch_assoc()){
	     $json[] = $row;
	}
	$data['data'] = $json;
	$result =  mysqli_query($mysqli,$sqlTotal);
	$data['total'] = mysqli_num_rows($result);
	echo json_encode($data);
*/
	public function get()
	{

		$num_rec_per_page = 5;
		if (isset($_GET["page"])){ 
			$page  = $_GET["page"];
		}else{ 
			$page=1; 
		};
		$start_from = ($page-1) * $num_rec_per_page;
		$data = array(
			'limit' => $num_rec_per_page,
			
			'start' => $start_from
		);

		$result = $this->M_crud->get($data);
		$query = $this->db->get('items');
		$num = $query->num_rows();
		
		$data = array(
			'data' => $result,
			'total' => $num, 
			);

		echo json_encode($data);
		
	}

	public function add(){
		
		$post = file_get_contents('php://input');
		$post = json_decode($post);
		$result = $this->db->query("INSERT INTO items (title,description) VALUES ('".$post->title."','".$post->description."')");
		if($result){
			echo "test";
		}

	}

	public function edit(){
		
	}
}
