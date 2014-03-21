<?php

/**
 * Profiler
 * @author wangjiajun
 */
class Profiler extends Jkit_Profiler
{
    // 执行时间超过该值的方法会记录日志，单位ms
    public static $_methodExecTimeThreshold = 100;
    private static $_methodExecTime = array();
    
    public static function startMethodExec()
    {
        array_push(self::$_methodExecTime, microtime(true));
    }
    
    public static function endMethodExec($message)
    {
        $duration = (microtime(true) - array_pop(self::$_methodExecTime)) * 1000;
        if ($duration >= self::$_methodExecTimeThreshold) {
            Kohana::$log->info("\"$message, exec take: $duration ms.\"");
        }
    }
}