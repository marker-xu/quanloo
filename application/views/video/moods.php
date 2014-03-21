                            <%if empty($feed_mooded_user.data)%>
                                <li class='no_comment'>该视频现在还没有人发表心情</li>
                            <%else%>    
                                <li>
                                    <div>谁标了心情</div>
                                    <dl class="clearfix">
                                        <dd>
                                        <%foreach $feed_mooded_user.data as $v%>
                                            <a class="item" href="<%Util::userUrl($v._id)%>" target="_blank" title="<%$v.nick|escape:'html'%>">
                                            	<img src="<%Util::userAvatarUrl($v.avatar.30, 30)%>" alt="<%$v.nick|escape:'html'%>" class="ava_popup_" data-id="<%$v._id%>" />
                                            	<%if $v.data == 'xh'%><span class="ico-xq-xh-m"></span>
                                            	<%elseif $v.data == 'wg'%> <span class="ico-xq-wg-m"></span>
                                            	<%elseif $v.data == 'dx'%> <span class="ico-xq-dx-m"></span>
                                            	<%elseif $v.data == 'fn'%> <span class="ico-xq-fn-m"></span>
                                            	<%elseif $v.data == 'jn'%> <span class="ico-xq-jn-m"></span>
                                            	<%/if%>
                                            </a>
                                        <%/foreach%>
                                        </dd>
                                    </dl>
                                    <div>这个视频被标记了<%$feed_mooded_user.total%>次心情</div>
                                </li>
                            <%/if%>
