<?php
	include_once('openTBS/tp_libCetak.php');
	// load the TinyButStrong library
	if (version_compare(PHP_VERSION,'5')<0) {
		include_once('openTBS/tbs_class.php'); // TinyButStrong template engine for PHP 4
	} else {
		include_once('openTBS/tbs_class_php5.php'); // TinyButStrong template engine
	}

	// Excel plug-in for TBS 
	include('openTBS/tbs_plugin_opentbs.php');
	//require_once(dirname(__FILE__).'\html2pdf.php');
	class CI_Template_cetak{
		var $type="";
		function __construct($type=""){
			$this->type = $type;
		}
		function _templateExist($templateName=""){
			$isExist = file_exists($templateName);
			if (!$isExist) {
				show_error('Template tidak ada ('.basename ($templateName).')');
			}
			return $isExist;
		}
		function createNew ($type,$templateUri,$orientation = 'P', $format = 'A4', $langue='fr', $unicode=false, $encoding='UTF-8', $marges = array(5, 5, 5, 8)){
			
			if(!empty($type)){
				$this->type=$type;
			}
			if($this->type=='pdf'){
				return new HTML2PDF($orientation, $format, $langue,$unicode, $encoding, $marges);
			}	
			else if ($this->type == "doc"){
				return new clsTinyButStrong;
			}
			else if($this->type == "docx"){
				if($encoding == 'UTF-8' and $unicode) $mode = OPENTBS_ALREADY_UTF8;
				else $mode = OPENTBS_ALREADY_XML;
					
				$TBS = new clsTinyButStrong;
				$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
				$this->_templateExist($templateUri);
				$TBS->LoadTemplate($templateUri,$mode);
				return $TBS;
			}
			else if($this->type == "xls"){
				return new clsTinyButStrong;
			}
			else if($this->type == "xlsx"){
				$TBS = new clsTinyButStrong;
				$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
				$this->_templateExist($templateUri);
				$TBS->LoadTemplate($templateUri);
				return $TBS;
				
			}
			else if($this->type == "xls"){
				return new clsTinyButStrong;
			}	
			else {
				return new clsTinyButStrong;
			}			
		}
		function format($string){
			$string = $this->clearTags($string);
			$print = new printClass();
			return $print->format($string);			
		}	
		function clearTags($str){
			$str = str_replace('<p>','',$str);
			$str = str_replace('</p>','',$str);
			$str = str_replace('<div>','',$str);
			$str = str_replace('</div>','',$str);
			$str = str_replace('&#8203;',' ',$str);
			$str = str_replace('&nbsp;',' ',$str);
			$str = str_replace('&','&amp;',$str);
			$pos = strpos($str, '<blockquote>');
			if ($pos !== false) {
				$jml = substr_count($str, '<blockquote style');
				// echo $jml;break;
				for($i=1;$i<=$jml;$i++){
					$str = $this->hapus($str,'<blockquote style');
				}
				$str = str_replace('<br><blockquote>','<blockquote>',$str);
				$str = str_replace('<br></blockquote>','</blockquote>',$str);
				$str = preg_replace('<blockquote>','<blockquote1>',$str,1);
				$str = str_replace('</blockquote>','</blockquote><w:p><w:r><w:t>',$str);
				$str .= '</w:t></w:r></w:p>';
				$str = str_replace('</blockquote>','</w:t></w:r></w:p>',$str);
				$str = str_replace('<blockquote1>','w:p><w:pPr><w:ind w:left="720" /></w:pPr><w:r><w:t',$str);
				$str = str_replace('<blockquote>','</w:t></w:r></w:p><w:p><w:pPr><w:ind w:left="720" /></w:pPr><w:r><w:t>',$str);
				// $str = str_replace('<blockquote>','<w:p><w:pPr><w:ind w:left="720" /></w:pPr><w:r><w:t>',$str);
			} else {
			}
			$str = str_replace('<br>','<w:br/>',$str);
			return $str;
		}
		
	}
?>