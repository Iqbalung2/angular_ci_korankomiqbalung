<?php 


function str_replace_first($from, $to, $subject)
{
    $from = '/'.preg_quote($from, '/').'/';

    return preg_replace($from, $to, $subject, 1);
}

function make_wherein_from_array($array=array())
{	
	foreach ($array as $key => $value) {
		$array[$key] = "'$value'";
	}
	$string_array = implode(',', $array);
	return $string_array;
}