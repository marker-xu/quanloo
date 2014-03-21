<?php
class Model_Logic_Blackword extends Model {
    const FILE_EXT = '.trie';
    
    protected static $_objSelf;
    protected $_arrWordlist;
    
    
    protected function __construct() {
        //    
    }
    
    public static function instance() {
        if (! self::$_objSelf) {
            self::$_objSelf = new self();
        }
        
        return self::$_objSelf;
    }
    
    /**
     * 检查是否存在违禁词
     * 
     * @param string $strTxt
     * @return bool 有违禁词返回true，否则false
     */
    public function filter($strTxt, $mixedTbname = array('wl_killed')) {
    	if(!$strTxt) {
    		return false;
    	}
        if (! is_array($mixedTbname)) {
            $mixedTbname = (array) $mixedTbname;
        }
        foreach ($mixedTbname as $v) {
            if (isset($this->_arrWordlist[$v])) {
                $resTrie = $this->_arrWordlist[$v];
                //JKit::$log->debug("wordlist cache $strFname");
            } else {
                $strFname = $this->_fullFname($v);
                //JKit::$log->debug("load wordlist $strFname");
                $resTrie = trie_filter_load($strFname);
                if (! $resTrie) {
                    //词表加载失败，默认为命中关键词
                    JKit::$log->warn("load wordlist $strFname fail");
                    return true;
                }
                $this->_arrWordlist[$v] = $resTrie;
            }
            $ret = trie_filter_search($resTrie, $strTxt);
            if ($ret && $ret[1] > 0) {
                $strSpamWord = substr($strTxt, $ret[0], $ret[1]);
                JKit::$log->debug("filter word $strSpamWord");
                return true;
            }            
        }
        
        return false;
    }
    
    protected function _fullFname($strTbname) {
        $strFile = APPPATH . "../../../data/wordlist/{$strTbname}" . self::FILE_EXT;
        return $strFile;
    }
}