<?php 
/**
* 
*/
class Service_execute
{
	
	public function __construct()
	{
		
	}

	public function servicePost($url,$params)
	{
		
	  	$postData = '';
	   
	  	foreach($params as $k => $v) 
	   	{ 
	      $postData .= $k . '='.$v.'&'; 
	   	}
	   	$postData = rtrim($postData, '&');
	 
	    $ch = curl_init();  	 	
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);	    	    
	    curl_setopt($ch, CURLOPT_POST, count($postData));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
	 
	    $output=curl_exec($ch);
	    curl_close($ch);	    
	    $out = true;

	    $data = json_decode($output,true);

	    if ($data == null) {
	    	$out = false;
	    }else{
	    	if (!isset($data['success'])) {
	    		$out = false;
	    	}
	    }
	    		
	    return $out;
	 
	}
}