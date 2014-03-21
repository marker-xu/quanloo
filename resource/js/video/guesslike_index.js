
/**
 * 猜你喜欢页JS
 */ 

GROUP_DATA_URL = '/guesslike/likecircles';
GROUP_INDEX = 0;
GROUP_COUNT = 4;
GROUP_IS_LOADING = false;
 
Dom.ready
(
    function()
    {
        var group_box = W('#group_box');
        var next_group = W('#next_group');
        
        next_group.on
        (
            "click",
            function( ex )
            {
                ex.preventDefault();
            
                if( GROUP_IS_LOADING ) return false;  
                
                GROUP_IS_LOADING = true;
    
                QW.Ajax.get
                (
                    GROUP_DATA_URL, 
                    { offset: GROUP_INDEX, count: GROUP_COUNT , _r : Math.random()}
                    , function( e )
                    {
                        if( e && e.err == "ok" && e.data && e.data.html )
                        {
                            group_box.html( e.data.html );
                            GROUP_IS_LOADING = false;
                            
//                             var ids = [];
//                             
//                             group_box.query('input[type=hidden]').forEach
//                             (
//                                 function( $e )
//                                 {
//                                     ids.push( W($e).val() );
//                                 }
//                             );
//                             
//                             circle_marmot.item_list = ids.join(',');
//                             
//                             QW.Marmot.log( circle_marmot );
                            
//                             INVITE_FRIEND( group_box )
//                             SHARE_GROUP( group_box )
                        }
                    }
                    
                    , {
                        onerror:
                        function()
                        {
                            GROUP_INDEX -= 4;
                            GROUP_IS_LOADING = false;
                        }
                    }
                );
                
                GROUP_INDEX += 4;
                
                return false;
            }
        );
        
        next_group.fire( 'click' );
    }
);


function sendVideoMarmot()
{
    return;
    var waterfall = W('#waterfall');
    if(!waterfall.length) return;
    
    var o = {};
    
    waterfall.query( 'a.i-mood' ).forEach
    (
        function( $e )
        {
            if( W($e).attr('data-action') )
            {
                o[ (W($e).attr('data-action').evalExp()).id ] = 1;
            }
        }
    );
    
    var a = [];
    for( var k in o )
    {
        a.push( k );
    }
    video_marmot.item_list = a.join(',');
    QW.Marmot.log( video_marmot );
}
