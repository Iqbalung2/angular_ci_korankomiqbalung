<?php

class MY_Controller extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		// echo $this->config->item('url_upload_path2');
		// exit;
	}

	function _respon($in,$data = false){
		if($in){
			$out = array(
				'success' => true,
				'msg' => 'Transaksi Berhasil dilaksanakan'
			);			
			if ($data) {
				$out['data'] = $data;
			}
		}else{
			$out = array(
				'success' => false,
				'msg' => 'Transaksi Gagal dilaksanakan'
				);
		}
		return $out;
	}	

	// Selesai keperluan untuk service
}