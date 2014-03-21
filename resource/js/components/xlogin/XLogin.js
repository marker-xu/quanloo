/**
 * 用户操作触发登陆后, 登陆成功完成未登陆前的操作
 * @className   XLogin
 * @author      x@btbtd.org
 * @date        2011-7-22  
 */ 
void function()
{    
    window.XLOGIN_INS;

    function Control( $params )
    {
        this.model = new Model($params).init();
    }
    /**
     * 页面级回调, 如果回调存, 则登陆成功后, 
     * 除了存储的操作, 其他操作将在回调里完成
     */         
    Control.callback = null;
    Control.debug = false;
    Control.forceRefresh = false; //未登陆操作完成后, 是否强刷页面, XLogin.ondone() 检测这一状态
    /**
     * 更新全局状态, 比如头部
     */    
    Control.loginedCallback = null;
    
    Control.exec =
    function( $params )
    {
        return window.XLOGIN_INS || new Control( $params ).init();
    };    
    /*
        var $srcData = {
            'source': this
            , 'event': e.type
        };
        
        window.XLogin && XLogin.storeTriggerData( $resultData, $srcData );
    */
    /**
     * 
     */         
    Control.storeTriggerData = 
    function( $loginedData, $sendData )
    {
        Control.exec().model.storeTriggerData( $loginedData, $sendData );
    };
    
    Control.ondone =
    function( $url )
    {
        $url = $url || location.href;
        
        if( Control.forceRefresh )
        {
            location.replace( $url.split('#')[0] );
        }
    };
    
    Control.onlogin =
    function( $data )
    {
        Control.exec().onlogin( $data );     
    };
    
    Control.fireAction =
    function( $data )
    {
        Control.exec().onlogin( $data );     
    };
    
    Control.resetData =
    function()
    {
        Control.exec().model.resetData();  
    };
    /*
    <script>
        if( window.parent && parent != window && parent.XLogin )
        {
            parent.XLogin.fireAction
            (
                {
                    "err": "ok", "url": "/", "msg": ""
                }
            );
        }
    </script>
    
    如果 url 为空, 则刷新当前页面;
    */
    
    Control.prototype =
    {
        init:
        function()
        {
            window.XLOGIN_INS = this;
            
            return this;
        }    
        /**
         * 解析登陆后的返回数据    
         */
        , onlogin:
        function($data)
        {
            var p = this;
            
            if( !window.LOGIN_IFRAME_URL ) return;
            
            if( $data && $data.err == 'ok' )
            {
                try{ $data.url = decodeURIComponent( $data.url||location.href ); }catch(ex){}
                
                if( Control.loginedCallback && !Control.forceRefresh ) Control.loginedCallback();
                
                if( Control.callback )
                {
                    if( p.model.hasData()  )
                    {   
                        if( Control.debug ) document.title = 'XLogin: 1';
                        
                        if( p.model.sendData.source && p.model.sendData.event )
                        {
                            //XDialog.closeAll();
                            if( window.LGOIN_POP_INS )
                            {
                                if( window.LGOIN_POP_INS ){ try{ window.LGOIN_POP_INS.view.close(); }catch(ex){} }
                            }
                            Control.callback( p.model.loginedData, p.model.sendData );
                            /**
                             * 这里 event IE可能有BUG, 需要去掉^on
                             */                                                    
                            if(typeof p.model.sendData.event == 'string' )
                            {
                                W(p.model.sendData.source).fire( p.model.sendData.event );
                            }
                            //document.title += ', ' + p.model.sendData.event;
                        }
                        else
                        {
                            QW.Ajax[ p.model.sendData.method ]
                            (
                                p.model.sendData.url
                                , p.model.sendData.data
                                        
                                , function( $d )
                                {
                                     XDialog.closeAll();
                                    Control.callback( p.model.loginedData );
                                }
                                        
                                , {
                                    onerror:
                                    function()
                                    {
                                        XDialog.closeAll();
                                        Control.callback( p.model.loginedData );
                                    }
                                }
                            );
                        }
                    }
                    else
                    {
                        if( Control.debug ) document.title = 'XLogin: 2';
                        XDialog.closeAll();
                        Control.callback( p.model.loginedData );
                    }
                }
                else
                {
                    if( p.model.hasData() )
                    {
                        if( Control.debug ) document.title = 'XLogin: 3';
                        if( p.model.sendData.source && p.model.sendData.event )
                        {
                            XDialog.closeAll();
                            /**
                             * 这里 event.type IE可能有BUG, 需要去掉^on
                             */                             
                            if(typeof p.model.sendData.event == 'string' )
                            {
                                W(p.model.sendData.source).fire( p.model.sendData.event );
                            }
                            
                            //document.title += ', ' + p.model.sendData.event;
                        }
                        else
                        {
                            QW.Ajax[ p.model.sendData.method ]
                            (
                                p.model.sendData.url
                                , p.model.sendData.data
                                        
                                , function( $d )
                                {
                                     XDialog.closeAll();
                                }
                                        
                                , {
                                    onerror:
                                    function()
                                    {
                                        XDialog.closeAll();
                                    }
                                }
                            );
                        }
                        //location.href = $data.url;
                    }
                    else
                    {
                        if( Control.debug ) document.title = 'XLogin: 4';
                        
                        XDialog.closeAll();
                        
                        var $url = location.href;
                        
                        if( Control.forceRefresh )
                        {
                            location.replace( $url.split('#')[0] );
                        }
                    }
                }
            }   
        }
        /**
         * 解析触发成功后的操作
         */                 
        , parseloginedData:
        function()
        {
        
        }
    }
    
    function Model($params)
    {
        this.sendData;
        this.loginedData;
        
        for( var k in $params ) this[k] = $params[k];
    }
    
    Model.prototype =
    {
        init:
        function()
        {
            return this;
        }
        /**
         * 存储操作, 如果操作需要登陆的话
         */      
        , storeTriggerData:
        function( $loginedData, $sendData )
        {
            var p = this;
            if( $loginedData )
            {                  
                if( $loginedData.err == 'sys.permission.need_login' || $loginedData.err == '' )
                {
                    p.loginedData = $loginedData;
                    p.sendData = $sendData || null;
                    
                    if( p.sendData )
                    {
                        p.sendData.method = p.sendData.method || 'get';
                        p.sendData.data = p.sendData.data || {};
                        p.sendData.data.rnd = new Date().getTime();
                    }
                }
                else
                {
                    p.loginedData = $loginedData;
                    p.sendData = null;
                }
            } 
        }
        , hasData:
        function()
        {
            var r = false;
            
            if( this.sendData )
            {
                if( this.sendData.url || (this.sendData.source && this.sendData.event) )
                {
                    r = true;
                }
            }
            
            return r;
        }
        , resetData:
        function()
        {
            this.sendData = null;
            this.loginedData = null;
        }
    };
    
    window.XLogin = Control;
}();
