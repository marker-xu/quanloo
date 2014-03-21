<%*播放页的头部*%>
    	<div id="hd">
        	<div class="wrap">
            	<div class="content">
                    <div id="logo">
                        <a href="<%#siteUrl#%>/" title="首页">
                            <img src="<%#resUrl#%>/img/play/logo.jpg" alt="圈乐" />
                        </a>
                    </div>
                    <div class="BoardBrick interactive cls" id="myHeart">
                    	<span>认为：</span>
                        <span style="padding-top:0">
                            <a title="喜欢" class="i-mood i-love marmot" href="###" data-action="{id:'<%$video._id%>',mood:'xh', circle:'<%$smarty.get.circle|escape:"html"%>'}" data--marmot="{page_id:'click_feelingicon',feeling_id:'0',video_id: '<%$video._id%>'}"></a>
                            <a title="大笑" class="i-mood i-happy marmot" href="###" data-action="{id:'<%$video._id%>',mood:'dx', circle:'<%$smarty.get.circle|escape:"html"%>'}" data--marmot="{page_id:'click_feelingicon',feeling_id:'1',video_id: '<%$video._id%>'}"></a>
                            <a title="围观" class="i-mood i-nobody marmot" href="###" data-action="{id:'<%$video._id%>',mood:'wg', circle:'<%$smarty.get.circle|escape:"html"%>'}" data--marmot="{page_id:'click_feelingicon',feeling_id:'2',video_id: '<%$video._id%>'}"></a>
                            <a title="鄙视" class="i-mood i-angry marmot" href="###" data-action="{id:'<%$video._id%>',mood:'fn', circle:'<%$smarty.get.circle|escape:"html"%>'}" data--marmot="{page_id:'click_feelingicon',feeling_id:'3',video_id: '<%$video._id%>'}"></a>
							<a title="囧" class="i-mood i-jiong marmot" href="###" data-action="{id:'<%$video._id%>',mood:'jn', circle:'<%$smarty.get.circle|escape:"html"%>'}" data--marmot="{page_id:'click_feelingicon',feeling_id:'4',video_id: '<%$video._id%>'}"></a>
                        </span>
                        <div id="comment-pop"><a title="评论" class="comments" href="#"></a>
                        	<!--评论-->
                            <div id="commentList">
                            	<div class="wrap">
                                    <div class="tit">
                                    <%$comment_count=count($comments.data)%>
                                        <div class="r"><span id="comment_prev" class="comment_prev unable">上一页</span><span id="comment_next" class="comment_next<%if $comment_count >= $comments.total%> unable<%/if%>">下一页</span></div>
                                        <span>对这个视频的评论&nbsp;(<kbd id="comments_total"><%$comments.total%></kbd>)</span>
                                    </div>
                                    <ul>
                                    <%if $comment_count < 1%>
                                    	<li class="wp cls" id="js_nocomment">
                                            <div class="nocomment">暂时还没有评论，快来吐槽一个</div>
                                        </li>
                                    <%else%>
                                        <%include file="video/comments.php" inline%>
                                    <%/if%>
                                    </ul>
                                    <div id="commentSubmit">
                                    <%if $login_user%>
                                        <div class="tit">你的评论</div>
                                        <div class="b">
                                        <textarea name="content" rows="4" class="commentText" id="commentText" placeholder="说点啥吧, 不多于200字!" data-vid="<%$video._id%>"></textarea>
                                        </div>
                                        <div class="ft">
                                            <span id="comment_input_tip" style="float:left"></span>
                                            <span class="sb" title="发表"></span>
                                            <span class="cl" title="取消"></span>
                                        </div>
                                    <%else%>
                                        <div class="ft">请你先<span class="fast_login" onclick="javascript:LOGIN_POPUP();">登录</span></div>
                                    <%/if%>
                                    </div>
                                </div>
                            </div>
                            <!--评论-->
                        </div>
						<div id="share-pop"><a title="分享" class="share" href="#" u-data="{pu:'<%Util::videoThumbnailUrl($video.thumbnail)%>'}"></a>
                        <!--分享-->
                        <div id="shareList">
                            <div class="shareItem">
								<div>
								<textarea id="shareText" class="shareText" rows="4" name="shareText">ddd</textarea>
								</div>
								<div class="submit_btn cls">
									<span data-sns="sina_weibo" title="分享到新浪微博" class="share_sina submit__ marmot" 
                                    data--marmot="{page_id:'click_share',share_id:'0',video_id: '<%$video._id%>'}"></span>
									<span data-sns="tencent_weibo" title="分享到腾讯微博" class="share_qq submit__ marmot" 
                                    data--marmot="{page_id:'click_share',share_id:'1',video_id: '<%$video._id%>'}"></span>
								</div>
                            </div>
                        </div>
                        <!--分享-->
                        </div>
                    </div>
                    <div id="search">
                        <form action="<%#siteUrl#%>/search" id="search-form">
                            <fieldset>
                                <legend>搜索</legend>
                                <!-- 搜索框区域 -->
                                <div id="search-aera">
                                    <p class="search-words">
                                    <%if $search_result.query%>
                                        <%$queryStr = join('', $search_result.query)|escape:'html'%>
                                    <%/if%>
                                        <input name="q" class="search-frame" title="搜视频，搜圈子" value="搜视频，搜圈子" id="search-text" maxlength="100" <%if $queryStr%> value="<%$queryStr%>"<%/if%>><button type="submit"><span>搜索</span></button>                                        
                                    </p>
                                </div>
                                <!--//搜索框区域 -->
                            </fieldset>
                        </form>                        
                    </div>
                    <div id="playList" class="playList listopen">
                        <%if $left_playlist.0.has_offset%>
                            <%$playUrlParamTmp=['circle' => $smarty.get.circle, 'offset' => $smarty.get.offset]%>
                        <%else%>
                            <%$playUrlParamTmp=[]%>
                        <%/if%>
                        <%$playUrlParamTmp2['playlist']=$left_playlist.0.id%>
                    	<a href="<%Util::videoPlayUrl($left_playlist_first_video._id)%>" data-lks="<%json_encode($playUrlParamTmp2)|escape:"html"%>" class="wp cls" title="<%$left_playlist_first_video.title|escape:'html'%>">
                        	<span class="thumb"><img class="img" src="<%Util::videoThumbnailUrl($left_playlist_first_video.thumbnail)%>" alt="<%$left_playlist_first_video.title|escape:'html'%>"></span>
                            <dl>
                            	<dt>下一视频</dt>
                                <dd><%Util::utf8SubStr($left_playlist_first_video.title, 24)%></dd>
                            </dl>
                        </a>
                         <!--视频列表-->
                        <div id="videoList">
                            <div class="playList currentPlay">
                                <span class="wp cls" title="<%$video.title|escape:'html'%>">
                                    <span class="thumb"><img class="img" src="<%Util::videoThumbnailUrl($video.thumbnail)%>" alt="<%$video.title|escape:'html'%>"></span>
                                    <dl>
                                        <dt>正在播放</dt>
                                        <dd><%Util::utf8SubStr($video.title, 30)%></dd>
                                    </dl>
                                </span>
                            </div>                         
                            <div class="lists" id="left_play_list">
                                <ul class="tit">
                                    <li class="selected"><span><%$left_playlist.0.name|escape:"html"%></span></li>
                                    <%if $left_playlist.1.id%>
                                    <li><span><%$left_playlist.1.name|escape:"html"%></span></li>
                                    <%/if%>
                                </ul>
                                <ul class="playList lst-a selected">
                                <%include file="video/playlist.php" videos=$left_playlist.0.videos playlist=$left_playlist.0.id playUrlParam=$playUrlParamTmp rec_zone=$left_playlist.0.rec_zone%>
                                </ul>
                                <%if $left_playlist.1.id%>
                                <ul class="playList lst-a" style="display:none;">
                                <%$playUrlParamTmp=['circle' => $smarty.get.circle]%>
                                <%include file="video/playlist.php" videos=$left_playlist.1.videos playlist=$left_playlist.1.id playUrlParam=$playUrlParamTmp rec_zone=$left_playlist.1.rec_zone%>
                                </ul>
                                <%/if%>
                            </div>                           
                        </div>
                        <!--视频列表-->
                    </div>
                        <%if $login_user%>
						<div id="logined" class="login">
							<i></i>
							<a class="headp" href="<%Util::userUrl($login_user._id)%>"><img src="<%Util::userAvatarUrl($login_user.avatar.30)%>"></a>
							<div id="userList">
								<ul>
									<li class="name"><span><%$login_user.nick|escape:'html'%></span></li>
									<li class="clearLeft"><a href="<%Util::userUrl($login_user._id)%>">我的主页</a></li>
									<li><a href="<%Util::userUrl($login_user._id, 'circle')%>">我的圈子</a></li>
									<li><a href="<%#siteUrl#%>/user/setting">个人设置</a></li>
									<li><a href="<%#siteUrl#%>/user/logout?f=<%$smarty.server.REQUEST_URI|escape:"url"%>">登出</a></li>
								</ul>
							</div>                            
							<input type="hidden" id="logined_mark" />
                        <%else%>
						<div id="login">
						    <a class="loginLink" href="#" id="fast_login_" onclick="javascript:LOGIN_POPUP();"><span>登录</span></a>                           
                            <i class="none"></i>
                            <a class="reg" href="<%#siteUrl#%>/user/register"><span>注册</span></a>
                        <%/if%>
                    </div>
                </div>
            </div>
        </div>
        <%include file="inc/_header_login.inc" inline%>
