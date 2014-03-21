<%*
$comments: array(total => 记录总数, data =>当前页视频信息列表)
*%>
                            	<%foreach $comments.data as $v%>   
                                <li<%if $v@first%> class="first"<%/if%>>
								    <div class="user-info">
										<p class="face-box">
										<%if $v.user_id > 0%>
											<a href="<%Util::userUrl($v.user_id)%>" target="_blank"><img class="face ava_popup_" src="<%$v.avatar%>" data-id="<%$v.user_id%>" /></a>
										<%else%><img class="face" src="<%$v.avatar%>" /><%/if%>
										</p>
									</div>	
									<div class="name"><%if $v.user_id%><a href="<%Util::userUrl($v.user_id)%>" class="" target="_blank"><%$v.nick|escape:"html"%></a>
										    <%else%><%$v.nick|escape:"html"%><%/if%></div>
									<div class="content"><%Util::formatUserLinkText($v.data, $v.users)%></div>
									<div class="attract">
									    <div class="done"><a class="reply j_comment-reply" data-jss="name:'<%$v.nick|escape:"html"%>'" href="javascript:void(0)">回复</a></div>
										<div class="info">
										    <p class="digg" style="display: none"><span class="num">11</span><a class="do" href="#"><em class="ico"></em></a></p>
										    <p class="date"><%$v.create_time_str%><%if $is_admin_user%> <a href="###" data-action="<%$v._id%>" class="comment-del">删除</a><%/if%></p>
										</div>
									</div>
                                </li>
                                <%/foreach%>