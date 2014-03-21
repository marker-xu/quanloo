<?php

/**
 * 异常处理
 * @author wangjiajun
 */
class ExceptionHandler
{
    public static function handle(Exception $e)
    {        
        switch (get_class($e))
        {
            case 'HTTP_Exception_404':
                $response = new Response();
                $response->status(404);
                $view = View::factory('error/404');
                echo $response->body($view)->send_headers()->body();
                break;
                
            default:
                JKit::$log->error($e);
                Util::sendSmsMonitor($e);
                if (JKit::$environment == JKit::DEVELOPMENT) {
                    Kohana_Exception::handler($e);
                } else {
                    $response = new Response();
                    $response->status(500);
                    $view = View::factory('error/500');
                    echo $response->body($view)->send_headers()->body();
                }
                break;
        }
    }
}