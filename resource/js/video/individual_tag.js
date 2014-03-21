/**
 * 个人页/完善信息页 用户个人标签 
 */ 
window.USER_TAG_ = 20;

Dom.ready(function(){
    var rsplit = /[^,]+/g //用于分割标签
    //添加标签（私有方法)
    function addTag(tag){

        var array = W('#owned_tag').query('> li a').map(function(el){
            el =  QW.StringH.trim(el.innerHTML)
            return QW.StringH.decode4Html(el)
        });

        if( array.length >= window.USER_TAG_ ) {
            showTip("#err-input_tag",0,"只能添加"+window.USER_TAG_+"个标签");
            return false;
        }
        
        if( array.indexOf(tag) !== -1 ){
            showTip("#err-input_tag",0,"不能添加相同标签");
            return false;
        }
    
        showTip("#err-input_tag",0,"");
        W('<li><a href="###">'+tag+'</a></li>').insertTo('beforeend', W("#owned_tag")[0]);
        array.push(tag);
        W("#tags").setValue( array.join(',') );

        return true;
    }
    //回车添加
    W("#input_tag").on("keypress",function(e){
        var key = e.keyCode || e.which;
        if(key === 13){
            e.preventDefault();
            e.stopPropagation();
            
            W("#submit_tag").fire("click");
            
            return false;
        }
        return true;
    })
    
    W("#submit_tag").on("click",function(){
        var el = W("#input_tag");
        var tag = el.getValue() || "";
        tag = QW.StringH.trim(tag);//不能为空 只能是中英文，长度不超过三个字
        if(tag == ""){
            return showTip("#err-input_tag",0,"不能为空")
        }
        if(tag.length > window.USER_TAG_){
            return showTip("#err-input_tag",0,"超过"+window.USER_TAG_+"个字")
        }
        if(/^[\u4e00-\u9fa5a-z0-9]+$/i.test(tag) ){
            addTag(tag);
            el.setValue("")
        }else{
            showTip("#err-input_tag",0,"只能为中英文数字")
        }
    });
    //隐藏错误提示
    W("#input_tag").on("focus",function(){
        showTip("#err-input_tag",2)
    })
    //添加标签
    W("#guess_tag").delegate("a:not(.tag_added)","click",function(){
        var tag = QW.StringH.trim(this.innerHTML);
        tag = QW.StringH.decode4Html(tag)
        if(addTag(tag)){//我已经添加的标签
            W(this).addClass("tag_added");
            W("#owned_tag ul").appendChild(W('<li><a href="###">'+tag+'</a></li>')[0])
        }
    });


    //除选已选中的标签
    W("#owned_tag").delegate("a", "click",function(){
        var tag = this.innerHTML
        tag = QW.StringH.decode4Html(tag)
        var array = W("#tags").getValue().match(rsplit)||[];
        var index = array.indexOf(tag);
        W(this).parentNode().removeNode();//移除标签
        W("#guess_tag .tag_added:contains("+tag+")").removeClass("tag_added")
        if(index != -1){
            array.splice(index, 1);
            showTip("#err-input_tag",2);
        }
        W("#tags").setValue(array.join(","));
    });


    //换一换用户感兴趣的tag
    //URI：/recommend/ recommendusertags
    //请求参数：
    // offset– 起始位置，默认0
    // count– 数量，默认10个
    //返回数据：{"err": "ok","data":["tag1", "tag2"]}
    var change_offset = 0, requesting = 0
    W("#change_tag").on("click", function(){
        var array = W("#tags").getValue().match(rsplit)||[];
        if(!requesting){
            requesting = 1;
            change_offset+=10;
            QW.Ajax.get( "/recommend/recommendusertags", {
                offset: change_offset,
                count: 10
            },function (json){
                requesting = 0;
                if(json.err == "ok"){
                    var html = ""
                    for(var i =0 , el ; el = json.data[i++];){//如果已存在则添加一个tag_added的类名，加号变绿
                        html += ('<li><a class="'+ (array.indexOf(el) == -1 ? "" : "tag_added"  ) +'"href="###">'+el+'</a></li>')
                    }
                    W("#guess_tag")[0].innerHTML = html;
                }
            })
        }
    });

    //用于显示错误提示
    //HTML 格式 <div class="l tips"><em id="err-intro"></em></div>
    //@param id 元素的CSS表达式
    //@param status
    //0 表示失败，这时第三个参数有效，显示这红色字
    //1表示成功，会在此元素的父节点上添加一个叫okey的类名，显示绿色的勾号
    //2表示隐藏，去掉元素的innerHTML与父节点上的okey的类名
    //@param msg 错误消息
    function showTip(id, status, msg){
        var el = W(id), parent = el.parentNode();
        switch(status){
            case 0 :
                parent.removeClass("okey");
                el[0] && (el[0].innerHTML = msg);
                break;
            case 1:
                parent.addClass("okey");
                el[0] && (el[0].innerHTML = "");
                break
            case 2:
                parent.removeClass("okey");
                el[0] && (el[0].innerHTML = "");
                break
        }
    }

})
