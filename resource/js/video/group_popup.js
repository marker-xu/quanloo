

/**
 * 圈子-弹出框
 * x@btbtd.org  2012-2-17 
 */ 
var popup_group_box;

Dom.ready
(
    function()
    {
        var ckName = 'uid';
        
//         window.is_need_pop = 1;
        
        if( window.is_need_pop )
        {
            if( !window.UID ) return;
            if( QW.Cookie.get( ckName ) && QW.Cookie.get( ckName ) == window.UID ) 
            {
                return;
            }
        
            popup_group_box = W('#popup_group_box');
        
            QW.Ajax.get
            (
                '/guesslike/getpopcircles', 
                { offset: 0, count: 6, format: 'html' }
                , function( e )
                {
                    if( e && e.err == "ok" && e.data && e.data.html && popup_group_box.length )
                    {
                        XDialog.exec
                        (
                            {
                                "tpl": e.data.html
                                , submitCallback: 
                                function( $ele, $evt )
                                { 
                                    var p = this;
                                    var box = W( this.model.getDialog() );
                                    var checklist = box.query( '.pg_mid input[type=checkbox]');                                    

                                    var result = [];
                                    
                                    checklist.forEach
                                    (
                                        function(ele)
                                        {
                                            if( W(ele).get('checked') )
                                            {
                                                result.push( W(ele).get('value') );
                                            }                    
                                        }
                                    );
                                    
                                    if( !result.length )
                                    {
                                        alert( '请至少选择一个圈子!' );
                                        return false;
                                    }
                                    
                                    QW.Ajax.get
                                    (
                                        '/circle/batchsubscribe', 
                                        { "ids": result.join() }
                                        , function( e )
                                        {
                                            if( e && e.err == "ok" )
                                            {
                                                alert( '已经成功选择关注的圈子!' );
                                                
                                                p.hide();
                                                                        
                                                location.reload( location.href.split('#')[0] );
                                            }
                                        }
                                        
                                        , {
                                            onerror:
                                            function()
                                            {
                                                alert( '操作失败!' );
                                            }
                                        }
                                    );
                                    
                                    return false;
                                }
                                
                                , initCallback:
                                function()
                                {
                                    var box = W( this.model.getDialog() );        
                                    var closely_all = box.query('.closely_all');
                                    var checklist = box.query( '.pg_mid input[type=checkbox]');
    
                                    closely_all.on
                                    (
                                        "click",
                                        function()
                                        {
                                            if( W(this).get('checked') )
                                            {            
                                                checklist.set('checked', true);
                                            }
                                            else
                                            {            
                                                checklist.set('checked', false);
                                            }
                                            return true;
                                        }
                                    );
    
                                    closely_all.set('checked', true);
                                    checklist.set('checked', true); 
    
                                    checklist.on
                                    (
                                        'click',
                                        function()
                                        {
                                            var all_checked = true;
                                            
                                            checklist.forEach
                                            (
                                                function( ele )
                                                {
                                                    if( !W(ele).get('checked') )
                                                    {
                                                        all_checked = false;
                                                        return false;
                                                    }
                                                }
                                            );
                                            
                                            if( all_checked )
                                            {
                                                closely_all.set('checked', true);
                                            }
                                            else
                                            {
                                                closely_all.set('checked', false);
                                            }
                                            
                                            return true;
                                        }
                                    );
                                }//
                            }
                        );
            
                        QW.Cookie.set( ckName, window.UID );
                    }
                }
                
                , {
                    onerror:
                    function()
                    {
                    }
                }
            );
        }
    }
);
