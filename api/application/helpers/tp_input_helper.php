<?php
function ifunset($arr, $key, $default) {
	return isset($arr[$key])? $arr[$key] : $default;
}
function ifunsetempty($arr, $key, $default) {
	return !isset($arr[$key]) || empty($arr[$key]) || $arr[$key]=='undefined' ? $default : $arr[$key];
}
function ifunsetempty_quote($arr, $key, $default) {
	return !isset($arr[$key]) || empty($arr[$key]) || $arr[$key]=='undefined' ? $default : stripslashes(strip_tags(htmlspecialchars($arr[$key],ENT_QUOTES)));
}
function ifunsetemptybase64($arr, $key, $default) {
	return !isset($arr[$key]) || ifcharempty(base64_decode($arr[$key])) || $arr[$key] == 'undefined' ? $default : base64_decode($arr[$key]);	
}
function ifempty($val, $default) {
	return empty($val)? $default : $val;
}
function ifcharempty($val) {
	return empty($val)? true : false;
}
function ifcharquote($string){
	return stripslashes(strip_tags(htmlspecialchars($string,ENT_QUOTES)));
}
function echojson($data) {
	header("Content-Type: application/json");
	
	if (!is_string($data)) {
		$data = json_encode($data);
	}
	
	echo $data;
}
function convertArrayKeysToUtf8(array $array){ 
	$convertedArray = array(); 
	foreach($array as $key => $value) { 
		if(!mb_check_encoding($key, 'UTF-8')) $key = utf8_encode($key); 
		if(is_array($value)) $value = convertArrayKeysToUtf8($value); 
		$convertedArray[$key] = $value; 
	} 
	return $convertedArray; 
} 		
function convertUtf8(array $array){
    foreach($array as $key => $value){
        if(is_array($value)){
            $array[$key] = convertUtf8($value);
        }
        else{
            $array[$key] = mb_convert_encoding($value, 'UTF-8', 'HTML-ENTITIES');
        }
    }
    return $array;
}
function utf8_for_xml($string){
	return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}
function decode_utf8_to_html($string){
	return html_entity_decode(iconv(mb_detect_encoding($string, mb_detect_order(), true), "UTF-8", $string), ENT_QUOTES);
}

function isset_in_object($object, $key, $default){
	return isset($object->{$key})?$object->{$key}:$default;
}
