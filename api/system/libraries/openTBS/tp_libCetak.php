<?php 
class printClass {
    private static $currentStyles = array();    

    public function __construct() {}

    public function format($string) {
            if($string !=""){
            return preg_replace_callback("#<b>|<u>|<i>|</b>|</u>|</i>#",
                                        'printClass::replaceTags',
                                        $string);
        }else{
            return false;
        }
    }


    private static function applyStyles() {
		$styles ='';
        if(count(self::$currentStyles) > 0 ) {

            foreach(self::$currentStyles as $value) {

                if($value == "b") {
                    $styles .= "<w:b/>";
                }   

                if($value == "u") {
                    $styles .= "<w:u w:val=\"single\"/>";
                }   

                if($value == "i") {
                    $styles .= "<w:i/>";
                }
            }

            return "<w:rPr>" . $styles . "</w:rPr>";
        }else{
            return false;
        }
    }



    private static function replaceTags($matches) {

        if($matches[0] == "<b>") {
            array_push(self::$currentStyles, "b");
        }   

        if($matches[0] == "<u>") {
            array_push(self::$currentStyles, "u");
        }   

        if($matches[0] == "<i>") {
            array_push(self::$currentStyles, "i");
        }

        if($matches[0] == "</b>") {
            self::$currentStyles = array_diff(self::$currentStyles, array("b"));
        }   

        if($matches[0] == "</u>") {
            self::$currentStyles = array_diff(self::$currentStyles, array("u"));
        }   

        if($matches[0] == "</i>") {
            self::$currentStyles = array_diff(self::$currentStyles, array("i"));
        }

        return "</w:t></w:r><w:r>" . self::applyStyles() . "<w:t xml:space=\"preserve\">";
    }

	public function clearTags($str){
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