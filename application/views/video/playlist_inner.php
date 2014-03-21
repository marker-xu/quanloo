<%*
$videos: array(total => 记录总数, data =>当前页视频信息列表)
$playlist: 视频所在播放列表名字
$playUrlParam: 拼接播放url所需的额外参数
$offset: url里的offset参数
$rec_zone: 日志上报用
*%>
<%$offset=$offset+1%><%if $playlist != ''%><%$playUrlParam['playlist']=$playlist%><%/if%>
            <%foreach $videos.data as $v%>
                <li class="item m_v-item marmot" <%strip%>data--marmot="{
                page_id:'click_recommendation',
                item_id:'<%$v._id|escape:"javascript"%>',
                item_pos:'<%$v@index+$offset%>',
                rec_zone:'<%$rec_zone%>',
                item_list:''<%*覆盖全局的变量，节省流量用*%>
                }"<%/strip%>>
                    <a href="<%Util::videoPlayUrl($v._id)%>"<%if $playUrlParam%> data-lks="<%json_encode($playUrlParam)|escape:"html"%>"<%/if%> title="<%$v.title|escape:'html'%>" data-vid="<%$v._id%>">
                        <img class="thumb" src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>">
                    </a>
                    <p class="title"><a href="<%Util::videoPlayUrl($v._id)%>"<%if $playUrlParam%> data-lks="<%json_encode($playUrlParam)|escape:"html"%>"<%/if%> title="<%$v.title|escape:'html'%>" data-vid="<%$v._id%>">
                        <%Util::utf8SubStr($v.title, 200)%>
                    </a></p>
                </li>
            <%/foreach%>