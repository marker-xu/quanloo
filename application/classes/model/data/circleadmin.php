<?php 

/**
 * 圈子
 * @author wangjiajun
 */
class Model_Data_CircleAdmin extends Model_Data_MongoCollection
{
	const OPTYPE_DEL = 1; //禁止视频出现在圈子中
	const OPTYPE_ADD = 2; //添加视频到圈子中
	 
	public function __construct()
	{
        parent::__construct('circle', 'video_search', 'circle_cms');
	}
	
	/**
	 * 圈子视频编辑
	 * @param array $arrParams
	 * @return bool
	 */
	public function addOpt($arrParams) {
		if ($arrParams['cid'] < 1 || $arrParams['vid'] == '') {
			return false;
		}
        $strId = "{$arrParams['cid']}_{$arrParams['vid']}";
        $ret = false;
        try {
            $ret = $this->update(array('_id' => $strId), $arrParams);
        } catch (Exception $e) {
            JKit::$log->warn("add fail, code-".$e->getCode().", msg-".$e->getMessage());
        }
        return $ret;
    }
    
    public function remove($id) {
    	if (strpos($id, '_') === false) {
    		//兼容老的_id，它是自增长的id
    		$mixedId = new MongoId($id);
    	} else {
    		$mixedId = strval($id);
    	}
        $condition = array(
            "_id" => $mixedId,
        );
        $ret = false;
        try {
            $ret = $this->delete($condition);           
        } catch (Exception $e) {
            JKit::$log->warn("remove fail, code-".$e->getCode().", msg-".$e->getMessage().", id-".$id);
        }   
        return $ret;
    }
}
