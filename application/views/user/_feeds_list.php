<%*
$feeds: array(data => feed数组, dict => array(user => feed中出现的用户的信息, video => feed中出现的视频的信息, circle =>feed中出现的圈子的信息), has_more => 是否还有下一页) 
*%>
<%*输出feed的模版函数*%>
<%function name=_feed_list_part_trackstr opt=null%>
	<%if ! empty($opt)%><%$arrTrackParam=array_merge($arrTrackParam, $opt)%><%/if%>
	data-lks="<%HTML::chars(json_encode($arrTrackParam))%>"
<%/function%>

<%function name=_feed_list_part_action_link%>
    <%if $is_root_feed%><%$data_id=sprintf("%s-root", $cur_feed._id)%><%else%><%$data_id=$v._id%><%/if%>
                                <div class="tools"><a href="###" data-id="<%$data_id%>-forward" class="forward_">转发</a><%if $v.nRepostCount > 0%>(<%$v.nRepostCount|number_format%>)<%/if%></div>
<%/function%>

<%function name=_feed_list_part_callback feedinfo=null%>
	<%*确定该feed应该调用哪个渲染函数*%>
	<%if empty($feedinfo) || ! isset($feedinfo['msgtype'])%>
	    <%*do nothing*%>
	<%elseif $feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_QUAN_VIDEO || $feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_VIDEO || $feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_COMMENT_VIDEO || $feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_MOOD_VIDEO || ($feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO && is_string($feedinfo.vid))%>
	    _feed_list_1_video
	<%elseif $feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_CIRCLE%>
	    _feed_list_share_circle
	<%elseif $feedinfo.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO%>
		_feed_list_circle_video
	<%else%>
		<%*do nothing*%>
	<%/if%>
<%/function%>

<%function name=_feed_list_part_forward_form orig_feed_data=0 rootfid=0 curtext=''%>
    <%if $is_root_feed%><%$data_id=sprintf("%s-root", $cur_feed._id)%><%else%><%$data_id=$v._id%><%/if%>
								<div class="form-rt">
									<form action="/user/doforwardfeed" method="post" data-id="<%$data_id%>-forward">
										<div class="form-rt-input clearfix">
											<textarea name="curtext" class="textarea forward_txa_ xat_control_"><%$curtext|escape:"html"%></textarea>
											<input type="hidden" value="<%$v._id%>" name="curfid">
											<input type="hidden" value="<%if ! empty($rootfid)%><%$rootfid%><%else%><%$v._id%><%/if%>" name="rootfid">
											<%if ! empty($orig_feed_data)%>
											<input type="hidden" value="<%$orig_feed_data|json_encode|escape:"html"%>" name="orig_feed_data">
											<%/if%>
										</div>
										<div class="form-rt-btn clearfix">
											<div class="tips">你还可以输入<span><%$forward_text_max_len%></span>字</div>
											<button type="submit" class="bt-rt submit_forward_">转发</button>
										</div>
									</form>
								</div>
                                <%*
								<div class="rt-succ">
									<div class="rt-succ-inner">转发成功</div>
								</div>
                                *%>
<%/function%>

<%*is_root_feed为1表示是在渲染转发feed的原始feed，此时$v为原始feed信息，cur_feed为当前feed信息*%>
<%function name=_feed_list_1_video is_root_feed=0 cur_feed=0%>                                
								<div class="f-c-hd">
								<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_VIDEO%>
									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank" data-id="<%$v.uid%>" class="ava_popup_"><%$user_dict[$v.uid].nick|escape:"html"%></a>：分享了视频
							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_QUAN_VIDEO%>
									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank" data-id="<%$v.uid%>" class="ava_popup_"><%$user_dict[$v.uid].nick|escape:"html"%></a>： 把视频圈入了<a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$v.cid].title|escape:"html"%></a>圈子
							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_COMMENT_VIDEO%>
									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank" data-id="<%$v.uid%>" class="ava_popup_"><%$user_dict[$v.uid].nick|escape:"html"%></a>说：<%Util::formatUserLinkText($v.data, $v.users, $arrTrackParam)%>							    
							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_MOOD_VIDEO%>
									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank" data-id="<%$v.uid%>" class="ava_popup_"><%$user_dict[$v.uid].nick|escape:"html"%></a>：对视频表示 
                            		<%if ! isset(Model_Logic_Video::$arrMoodMap[$v.data])%>
                            			<%$v.data='xh'%>
                            		<%/if%>
										<%Model_Logic_Video::$arrMoodMap[$v.data]%><span class="face-min-<%$v.data%>" title="<%Model_Logic_Video::$arrMoodMap[$v.data]%>"><em class="ico-face"></em></span>
							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO%>
							        <a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$v.cid].title|escape:"html"%></a>：新增了1个视频
							    <%/if%>							    							    
								</div>
								<div class="f-c-bd clearfix">
								<%if ! $video_dict[$v.vid]._id%>
								该视频已经删除 
								<%else%>
									<div class="video">
										<a href="<%Util::videoPlayUrl($v.vid)%>"<%if empty($v.cid)%><%$strTrackParam%><%else%><%call name=_feed_list_part_trackstr opt=['circle' => $v.cid]%><%/if%> target="_blank" class="thumb video_hover_">
											<img src="<%Util::videoThumbnailUrl($video_dict[$v.vid].thumbnail)%>" alt="<%$video_dict[$v.vid].title|escape:"html"%>" width="160"/>
											<span class="ico"></span>
											<div class="action" style="display:none;">
							                   <span data-action="{id:'<%$v.vid%>'}" class="newadd marmot" title="添加到我的收藏" data--marmot="{page_id:'click_addtowatchlater',video_id:'<%$v.vid%>', 'fuid': '<%$v.uid%>', feedid:'<%$v._id%>', 'stype': 'i'}"></span>
							                   <span class="sharer video_share_" data-sns="{id:'<%$v.vid%>', cid:'<%$v.cid%>', title:'<%$video_dict[$v.vid].title|escape:"javascript"%>', image: '<%Util::videoThumbnailUrl($video_dict[$v.vid].thumbnail)%>', feedid:'<%$v._id%>'}" title="分享给我的好友"></span>
                                                <span video-id="<%$v.vid%>" class="circle_down addCircle forgroup marmot" data--marmot="{page_id:'click_viewvideo_quan'}" title="添加到我创建的圈子">圈一下</span> 
							                 </div>
						             	</a>
									</div>
									<div class="video-name"><a href="<%Util::videoPlayUrl($v.vid)%>"<%if empty($v.cid)%><%$strTrackParam%><%else%><%call name=_feed_list_part_trackstr opt=['circle' => $v.cid]%><%/if%> target="_blank"><%$video_dict[$v.vid].title|escape:"html"%></a>
										<%if ! empty($v.cid)%>
										<div class="circleName"><span class="ico"></span><a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$v.cid].title|escape:"html"%></a></div>
										<%/if%>
										<div class="playn"><span class="ico"></span><%$video_dict[$v.vid].watched_count|number_format%></div>
										<%$arrMoodedCountTmp=$video_dict[$v.vid].mooded_count%>
										<%if $arrMoodedCountTmp.total > 0%>
										<div class="heart"><span class="ico wg_mood"></span><%$arrMoodedCountTmp.total|number_format%></div>
										<%/if%>
									</div>
								<%/if%>
								</div>
								<div class="f-c-ft"><%Util::time_from_now($v.time, true)%> &nbsp;&nbsp;&nbsp;&nbsp;来源： <%$video_dict[$v.vid].domain|escape:"html"%>
								    <%call name=_feed_list_part_action_link%>
								</div>
								<%call name=_feed_list_part_forward_form%>
<%/function%>

<%function name=_feed_list_circle_video is_root_feed=0 cur_feed=0%>
							<%if ! is_array($v.vid)%><%$v.vid=(array)$v.vid%><%/if%>
							<%if is_array($v.cid)%><%*cid为数组的时候，每个视频上面会显示圈子名*%>
								<div class="f-c-hd">你关注的圈子有新的视频</div>
							<%else%>
								<div class="f-c-hd"><a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$v.cid].title|escape:"html"%></a>：新增了<%count($v.vid)%>个视频 <a class="lnk-more" href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank">查看全部</a></div>
							<%/if%>	
								<div class="f-c-bd clearfix">
									<div class="video-list">
										<ul class="clearfix">
										<%foreach $v.vid as $v2%>
											<li>
            								<%if ! $video_dict[$v2]._id%>
            								该视频已经删除 
            								<%else%>											
												<div class="video">
													<%if is_array($v.cid) && isset($v['cid'][$v2@index])%>
														<%$curCidTmp=$v['cid'][$v2@index]%>
														<%if isset($circle_dict[$curCidTmp])%>
														<div class="circleName"><span class="ico"></span><a href="<%Util::circleUrl($curCidTmp, null, $circle_dict[$curCidTmp])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$curCidTmp].title|escape:"html"%></a></div>
														<%/if%>
													<%else%>
														<%$curCidTmp=0%>
													<%/if%>
													<a href="<%Util::videoPlayUrl($v2)%>"<%if empty($curCidTmp)%><%$strTrackParam%><%else%><%call name=_feed_list_part_trackstr opt=['circle' => $curCidTmp]%><%/if%> target="_blank" class="thumb video_hover_">
														<img src="<%Util::videoThumbnailUrl($video_dict[$v2].thumbnail)%>" alt="<%$video_dict[$v2].title|escape:"html"%>" width="160"/>
														<span class="ico"></span>
														<div class="action" style="display:none;">
							                                <span data-action="{id:'<%$v2%>'}" class="newadd marmot" title="添加到我的收藏" data--marmot="{page_id:'click_addtowatchlater',video_id:'<%$v2%>', 'fuid': '<%$v.uid%>', feedid:'<%$v._id%>', 'stype': 'i'}"></span>
							                                <span title="分享给我的好友" class="sharer video_share_" data-sns="{id:'<%$v2%>', cid:'<%$curCidTmp%>', title:'<%$video_dict[$v2].title|escape:"javascript"%>', image: '<%Util::videoThumbnailUrl($video_dict[$v2].thumbnail)%>', feedid:'<%$v._id%>'}" ></span>
                                                             <span video-id="<%$v2%>" class="circle_down addCircle forgroup marmot" data--marmot="{page_id:'click_viewvideo_quan'}" title="添加到我创建的圈子">圈一下</span> 
										                </div>
									             	</a>
									             	<p class="title">
									             		<a href="<%Util::videoPlayUrl($v2)%>"<%if empty($curCidTmp)%><%$strTrackParam%><%else%><%call name=_feed_list_part_trackstr opt=['circle' => $curCidTmp]%><%/if%> target="_blank"><%$video_dict[$v2].title|escape:"html"%></a>
									             	</p>
												</div>
											<%/if%>
											</li>
										<%/foreach%>
										</ul>
									</div>
								</div>
								<div class="f-c-ft"><%Util::time_from_now($v.time, true)%>
								    <%call name=_feed_list_part_action_link%>
								</div>
								<%call name=_feed_list_part_forward_form orig_feed_data=['vid' => $v.vid, 'cid' => $v.cid]%>
<%/function%>

<%function name=_feed_list_share_circle is_root_feed=0 cur_feed=0%>
							<%if ! is_array($v.cid)%><%$v.cid=(array)$v.cid%><%/if%>
								<div class="f-c-hd"><a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank" data-id="<%$v.uid%>" class="ava_popup_"><%$user_dict[$v.uid].nick|escape:"html"%></a>：分享了<%count($v.cid)%>个圈子</div>
								<div class="f-c-bd clearfix">
									<div class="circle-list">
										<ul class="clearfix">
										<%foreach $v.cid as $v2%>
										    <%if ! $circle_dict[$v2]._id%><%continue%><%/if%>
											<li class="circleitem circleitem-t2">
												<div class="hd">
													<h3><a href="<%Util::circleUrl($v2, null, $circle_dict[$v2])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$v2].title|escape:"html"%></a></h3>
												</div>
												<div class="bd">
													<a href="<%Util::circleUrl($v2, null, $circle_dict[$v2])%>"<%$strTrackParam%> title="<%$circle_dict[$v2].title|escape:"html"%>"><img src="<%Util::circlePreviewPic($circle_dict[$v2].tn_path)%>" alt="<%$circle_dict[$v2].title|escape:"html"%>" /></a>
												</div>
												<div class="ft clearfix">
												<%if $circle_dict[$v2].is_focus%>
		                                	        <%$followst = 'followed'%>
		                                        <%else%>
		                                	        <%$followst = 'b-follow'%>
		                                        <%/if%>
													<a class="btn <%$followst%>" href="###" data-action="{'id':'<%$v2%>'}" is-focus="<%$circle_dict[$v2].is_focus%>"><span class="text0">关注</span><span class="text1">已关注</span><span class="text2">取消关注</span></a>
													<a href="###" class="btn invite_friend" data-action="{'id':'<%$v2%>', 'url': '<%Util::circleUrl($v2, null, $circle_dict[$v2])%>'}" >邀请好友</a>
										            <a href="###" class="btn share_group" data-action="{'id':'<%$v2%>', 'url': '<%Util::circleUrl($v2, null, $circle_dict[$v2])%>'}">分享Ta</a>
												</div>							
											</li>
									    <%/foreach%>
										</ul>
									</div>
								</div>
								<div class="f-c-ft"><%Util::time_from_now($v.time, true)%>
								    <%call name=_feed_list_part_action_link%>
								</div>
								<%call name=_feed_list_part_forward_form orig_feed_data=['cid' => $v.cid]%>
<%/function%>
<%*函数定义结束*%>
					<%$user_dict=$feeds.dict.user%>
					<%$circle_dict=$feeds.dict.circle%>
					<%$video_dict=$feeds.dict.video%>
					<%foreach $feeds.data as $v%>
					    <%*后端系统用户跟踪效果的参数*%>
                        <%$arrTrackParam=['stype' => 'i', 'feedid' => $v._id]%><%if $v.uid > 0%><%$arrTrackParam['fuid']=$v.uid%><%/if%>
                        <%$strTrackParam=sprintf(' data-lks="%s"', HTML::chars(json_encode($arrTrackParam)))%>
                        <%call name=_feed_list_part_callback assign=feed_render_func feedinfo=$v%>
                        <%$feed_render_func=trim($feed_render_func)%>
					    <%if $feed_render_func == '_feed_list_1_video'%>
						<!-- 动态类型1 视频 -->
						<div class="feed-item feed-t1">
							<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO%>
                                <div class="picr">
							    <a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><img src="<%#resUrl#%>/img/c/circle_48.png" /></a>
							<%else%>
                                <div class="pic feed_avatar">
								<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><img src="<%Util::userAvatarUrl($user_dict[$v.uid].avatar.48, 48)%>" data-id="<%$v.uid%>" class="ava_popup_" /></a>
							<%/if%>
							</div>
							<div class="content">
                                <%call name=_feed_list_1_video%>
							</div>
						</div>
						<!-- /动态类型1 视频 -->
						    <%continue%>
						<%/if%>
						<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_CIRCLE%>
						<!-- 动态类型3 圈子 -->
						<div class="feed-item feed-t3">
							<div class="pic feed_avatar">
								<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><img src="<%Util::userAvatarUrl($user_dict[$v.uid].avatar.48, 48)%>" data-id="<%$v.uid%>" class="ava_popup_" /></a>
							</div>
							<div class="content">
                                <%call name=_feed_list_share_circle%>
							</div>
						</div>
						<!-- /动态类型3 圈子 -->	
						    <%continue%>					
						<%/if%>
						<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO%>
						<!-- 动态类型2 视频列表 -->
						<div class="feed-item feed-t2">
							<div class="pic">
							<%if is_array($v.cid)%>
								<span><img src="<%#resUrl#%>/img/c/circle_48.png" /></span>
							<%else%>
								<a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><img src="<%#resUrl#%>/img/c/circle_48.png" /></a>
							<%/if%>
							</div>
							<div class="content">
                                <%call name=_feed_list_circle_video%>
							</div>
						</div>
						<!-- /动态类型2 视频列表 -->	
						    <%continue%>					
						<%/if%>
					    <%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_FORWARD_FEED%>
						<!-- 动态类型转发feed--->
							<%call name=_feed_list_part_callback assign=feed_render_func2 feedinfo=$v.root_feed%>
							<%$feed_render_func2=trim($feed_render_func2)%>
							<%$arrFeedRenderInfo=['class' => 'feed-t1', 'callback' => $feed_render_func2]%>
							<%if $feed_render_func2 == '_feed_list_share_circle'%>
								<%$arrFeedRenderInfo['class'] = 'feed-t3'%>
							<%elseif $feed_render_func2 == '_feed_list_circle_video'%>
								<%$arrFeedRenderInfo['class'] = 'feed-t2'%>
							<%/if%>
						<div class="feed-item <%$arrFeedRenderInfo.class|default:"feed-t1"%>">
							<div class="pic feed_avatar">
								<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><img src="<%Util::userAvatarUrl($user_dict[$v.uid].avatar.48, 48)%>" data-id="<%$v.uid%>" class="ava_popup_" /></a>
							</div>
							<div class="content">
								<div class="f-c-hd">
									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank" data-id="<%$v.uid%>" class="ava_popup_"><%$user_dict[$v.uid].nick|escape:"html"%></a>：<%Util::formatUserLinkText($v.data, $v.users, $arrTrackParam)%>					    							    
								</div>
								<div class="feed-rt">
									<div class="rt-arrow"></div>
								<%if empty($arrFeedRenderInfo.callback)%>
								    <div class="f-c-hd">该条动态已经删除</div>
								<%else%>
								    <%* 后端系统用户跟踪效果的参数，更新原始root feed *%>
                                    <%$arrTrackParam=['stype' => 'i', 'feedid' => $v.root_feed._id]%><%if $v.root_feed.uid > 0%><%$arrTrackParam['fuid']=$v.root_feed.uid%><%/if%>
                                    <%$strTrackParam=sprintf(' data-lks="%s"', HTML::chars(json_encode($arrTrackParam)))%>
								    <%call name=$arrFeedRenderInfo.callback cur_feed=$v v=$v.root_feed is_root_feed=1%>
								<%/if%>
								</div>
								<div class="f-c-ft"><%Util::time_from_now($v.time, true)%>
								    <%call name=_feed_list_part_action_link%>
								</div>
								<%call name=_feed_list_part_forward_form curtext=sprintf("//@%s: %s", $user_dict[$v.uid].nick, $v.data) rootfid=$v.root_feed._id orig_feed_data=$v.orig_feed_data%>
							</div>
						</div>
						<!-- /动态类型转发feed-->
						    <%continue%>
						<%/if%>						
				    <%/foreach%>
