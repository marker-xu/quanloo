<?php defined('SYSPATH') or die('No direct script access.');

class Response extends JKit_Response
{    
	/**
	 * 返回图片
	 * @param string $data
	 * @param string $mime
	 * @param int $expire 缓存多长时间，单位为秒
	 * @param int $lastModified 最后修改时间
	 * @return void
	 */
    public function image($data, $mime, $expire = NULL, $lastModified = NULL) 
    {
    	$this->headers('Content-Type', $mime);
        if (!is_null($expire)) {
    	    $this->headers('Cache-Control', "max-age=$expire, public");
    	    $this->headers('Expires', gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
        }
        if (!is_null($lastModified)) {
    	    $this->headers('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        }
    	$this->body($data);
    }
    
    public function plain($body)
    {
    	$this->headers(array(
        	'Content-Type' => 'text/plain; charset=UTF-8'
        ));
        $this->body($body);
    }
    
    public function html($body)
    {
    	$this->headers(array(
        	'Content-Type' => 'text/html; charset=UTF-8'
        ));
        $this->body($body);
    }
    
    public function xml($body)
    {
    	$this->headers(array(
        	'Content-Type' => 'text/xml; charset=UTF-8'
        ));
        $this->body($body);
    }
    
    public function js($body)
    {
    	$this->headers(array(
        	'Content-Type' => 'text/javascript; charset=UTF-8'
        ));
        $this->body($body);
    }
    
    public function json1($data) 
    {
    	$this->headers(array(
        	'Content-Type' => 'application/json'
        ));
        $this->body(json_encode($data));
    }
    
    public function json2($errno = 0, $error = '', $data = null) 
    {
        $this->json1(array(
        	'errno' => $errno, 
        	'error' => $error,
            'data' => $data
        ));
    }
    
    public function alert($message, $go = 0)
    {
        $message = json_encode($message);
        $go = (int) $go;
        $body = <<<EOF
<script type="text/javascript">
var message = $message;
var go = $go;
window.alert(message);
if (go != 0) {
	window.history.go(go);
}
</script>
EOF;
        $this->body($body);
    }
    
    public function alertBack($message)
    {
        $this->alert($message, -1);
    }
    
    public function alertGo($message, $href = '/')
    {
        $message = json_encode($message);
        $href = json_encode($href);
        $body = <<<EOF
<script type="text/javascript">
var message = $message;
var href = $href;
window.alert(message);
window.location.href = href;
</script>
EOF;
        $this->body($body);
    }
    
    public function alertClose($message)
    {
        $message = json_encode($message);
        $body = <<<EOF
<script type="text/javascript">
var message = $message;
window.alert(message);
window.close();
</script>
EOF;
        $this->body($body);
    }
}