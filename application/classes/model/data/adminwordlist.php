<?php
/**
 * 后台管理用的词表管理
 */
class Model_Data_Adminwordlist extends Model_Data_MongoCollection
{
    public function __construct() {        
        parent::__construct('web_mongo', 'video_search', 'adminwordlist');
    }

    /**
     * 添加关键词
     * adminwordlist表结构为(_id, tbname, word, word_len, adder_nick, adder_id, addtime)
     * @param array $arrAdder 添加者的信息
     * @param string $strTbname
     * @param array $arrWord
     * @return int 添加成功的个数
     */
    public function add($arrAdder, $strTbname, $arrWord) {
        $arrTmp = array();
        foreach ($arrWord as $v) {
            $v = trim($v);
            if (strlen($v) < 1) {
                continue;
            }
            $strId = "{$strTbname}_{$v}";
            $arrTmp[$strId] = array(
                '_id' => $strId,
                'tbname' => $strTbname,
                'word' => $v,
                'word_len' => mb_strlen($v, 'utf-8'),
                'adder_nick' => $arrAdder['nick'],
                'adder_id' => $arrAdder['_id'],
                'addtime' => time(),
            );
        }
        if (empty($arrTmp)) {
            return 0;
        }
        
        $intSuccessCount = 0;
        $arrTmp = array_chunk($arrTmp, 100, true);
        foreach ($arrTmp as $v) {
            //一次处理100个关键词
            try {
                $arrExistWord = $this->find(array('_id' => array('$in' => array_keys($v))), array('_id'));
            } catch (Exception $e) {
                JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
                break;
            }
            $v = array_diff_key($v, $arrExistWord);
            if (empty($v)) {
                continue;
            }
            try {
                $mixedRet = $this->getCollection()->batchInsert($v, array('safe' => true));
            } catch (Exception $e) {
                JKit::$log->warn($e->getMessage(), null, $e->getFile(), $e->getLine());
                break;
            }
            if (! isset($mixedRet['ok']) || $mixedRet['ok'] != 1) {
                break;
            }
            $intSuccessCount += count($v);
        }

        return $intSuccessCount;
    }
    
    public function remove($id) {
        $condition = array(
                "_id" => (string) $id
        );
        $ret = false;
        try {
            $ret = $this->getCollection()->remove($condition);         
        } catch (Exception $e) {
            JKit::$log->warn("remove word fail, code-".$e->getCode().", msg-".$e->getMessage().", id-".$id);
        }   
        return $ret;
    }
}
