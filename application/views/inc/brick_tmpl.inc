<%extends file="common/share_panel.tpl"%>

<%block name="brfore_html"%>

<!--- 砖头的模板 -->
<script id="brick_tmpl" type="text/template">
    <&
            function getMoodIcon(obj){
                obj = obj || { xh: 0,zj:0,gx:0,bj:0,total:0 };
                var max = 0, icon = "";
                for(var i in obj){
                    if(obj[i] > max && i !== "total"){
                        max = obj[i];
                        icon = i
                    }
                }
                return "heart " + icon + "_mood"
            }

            function prettyNumber( a  ){
                if(!a){
                    return false
                }else{
                    return (a+"").replace(/\B(?=(?:\d{3})+$)/g,",")
                }
            }
            //民德版，不位伸大则缩小
            function adjustWidth(width){
                return width < 200 ? width : 200;
            }
            function adjustHeight(width, height){
                return width < 200 ? height : Math.ceil(height * 200 / width)
            }
            //肖潇版，拉伸
             function adjustWidth(width){
                return 200
            }
            function adjustHeight(width, height){
                return  Math.ceil(height * 200 / width)
            }
    var guesslike = /guesslike/.test(location.href ) ? " marmot " : "";
    var isIE678 = !+"\v1";//处理IE678
    var isMacSafari = navigator.userAgent.indexOf("Mac OS X") != -1 && /Apple/i.test(navigator.vendor) ;
    &>
    <div class="BoardBrick" style="<&= (isIE678 || isMacSafari)  === true  ?  '' : 'opacity:0' &>">
        <& if(  @circle && @circle.title ){ &>
        <b class="<&=  (@rec_type === '2' ? 's-hot' : @rec_type === '1'  ? 's-new' :'')  &>"></b>
        <div class="circleName s-ic"><span><a href="<&= QW.PageUtil.circleUrl(  @circle._id || 0, null, @circle ) &>"><&=  @circle.title  &></a></span></div>
        <& }else{ &>
        <div class="hidenHead"></div>
        <& } &>
        <& if( @playlist == 'personal_recommend' || @playlist == "home_page" ){ &>
        <div class="cls vod m-pic tp-a">
        <& }else{ &>
        <div class="vod m-pic tp-a cls">
        <& } &>
           <& var playurl = QW.PageUtil.videoPlayUrl( @_id, @playlist,{user:@user,offset:@offset,count:@count,circle: (@circle || {})._id });  
              var playurls = playurl.split("?");
            &>
            <a target="_blank" href="<&= playurls[0]  &>" data-lks="<&= playurls[1] &>" class="<&=guesslike&> a playurl" 
            data--marmot="{page_id:'click_recommendation',item_pos:'<&=@_index&>',rec_zone:'video_rec',item_id: '<&= @_id &>'}"  
            style="width:<&= adjustWidth(@tn_width) &>px;height:<&= adjustHeight(@tn_width,@tn_height) &>px">
            
                <img src="<&= QW.PageUtil.videoThumbnailUrl( @thumbnail+'' ) &>" alt="<&= QW.StringH.encode4Html(@title) &>" class="h brick_img" 
                  style="width:<&= adjustWidth(@tn_width) &>px;height:<&= adjustHeight(@tn_width,@tn_height) &>px" />
                
                 <div class="action">
                   <span class="circle_down" video-id="<&= @_id &>" circle-id="<&= (@circle || {} )._id &>" ></span>
                   <span data-action="{id:'<&= @_id &>'}" class="newadd marmot" 
                   data--marmot="{page_id:'click_addtowatchlater',video_id: '<&= @_id &>'}" title="添加到我的收藏" ></span>
                   <span class="sharer" data-sns="{id:'<&= @_id &>', cid:'<&= (@circle || {} )._id &>', title:'<&= @title &>', image: '<&= QW.PageUtil.videoThumbnailUrl( @thumbnail+'' ) &>'}"></span>
                 </div>
                  <span class="ico"></span>
            </a>
            <& if( @length ){ &>
            <span class="time"><&=  QW.PageUtil.sec2time( @length)  &></span>
            <& } &>
            <div class="tit">
       
                <a title="<&= QW.StringH.encode4Html(@title) &>" target="_blank" class="playurl" href="<&= playurls[0]  &>" data-lks="<&= playurls[1] &>"><&= QW.StringH.encode4Html(@title) || "" &></a>
                <& if(@recommend_reason ){ &>
                      <span style="color:#ff3c00">&nbsp;&nbsp;<&= QW.StringH.encode4Html(@recommend_reason.reason_text||"")  &></span>
               <& } &>
            </div>
            <div class="count">
                            <&
                            var iss = prettyNumber(@shared_count),
                            ism = prettyNumber(@mooded_count && @mooded_count.total),
                            isw = prettyNumber(@watched_count),
                            none =  'style="display:none"'
                            &>
                            <span class="share" title="分享" <&= iss ? '' : none  &>><&= iss &></span>
                            <span class=" <&= getMoodIcon( @mooded_count ) &>" title="心情" <&= ism ? '' : none &>><&= ism  &></span>
                            <span class="playn" title="播放" <&= isw ? '' : none &>><&= isw &></span>
            </div>
        </div>
        <& if (@note) { &>
            <div class="note">
                <span><&= QW.StringH.encode4Html(@note||"") &></span>
            </div>
            <& } &>
        <!--表情-->
        <div class="interactive s-rx cls">
            <span>
                <a href="javascript:void(0)" class="i-mood-tips i-comment marmot" title="评论" data--marmot="{page_id:'click_comment',video_id: '<&= @_id &>'}"></a>
                <i></i>
                <a href="javascript:void(0)" class="i-mood i-love i-mood-tips marmot" title="喜欢" data-action="{id:'<&= @_id &>',mood:'xh', circle: <&= (@circle || {} )._id || 0 &>}" data--marmot="{page_id:'click_feelingicon',feeling_id:'0',video_id: '<&= @_id &>'}"></a>
                <a href="javascript:void(0)" class="i-mood i-happy i-mood-tips marmot" title="搞笑" data-action="{id:'<&= @_id &>',mood:'gx', circle: <&= (@circle || {} )._id|| 0 &>}" data--marmot="{page_id:'click_feelingicon',feeling_id:'1',video_id: '<&= @_id &>'}"></a>
                <a href="javascript:void(0)" class="i-mood i-nobody i-mood-tips marmot" title="震惊" data-action="{id:'<&= @_id &>',mood:'zj', circle: <&= (@circle || {} )._id || 0 &>}" data--marmot="{page_id:'click_feelingicon',feeling_id:'2',video_id: '<&= @_id &>'}"></a>
                <a href="javascript:void(0)" class="i-mood i-angry i-mood-tips marmot" title="鄙视" data-action="{id:'<&= @_id &>',mood:'bs', circle: <&= (@circle || {} )._id || 0 &>}" data--marmot="{page_id:'click_feelingicon',feeling_id:'3',video_id: '<&= @_id &>'}"></a>
                <a href="javascript:void(0)" class="i-mood i-jiong i-mood-tips marmot" title="杯具" data-action="{id:'<&= @_id &>',mood:'bj', circle: <&= (@circle || {} )._id || 0 &>}" data--marmot="{page_id:'click_feelingicon',feeling_id:'4',video_id: '<&= @_id &>'}"></a>
            </span>
             <div class="put"><img class="h" src="javascript:void(0)">
             <textarea placeholder="添加评论" name="comment_text" autocomplete="off" rows="1" class="text comment_text xat_control_"></textarea>
             <span class="post" data-action="{id: '<&= @_id &>', circle : '<&= (@circle || {} )._id || 0 &>'}">确定</span></div>
        </div>
        <!--评论-->
        <div class="commentBox <&= @comments.length ?  '':  ' hideComment' &>">

                <& for(var comments = @comments || [],i = 0, el; el = comments[i++];){ &>
                <dl class="comment cls">
                    <& if ( el.user_id ){ &>
                    <dt><a href="<&= QW.PageUtil.userUrl( el.user_id ) &>"><img src="<&= el.avatar &>" class="ava_popup_" data-id="<&=el.user_id&>"></a><span class="<&= el.temper_icon &>"></span></dt>
                    <dd>
                        <a href="<&= QW.PageUtil.userUrl( el.user_id ) &>" class="ava_popup_" data-id="<&=el.user_id&>"><&= el.nick &></a>：<&= QW.PageUtil.formatUserLinkText(el.data, el.users, null, 60) || "" &>
                    </dd>
                    <& }else{ &>
                    <dt><img src="<&= el.avatar &>"><span class="<&= el.temper_icon &>"></span></dt>
                    <dd>
                        <&= el.nick &>：<&= (QW.StringH.encode4Html( el.data ) || "").slice(0, 60) &>
                    </dd>
                    <& }&>
                </dl>
                <& } &>
        </div>
        
        
    </div>
</script>

<%/block%>


<%*下面的内容是视频分享panel*%>
<%block name="url"%>
http://www.quanloo.com
<%/block%>
