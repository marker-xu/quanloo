<?php
/**
 * 从URL中抽取搜索词
 */
class Model_Logic_Searchword extends Model
{
	protected $_strCharset = 'UTF-8';
	protected $_arrSiteInfo = array(
		//array('host' => '域名中的关键标识', 'var' => array(搜索词的变量名), 'encode_var' => 搜索词编码变量, 'charset' => 搜索词默认编码),
		array('host' => '.google.', 'var' => array('q'), 'encode_var' => 'ie', 'charset' => 'UTF-8'),
		array('host' => '.baidu.', 'var' => array('wd', 'word'), 'encode_var' => 'ie', 'charset' => 'GBK'),
		array('host' => '.sogou.', 'var' => array('query'), 'encode_var' => null, 'charset' => 'GBK'),
		array('host' => '.zhongsou.', 'var' => array('w'), 'encode_var' => null, 'charset' => 'GBK'),
		array('host' => '.yahoo.', 'var' => array('p', 'q'), 'encode_var' => 'ei', 'charset' => 'UTF-8'),
		array('host' => '.soso.', 'var' => array('w'), 'encode_var' => 'ie', 'charset' => 'GBK'),
		array('host' => '.youdao.', 'var' => array('q'), 'encode_var' => 'ue', 'charset' => 'UTF-8'),
		array('host' => '.gougou.', 'var' => array('search'), 'encode_var' => null, 'charset' => 'GBK'),
		array('host' => '.bing.', 'var' => array('q'), 'encode_var' => null, 'charset' => 'UTF-8'),
	);
	//mb_detect_encoding返回值到标准字符集名字的映射
	protected $_arrCharsetName = array('CP936' => 'GBK', 'GB18030' => 'GBK', 'CP54936' => 'GBK');
	
	public function __construct($strCharset = null)
	{
		if ($strCharset) {
			$this->_strCharset = $strCharset;
		}
	}
	
	/**
	 * 获取搜索引擎查询url中的搜索词
	 * 
	 * @param string $strUrl 含有搜索词的url
	 * @return string 返回识别出的搜索词，如果没有则返回null
	 */
	public function getWord($strUrl) {
		$strUrl = trim($strUrl);
		if (! $strUrl) {
			return null;
		}
		
		$arrUrlInfo = parse_url($strUrl);
		if (! is_array($arrUrlInfo) || ! isset($arrUrlInfo['host']) || empty($arrUrlInfo['host'])) {
			return null;
		}
		
		//根据host确定是哪个搜索引擎
		$arrCurSite = null;
		$strHost = strtolower($arrUrlInfo['host']);
		foreach ($this->_arrSiteInfo as $v) {
			if (strpos($strHost, $v['host']) !== false) {
				$arrCurSite = $v;
				break;
			}
		}
		if ($arrCurSite === null) {
			return null;
		}
		
		//分析GET参数，找出原始的搜索词
		$arrParam = array();
		if (isset($arrUrlInfo['query']) && ! empty($arrUrlInfo['query'])) {
			parse_str($arrUrlInfo['query'], $arrParam);
		}
		$strRawWord = null;
		foreach ($arrCurSite['var'] as $strVarName) {
			if (array_key_exists($strVarName, $arrParam)) {
				$strRawWord = trim($arrParam[$strVarName]);
				break;
			}
		}
		if ($strRawWord === null || strlen($strRawWord) < 1) {
			return null;
		}
		
		//处理字符编码的问题
		$strEncode = $this->_detectEncoding($strRawWord, 'UTF-8,CP936');//$this->_log("detect $strEncode");
		if ($strEncode !== false) {
			$strEncode = strtolower($strEncode);
			if ($strEncode == 'utf-8' || $strEncode == 'gbk') {
				//信任它的检测结果
			} else {
				$strEncode = false;
			}
		}
		
		//URL中如果有编码类型参数
		if ($arrCurSite['encode_var'] && isset($arrParam[$arrCurSite['encode_var']])) {
			$strTmp = trim($arrParam[$arrCurSite['encode_var']]);
			if (! empty($strTmp)) {//$this->_log("orig $strTmp");	
				if (strncasecmp($strTmp, 'gb', 2) == 0) {
					//针对gb2312 gbk gb18030的hack，统一认为是gbk
					$strTmp = 'GBK';
				}			
				$strEncode = $strTmp;
			}
		}
		if (! $strEncode) {
			$strEncode = $arrCurSite['charset'];
		}

		$strTransWord = $strRawWord;//$this->_log("convert {$strEncode}-{$this->_strCharset}");
		if (strtolower($this->_strCharset) != strtolower($strEncode)) {
			$strTransWord = mb_convert_encoding($strRawWord, $this->_strCharset, $strEncode);
			if (strlen($strTransWord) > 0) {
				//检测转码后的字符串，如果存在非法字符，则认为转码失败
				$strEncode = $this->_detectEncoding($strTransWord, $this->_strCharset);
				if (strtolower($this->_strCharset) != strtolower($strEncode)) {
					$strTransWord = null;
				}
			} else {
				$strTransWord = null;
			}
		}
		return $strTransWord;
	}
	
	protected function _detectEncoding($strWord, $mixedCharset, $bolStrict = true) {
		$strEncode = mb_detect_encoding($strWord, $mixedCharset, $bolStrict);
		if (isset($this->_arrCharsetName[$strEncode])) {
			//统一字符集的名字，如CP936其实就是GBK
			$strEncode = $this->_arrCharsetName[$strEncode];
		}

		return $strEncode;
	}
	
	public function testGetWord() {
		$arrUrl = array(
			'http://www.google.com.tw/url?sa=t&rct=j&q=bing+++%E6%90%9C%E7%B4%A2%E6%A1%86&source=web&cd=1&ved=0CFoQFjAA&url=http%3A%2F%2Fwww.amznz.com%2Fbing-site-search%2F&ei=JSAFUJ2AMoabiQet2fjSCA&usg=AFQjCNHWhT7ei58Q2qa11H33LlPCOCywAw&cad=rjt',
			'http://www.gougou.com/search?search=%E7%BE%8E%E5%A5%B3&id=1',
			'http://cn.bing.com/search?q=%E5%9C%88%E4%B9%90&go=&qs=bs&form=QBRE',
			'http://www.google.com.hk/url?sa=t&rct=j&q=%E5%9C%88%E4%B9%90&source=web&cd=1&ved=0CGMQFjAA&url=http%3A%2F%2Fwww.quanloo.com%2F&ei=ySAFUMuaOvGQiQeQu6jSCA&usg=AFQjCNFtC6mo3r_4Vb57zv-pq09EA8vYxQ&cad=rjt',
			'http://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=1&ved=0CGAQFjAA&url=http%3A%2F%2Fwww.quanloo.com%2F&ei=NyEFUK-0IM-ZiAepsMm-CA&usg=AFQjCNFtC6mo3r_4Vb57zv-pq09EA8vYxQ&sig2=Efft66le4osJS9HGroUruQ',
			'http://www.google.com.sg/url?sa=t&rct=j&q=%E5%9C%88%E4%B9%90&source=web&cd=1&ved=0CGUQFjAA&url=http%3A%2F%2Fwww.quanloo.com%2F&ei=wCEFUNPpB6ykiAeBs-XOCA&usg=AFQjCNFtC6mo3r_4Vb57zv-pq09EA8vYxQ&cad=rjt',
			'http://www.youdao.com/search?q=%E7%BE%8E%E5%A5%B3&ue=utf8&keyfrom=web.index',
			'http://www.soso.com/q?ie=utf-8&w=%E7%BE%8E%E5%A5%B3%E5%9B%BE%E7%89%87&pid=sb.idx&ch=sb.c.idx&cid=s.idx.smb',
			'http://www.soso.com/q?sc=web&bs=%C8%A6%C0%D6&ch=w.uf&num=10&w=%C8%A6%C0%D6',
			'http://www.yahoo.cn/s?q=%E5%9C%88%E4%B9%90&bs=&oq=%E5%9C%88%E4%B9%90',
			'http://www.yahoo.cn/s?q=%E7%BE%8E%E5%A5%B3&bs=&oq=%E7%BE%8E%E5%A5%B3',
			'http://search.yahoo.com/search;_ylt=AqMYJs2DQ8dbfYFyDiq7NtybvZx4?p=%E5%9C%88%E4%B9%90&toggle=1&cop=mss&ei=UTF-8&fr=yfp-t-701',
			'http://www.zhongsou.com/third.cgi?w=%C8%A6%C0%D6&dt=2&pt=2&y=5',
			'http://www.sogou.com/web?query=%C8%A6%C0%D6&_asf=www.sogou.com&_ast=1342514432&w=01019900&p=40040100&sut=2063&sst0=1342514432141',
			'http://www.baidu.com/s?wd=%E5%9C%88%E4%B9%90&rsv_spt=1&issp=1&rsv_bp=0&ie=utf-8&tn=baiduhome_pg&inputT=1222',				
		);
		foreach ($arrUrl as $v) {
			$t = $this->getWord($v);
			echo "$v==[$t]<br>\n";
		}
	}
	
	public function testIsSearch($strUrl) {
		foreach ($this->_arrSiteInfo as $v) {
			if (strpos($strUrl, $v['host']) !== false) {
				return true;
			}
		}

		return false;
	}
	
	protected function _log($str) {
		//echo "<br>\n$str<br>\n";
	}
}
