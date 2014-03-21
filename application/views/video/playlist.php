<%*
$videos: array(total => 记录总数, data =>当前页视频信息列表)
$playlist: 视频所在播放列表名字
$playUrlParam: 拼接播放url所需的额外参数
$offset: url里的offset参数
$rec_zone: 日志上报用
*%>
<%$offset=$offset+1%><%if $playlist != ''%><%$playUrlParam['playlist']=$playlist%><%/if%>
            <%foreach $videos.data as $v%>
                <li class="marmot" <%strip%>data--marmot="{
                page_id:'click_recommendation',
                item_id:'<%$v._id|escape:"javascript"%>',
                item_pos:'<%$v@index+$offset%>',
                rec_zone:'<%$rec_zone%>',
                item_list:''
                }"<%/strip%>>
                    <a class="wp cls" href="<%Util::videoPlayUrl($v._id)%>" data-lks="<%json_encode($playUrlParam)|escape:"html"%>" title="<%$v.title|escape:'html'%>">
                        <span class="thumb"><img class="img" src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>"></span>
                        <dl>
                            <dt><%Util::utf8SubStr($v.title, 70)%></dt>
                        </dl>
                    </a>
                </li>
            <%/foreach%>          