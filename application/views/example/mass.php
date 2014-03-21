<!doctype html>
<html>
    <head>
        <title>mass Framework测试</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <%block name="custom_js"%><%/block%>
        <script src="http://dev.static.quanloo.sii.sdo.com:8181/js/mass/mass.js"></script>
         <!--  <script src="http://dev.static.quanloo.sii.sdo.com:8181/js/third/jquery-1.7.1.min.js"></script> -->

        <style type="text/css">
        </style>

        <script>
            (function() {
                //数据发送页的地址，回调，可配置项
                // document.domain = "quanloo.com"
                var w3c = document.removeEventListener
                var $ = {
                    noop: function(){},
                    bind: w3c ? function( el, type, fn, phase ){
                        el.addEventListener( type, fn, !!phase );
                        return fn;
                    } : function( el, type, fn ){
                        el.attachEvent && el.attachEvent( "on"+type, fn );
                        return fn;
                    },
                    unbind: w3c ? function( el, type, fn, phase ){
                        el.removeEventListener( type, fn || $.noop, !!phase );
                    } : function( el, type, fn ){
                        if ( el.detachEvent ) {
                            el.detachEvent( "on" + type, fn || $.noop );
                        }
                    }
                }
                var bindIframe = function( doc, url, callback, timeout ){
                    var el = doc.createElement('iframe');
                    var body = doc.body || doc.documentElement;
                    el._state = 0;
                    $.bind(el, "load", function(){
                        if(el._state === 1 ) {
                            var data
                            try {
                                data = el.contentWindow.name;
                            } catch(e) {
                                console.log("===============")
                                console.log(e.message+"!")
                            }
                            el._state = 2;
                            console.log(typeof data)
                            callback(data)
                            callback = $.noop;//防止旧式的标准浏览器二次触发它
                            //   el.parentNode.removeChild(el)
                        } else if(el._state === 0) {
                            //  setTimeout(function(){
                            el._state = 1;//跳回本域
                            el.contentWindow.location.replace("about:blank");
                            //  }, (window.opera ? timeout : 31) )
                        }
                    });
                    body.insertBefore( el, body.firstChild );
                    console.log(body.tagName)
                    el.src = url;
                }



                var UIloader = function( url, callback, opts){

                    var type, timeout;
                    url += (url.indexOf('?') > 0 ? '&' : '?') + '_time'+ new Date * 1
                    switch( typeof opts ){
                        case "number":
                            timeout = opts;//用于延迟opera数据接收时刻
                            break;
                        case "string":
                            type = opts;//接受的数据类型
                        case "object":
                            if(opts){//有可能是null
                                timeout = opts.timeout
                                type = opts.type
                            }
                    }
                    timeout = timeout || 3000
                    var body = document.body || document.documentElement;
                    var node = document.createElement("iframe")
                    node.attachEvent('onload', function(){

                        var doc = node.contentDocument || node.contentWindow.document;
                        // doc.write( "<script>document.domain = parent.document.domain;<\/script>" );
                     //   doc.close();
                        console.log("0000000000000000")
                        bindIframe( doc, url, callback, timeout );

                    });

                    body.insertBefore( node, body.firstChild );
                    node.src = 'javascript:document.open();document.write("<script>document.domain = \''+document.domain+'\'<\/script>");';
                    //  node.src = "javascript:false";

                }
                UIloader("http://www.cnblogs.com/rubylouvre/archive/2012/07/28/2613565.html",function( data ){
                    window.console &&  console.log( data );
                })
            })();


        </script>

    </head>
    <body>
        <h1>测试专用页</h1>
        <form>
            <input id="aa"/>
        </form>
    </body>
</html>