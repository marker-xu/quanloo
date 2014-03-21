<%*
$circle_list: 圈子信息的二维数组，每个圈子为array('_id', 'create_time', 'tn_status', 'title', 'popularity'或者'popularity_rank', 'is_focus', 'is_shared')
$pager: 分页的相关信息，array(count=> 每页记录数, offset=>当前偏移量，第一页为0, total=> 总记录数,pageUrl=>页面Url)
$no_marmot: 是否添加上报日志，默认是
$circle_ul_class: ul标签的class
*%>
<%$no_marmot=0%>
            		<div class="videolist-inner">
             				<ul class="<%if ! empty($circle_ul_class)%><%$circle_ul_class%><%else%>list clearfix<%/if%>">
             				<%foreach $circle_list as $row%>
								<li class="circleitem circleitem-t2">
								<%if ! $no_marmot%>
									<div class="hd marmot"  <%strip%>data--marmot="{
                page_id:'click_recommendation',
                item_list:'',
                item_id:'<%$row._id|escape:"javascript"%>',
                item_pos:'<%$row@index+$offset+1%>',
                rec_zone:'circle_rec'
                }"<%/strip%>>
                                <%else%>
                                    <div class="hd">
                                <%/if%>
										<h3><a href="<%Util::circleUrl($row._id, null, $row)%>" title="<%$row.title|escape:'html'%>"><%Util::utf8SubStr($row.title,21)%></a></h3><span class="type <%Util::circleTypeCss($row)%>"></span>
                                        <div class="circle_own">
                                            <img class="in-block head_img" src="<%Util::userAvatarUrl($row.user.avatar.30, 30)%>">
                                            <%if $row.user._id == 1846037590%>
                                            <span class="lele"><%$row.user.nick|escape:"html"%></span>创建
                                            <%else%>
                                            <a class="user" target="_blank" href="<%Util::userUrl($row.user._id)%>"><%$row.user.nick|escape:"html"%></a>创建
                                            <%/if%>
                                        </div>
									</div>
								<%if ! $no_marmot%>
									<div class="bd marmot"  <%strip%>data--marmot="{
                page_id:'click_recommendation',
                item_id:'<%$row._id|escape:"javascript"%>',
                item_pos:'<%$row@index+$offset+1%>',
                rec_zone:'circle_rec'
                }"<%/strip%>>
                                <%else%>
                                    <div class="bd">
                                <%/if%>
										<a href="<%Util::circleUrl($row._id, null, $row)%>">
										<img src="<%Util::circlePreviewPic($row.tn_path)%>" alt="<%$row.title|escape:'html'%>">
										</a>
									</div>
									<div class="ft clearfix">
                                        <%if $row.is_focus%>
		                                	<%$followst = 'followed'%>
		                                <%else%>
		                                	<%$followst = 'b-follow'%>
		                                <%/if%>
		                                <a class="btn <%$followst%>" href="#" data-action="{'id':'<%$row._id%>'}" is-focus="<%$row.is_focus%>"><span class="text0">关注</span><span class="text1">取消关注</span><span class="text2">取消关注</span></a>
		                                
										<a href="#" class="btn invite_friend" data-action="{'id':'<%$row._id%>', 'url': '<%Util::circleUrl($row._id, null, $row)%>'}" >邀请好友</a>
										<a href="#" class="btn share_group" data-action="{'id':'<%$row._id%>','image':'<%Util::circlePreviewPic($row.tn_path)%>','circleName':'<%Util::utf8SubStr($row.title,21)%>','url': '<%Util::circleUrl($row._id, null, $row)%>'}">分享Ta</a>
									</div>
                                    <input type="hidden" value="<%$row._id%>" />
								</li>
							<%/foreach%>
							</ul>
							
                    </div>
                    <%if ! empty($pager) && $pager.total > $pager.count%>
						<%include file="inc/pager.inc" count=$pager.count offset=$pager.offset total=$pager.total inline%>
					<%/if%>
