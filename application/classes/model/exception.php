<?php 

/**
 * Model层数据异常
 * @author wangjiajun
 */
class Model_Exception extends Kohana_Exception
{
    const ERROR_CODE_UNKNOWN = -1;
    
    public function __construct($message, $code = self::ERROR_CODE_UNKNOWN, $variables = NULL)
    {
        parent::__construct($message, $variables, $code);
    }
}