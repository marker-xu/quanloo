<?php
class Controller_Admin_Wordlist extends Controller_Admin
{
    protected $_arrWordlistMap = array(
        'wl_killed' => '必杀词词表',        
    );
    public function before()
    {
        parent::before();
        
	    $this->_checkPrivilege(self::RES_SENSITIVE_WORDS, self::PRIV_VIEW);
    }

    public function action_index()
    {
        $cur_tbname = trim($this->request->param('tbname', 'wl_killed'));
        $orderby = trim($this->request->param('orderby', 'addtime'));
        $orderseq = (int) $this->request->param('orderseq', -1);
        if ($orderseq != 1) $orderseq = -1;
        $wordlen = (int) $this->request->param('word_len', 0);
        $keyword = $this->request->param('word', '');
        $intOffset = (int) $this->request->param('offset', 0);
        $intCount = 50;
        
        $arrCond = array('tbname' => $cur_tbname);
        if ($wordlen > 0) {
            $arrCond['word_len'] = $wordlen;
        }
        if (strlen($keyword) > 0) {
            $arrCond['word'] = new MongoRegex("/$keyword/i");        
        }
        $objDataAdminwordlist = new Model_Data_Adminwordlist();
        $arrWord = $objDataAdminwordlist->find($arrCond, array(), array($orderby => $orderseq), $intCount, $intOffset);
        $intTotal = $objDataAdminwordlist->count($arrCond);
        $this->template->set('words', $arrWord);
        $this->template->set('cur_tbname', $cur_tbname);
        $this->template->set('cur_tbname_nick', $this->_arrWordlistMap[$cur_tbname]);
        $this->template->set('wordlist_map', $this->_arrWordlistMap);
        $this->template->set('orderby', $orderby);
        $this->template->set('orderseq', $orderseq);
        $this->template->set('pager', array('count' => $intCount, 'offset' => $intOffset, 'total' => $intTotal));
    }

    public function action_add()
    {
	    $this->_checkPrivilege(self::RES_SENSITIVE_WORDS, self::PRIV_ADD);
	    
        $cur_tbname = '';
        if ($this->request->method() == 'POST') {
            $objDataAdminwordlist = new Model_Data_Adminwordlist();
            $intSuccessNum = 0;
            $bolFatalErr = false;
            $strErrMsg = null;
            $arrPost = $this->request->post();
            $cur_tbname = trim($arrPost['tbname']);
            if (! isset($this->_arrWordlistMap[$cur_tbname])) {
                $strErrMsg = "选择的词表名[$cur_tbname]不存在";
                $bolFatalErr = true;
            }
            if (! $bolFatalErr && ! empty($arrPost['words'])) {
                $arrWord = explode("\n", $arrPost['words']);
                if (! empty($arrWord)) {
                    $intSuccessNum += $objDataAdminwordlist->add($this->_user, $cur_tbname, $arrWord);
                }                
            }
            
            if (! $bolFatalErr && isset($_FILES['wordsfile']) && $_FILES['wordsfile']['error'] == UPLOAD_ERR_OK) {
                $strTmpFname = $_FILES['wordsfile']['tmp_name'];
                $arrWord = file($strTmpFname);
                if (isset($arrWord[0])) {
                    //解决上传的utf-8文件的BOM头问题
                    $arrWord[0] = str_replace(urldecode('%EF%BB%BF'), '', $arrWord[0]);
                }
                if (! empty($arrWord)) {
                    $intSuccessNum += $objDataAdminwordlist->add($this->_user, $cur_tbname, $arrWord);
                }
            }
            if ($strErrMsg) {
                $this->template->set('err_msg', $strErrMsg);
            } else {
                $this->template->set('err_msg', "成功添加{$intSuccessNum}个关键词");
            }
        }
        
        $this->template->set('cur_tbname', $cur_tbname);
        $this->template->set('wordlist_map', $this->_arrWordlistMap);    
    }

    public function action_delete()
    {
	    $this->_checkPrivilege(self::RES_SENSITIVE_WORDS, self::PRIV_DELETE);
	    
        $id = trim($this->request->param('id'));
        $bolRet = false;
        if ($id) {
            $objDataAdminwordlist = new Model_Data_Adminwordlist();
            $bolRet = $objDataAdminwordlist->remove($id);
        }
        if ($bolRet) {
            $this->ok();
        } else {
            $this->err(null, '删除失败');
        }
    }

    public function action_publish()
    {
	    $this->_checkPrivilege(self::RES_SENSITIVE_WORDS, self::PRIV_MODIFY);
	    
        $cur_tbname = trim($this->request->param('tbname', ''));
        if (! isset($this->_arrWordlistMap[$cur_tbname])) {
            $strErrMsg = "选择的词表名[$cur_tbname]不存在";
            $this->err(null, $strErrMsg);
        }
        $resTrie = trie_filter_new();
        if (! $resTrie) {
            $this->err(null, "无法创建Trie树");
        }        
        $arrCond = array('tbname' => $cur_tbname);
        $objDataAdminwordlist = new Model_Data_Adminwordlist();
        $arrWord = $objDataAdminwordlist->find($arrCond, array('word'));
        foreach ($arrWord as $v) {
            if (! trie_filter_store($resTrie, $v['word'])) {
                $this->err(null, "构建Trie树失败");
            }
        }
        $arrConf = Kohana::$config->load('admin')->adminwordlist;
        $strRsyncPasswd = Kohana::$config->load('admin')->rsync_passwd;
        if (empty($arrConf) || ! isset($arrConf['path'])) {
            $this->err(null, "请先配置词表存放路径");
        }
        if (! is_dir($arrConf['path'])) {
            if (! mkdir($arrConf['path'], 0777, true)) {
                $this->err(null, "无法创建存放路径[{$arrConf['path']}]");
            }
        }
        $strFilename = $arrConf['path'] . "/{$cur_tbname}.trie";
        $strFilenameTmp = $strFilename . posix_getpid();
        $bolRet = trie_filter_save($resTrie, $strFilenameTmp);
        if (! $bolRet) {
            $this->err(null, "写入文件失败，是不是磁盘满了");
        }
        if (! rename($strFilenameTmp, $strFilename)) {
            $this->err(null, "重命名文件失败");
        }
        if (! isset($arrConf['server']) || empty($arrConf['server'])) {
            $this->err(null, "没有需要同步的服务器");
        }
        $strDestPath = dirname($strFilename);
        $strDestPath = substr($strDestPath, strpos($strDestPath, '/', 6)); //去掉/home/{user}
        $arrTmp = array();       
        foreach ($arrConf['server'] as $v) {
            $strTmp = "rsync -avz $strFilename {$arrConf['server_user']}@{$v}::home{$strDestPath}/"; //末尾加/，如果不存在目录则会自动创建
            JKit::$log->debug($strTmp);
            $arrTmp[] = $strTmp;
        }
        $strTmp = implode(' && ', $arrTmp);
        $strExecCmd = "export RSYNC_PASSWORD={$strRsyncPasswd} && $strTmp > /dev/null 2>&1";
        JKit::$log->debug($strExecCmd);
        $intRet = 0;
        exec($strExecCmd, $arrNouse, $intRet);
        if ($intRet) {
            $this->err(null, "同步到远程机器时发生错误");
        } else {
            $this->ok();
        }
    }
    
    public function action_publishtxt()
    {
    	$this->_checkPrivilege(self::RES_SENSITIVE_WORDS, self::PRIV_MODIFY);
    	 
    	$cur_tbname = trim($this->request->param('tbname', ''));
    	if (! isset($this->_arrWordlistMap[$cur_tbname])) {
    		$strErrMsg = "选择的词表名[$cur_tbname]不存在";
    		$this->err(null, $strErrMsg);
    	}

    	$arrCond = array('tbname' => $cur_tbname);
    	$objDataAdminwordlist = new Model_Data_Adminwordlist();
    	$arrWord = $objDataAdminwordlist->find($arrCond, array('word'));
    	$strTmp = '';
    	foreach ($arrWord as $v) {
    		$strTmp .= "$v\n";
    	}    	
    	$arrConf = Kohana::$config->load('admin')->adminwordlist;
    	if (empty($arrConf) || ! isset($arrConf['path'])) {
    		$this->err(null, "请先配置词表存放路径");
    	}
    	if (! is_dir($arrConf['path'])) {
    		if (! mkdir($arrConf['path'], 0777, true)) {
    			$this->err(null, "无法创建存放路径[{$arrConf['path']}]");
    		}
    	}
    	$strFilename = $arrConf['path'] . "/{$cur_tbname}.txt";
    	$strFilenameTmp = $strFilename . posix_getpid();
    	file_put_contents($strFilenameTmp, $strTmp);
    	if (! rename($strFilenameTmp, $strFilename)) {
    		$this->err(null, "重命名文件失败");
    	}

    	$this->ok();
    }
}