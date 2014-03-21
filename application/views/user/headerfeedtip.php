<%config_load file='site.conf'%>
                                <%if $msg_count.intFollowUnread > 0 || $msg_count.intMentionUnread > 0%>
                                    <div class="new_fans">
                                        <ul>
                                        <%if $msg_count.intFollowUnread > 0%>
                                            <a href="<%Util::userUrl($login_user._id, 'fans')%>"><li><span class="r">查看&gt;&gt;</span>
                                        <span style="color:#F60">你有<span class="n"><%$msg_count.intFollowUnread|escape:"html"%></span>个新粉丝</span></li></a>
                                        <%/if%>
                                        <%if $msg_count.intMentionUnread > 0%>
                                            <a href="<%Util::userUrl($login_user._id, 'feeds', ['type' => Model_Logic_Feed2::SUBTYPE_MEMTION_ME])%>"><li><span class="r">查看&gt;&gt;</span>
                                        <span style="color:#F60">有<span class="n"><%$msg_count.intMentionUnread|escape:"html"%></span>条新@提到你</span></li></a>
                                        <%/if%>                                     
                                        </ul>
                                    </div>
                                <%/if%>
                                    <%if ! empty($feeds.data)%>
                                    <div class="msg_tips">
                                        <div class="title"><a class="r" href="<%Util::userUrl($login_user._id)%>">查看所有动态&gt;&gt;</a>动态提示</div>
                                        <ul>
                                        <%$user_dict=$feeds.dict.user%>
                    					<%$circle_dict=$feeds.dict.circle%>
                    					<%$video_dict=$feeds.dict.video%>
                                        <%foreach $feeds.data as $v%>
                                            <%*后端系统用户跟踪效果的参数*%>
                                            <%$arrTrackParam=['stype' => 'i', 'feedid' => $v._id]%><%if $v.uid > 0%><%$arrTrackParam['fuid']=$v.uid%><%/if%>
                                            <%$strTrackParam=sprintf(' data-lks="%s"', HTML::chars(json_encode($arrTrackParam)))%>                
                                            <li class="cls">                                      
                                            <%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_QUAN_VIDEO || $v.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_VIDEO || $v.msgtype == Model_Logic_Feed2::FEED_TYPE_COMMENT_VIDEO || $v.msgtype == Model_Logic_Feed2::FEED_TYPE_MOOD_VIDEO%>
						                    <!-- 动态类型1 视频 -->        
                                                <a class="pic" href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank">
                                                    <img src="<%Util::userAvatarUrl($user_dict[$v.uid].avatar.30, 30)%>" title="<%$user_dict[$v.uid].nick|escape:"html"%>">
                                                </a>                
                                                <div class="info">
                    								<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_VIDEO%>
                    									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><%$user_dict[$v.uid].nick|escape:"html"%></a>：分享了视频<a target="_blank" href="<%Util::videoPlayUrl($v.vid)%>"<%$strTrackParam%>><%$video_dict[$v.vid].title|escape:"html"%></a>
                    							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_QUAN_VIDEO%>
                    									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><%$user_dict[$v.uid].nick|escape:"html"%></a>：把视频<a target="_blank" href="<%Util::videoPlayUrl($v.vid)%>"<%$strTrackParam%>><%$video_dict[$v.vid].title|escape:"html"%></a>圈入了<a href="<%Util::circleUrl($v.cid, null, $circle_dict[$v.cid])%>"<%$strTrackParam%> target="_blank"><%$circle_dict[$v.cid].title|escape:"html"%></a>圈子
                    							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_COMMENT_VIDEO%>
                    									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><%$user_dict[$v.uid].nick|escape:"html"%></a>：评论了视频<a target="_blank" href="<%Util::videoPlayUrl($v.vid)%>"<%$strTrackParam%>><%$video_dict[$v.vid].title|escape:"html"%></a>
                    							    <%elseif $v.msgtype == Model_Logic_Feed2::FEED_TYPE_MOOD_VIDEO%>
                    									<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><%$user_dict[$v.uid].nick|escape:"html"%></a>：对视频<a target="_blank" href="<%Util::videoPlayUrl($v.vid)%>"<%$strTrackParam%>><%$video_dict[$v.vid].title|escape:"html"%></a>标记了心情 
                    							    <%/if%>
                                                </div>
                                            <%/if%>            
                    						<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_CIRCLE%>
                    						<!-- 动态类型3 圈子 -->      
                                                <a class="pic" href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank">
                                                    <img src="<%Util::userAvatarUrl($user_dict[$v.uid].avatar.30, 30)%>" title="<%$user_dict[$v.uid].nick|escape:"html"%>">
                                                </a>                
                                                <div class="info">
                                                <%if ! is_array($v.cid)%><%$v.cid=(array)$v.cid%><%/if%>
                    								<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><%$user_dict[$v.uid].nick|escape:"html"%></a>：分享了<%count($v.cid)%>个圈子
                                                </div>                  						
                    						<%/if%>
                    						<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO%>
                    						<!-- 动态类型2 视频列表 -->        
                                                <span class="pic">
                                                    <a href="<%Util::userUrl($login_user._id, 'feeds', ['type' => Model_Logic_Feed2::SUBTYPE_CIRCLE_SLEF])%>" target="_blank"><img src="<%#resUrl#%>/img/c/circle_48.png" /></a>
                                                </span>                
                                                <div class="info">
                                                <%if ! is_array($v.vid)%><%$v.vid=(array)$v.vid%><%/if%>
                    								你关注的圈子有新的视频，点击<a href="<%Util::userUrl($login_user._id, 'feeds', ['type' => Model_Logic_Feed2::SUBTYPE_CIRCLE_SLEF])%>" target="_blank">查看详情</a>
                                                </div>                   						
                    						<%/if%>
                    						<%if $v.msgtype == Model_Logic_Feed2::FEED_TYPE_FORWARD_FEED%>
                    						<!-- 动态类型转发feed -->        
                                                <a class="pic" href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank">
                                                    <img src="<%Util::userAvatarUrl($user_dict[$v.uid].avatar.30, 30)%>" title="<%$user_dict[$v.uid].nick|escape:"html"%>">
                                                </a>                
                                                <div class="info">
                                                <%if trim($v.data) == ''%>
                                                    <%if ! $v.root_feed%><%$strFeedTxt='转发了一条动态'%>
                                                    <%elseif $v.root_feed.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_VIDEO%><%$strFeedTxt='转发了一条对视频的分享'%>
                                                    <%elseif $v.root_feed.msgtype == Model_Logic_Feed2::FEED_TYPE_COMMENT_VIDEO%><%$strFeedTxt='转发了一条对视频的评论'%>
                                                    <%elseif $v.root_feed.msgtype == Model_Logic_Feed2::FEED_TYPE_MOOD_VIDEO%><%$strFeedTxt='转发了一条对视频的表情'%>
                                                    <%elseif $v.root_feed.msgtype == Model_Logic_Feed2::FEED_TYPE_SHARE_CIRCLE%><%$strFeedTxt='转发了一条对圈子的分享'%>
                                                    <%elseif $v.root_feed.msgtype == Model_Logic_Feed2::FEED_TYPE_CIRCLE_VIDEO%><%$strFeedTxt='转发了一条圈子更新动态'%>
                                                    <%/if%>
                                                <%else%>
                                                    <%$strFeedTxt=Util::formatUserLinkText($v.data, $v.users, $arrTrackParam)%>
                                                <%/if%>
                    								<a href="<%Util::userUrl($v.uid)%>"<%$strTrackParam%> target="_blank"><%$user_dict[$v.uid].nick|escape:"html"%></a>：<%$strFeedTxt%>
                                                </div>                 						
                    						<%/if%>
                                            </li>                                        
                                        <%/foreach%>
                                        </ul>
                                        <div class="foot_all"><a href="<%Util::userUrl($login_user._id)%>">查看所有动态&gt;&gt;</a></div>
                                    </div>
                                    <%/if%>
