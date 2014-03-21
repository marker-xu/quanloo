/**
 * 返回页面顶部
 * @className   GoTop
 *   
 * @require     attach_event_f(叠加事件函数)
                viewport_f(获取页面屏幕大小的函数)
 *
 * @css
    <style>
        .go-top
        {
            background: url(images/go-top.png) no-repeat left top;
            width: 43px;
            height:44px;
            display:block;
            right: 40px;
            bottom: 40px;
            position:absolute;
            cursor:pointer;
        }
    </style>
 *  
 * @example
 *      GoTop.exec( {"id": "gotop", "right": 60, "bottom": 40 } );   
 *       
 * @author      x@btbtd.org
 * @date        2012-2-10  
 * @version     1.0 
 */ 
void function()
{
    var isIE6 = !-[1,] && !window.XMLHttpRequest;
    
    function Control( $params )
    {
        this.model = new Model($params).init();
        this.view = new View( this.model ).init();
    }
    
    Control.exec =
    function( $params )
    {
        return new Control( $params ).init();
    };
    
    Control.prototype =
    {
        init:
        function()
        {          
            //if( !this.model.id ) throw new Error('GoTop model.id is null!');
            if( !this.model.id ) return;
            
            var p = this;
            
            attach_event_f( window, 'resize', function(){ p.view.update(); } );
            attach_event_f( window, 'scroll', function(){ p.view.update(); } );
            
            p.view.update();
            
            return this;
        }    
    }
    
    function Model($params)
    {
        this.id;
        this.right = -1;
        this.bottom = -1;
        this.left = -1;
        this.top = -1;
        this.autoHide = true;
        
        for( var k in $params ) this[k] = $params[k];
    }
    
    Model.prototype =
    {
        init:
        function()
        {            
            this.id = document.getElementById(this.id);
            
            return this;
        }
        
        , width:
        function()
        {
            return this.id.offsetWidth;
        }
        
        , height:
        function()
        {
            return this.id.offsetHeight;
        }
    };
    
    function View( $model )
    {
        this.model = $model;
    }
    
    View.prototype = 
    {
        init:
        function()
        {
            return this;
        }
        
        , update:
        function()
        {
            var p = this;            
            var pos = viewport_f();            
            
            if( isIE6 )
            {
                if( pos.scrollTop > 0 || !p.model.autoHide )
                {
                    p.model.id.style.display = "block";
                }
                else
                {
                    p.model.id.style.display = "none";
                }
            
                if( p.model.right && p.model.right > -1 )
                {
                    p.model.id.style.left = pos.scrollLeft + pos.width - p.model.width() - p.model.right + 'px';
                }
            
                if( p.model.bottom && p.model.bottom > -1 )
                {
                    p.model.id.style.top = pos.scrollTop + pos.height - p.model.height() - p.model.bottom + 'px';
                }
            
                if( p.model.top && p.model.top > -1 )
                {
                    var temp = pos.scrollTop + p.model.top;
                
                    p.model.id.style.top = temp + 'px';
                }
            
                if( p.model.left && p.model.left > -1 )
                {
                    p.model.id.style.left = pos.scrollLeft + p.model.left + 'px';
                }            
            }
            else
            {
                p.model.id.style.position = 'fixed';
            
                if( pos.scrollTop > 0 || !p.model.autoHide )
                {
                    p.model.id.style.display = "block";
                }
                else
                {
                    p.model.id.style.display = "none";
                }
            
                if( p.model.right && p.model.right > -1 )
                {
                    p.model.id.style.right = p.model.right + 'px';
                }
            
                if( p.model.bottom && p.model.bottom > -1 )
                {
                    p.model.id.style.bottom = p.model.bottom + 'px';
                }
            
                if( p.model.top && p.model.top > -1 )
                {
                    p.model.id.style.top = p.model.top + 'px';
                }
            
                if( p.model.left && p.model.left > -1 )
                {
                    p.model.id.style.left = p.model.left + 'px';
                }  
            }
        }
    };
    
    window.GoTop = Control;
}();
