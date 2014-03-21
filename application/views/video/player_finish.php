<%config_load file='site.conf'%>
                    <div class="video-recomm clearfix">
                        <div class="logo <%Model_Logic_Video::$arrPlayerFinishRecoSite[$video['domain']]%>"></div>
                        <div class="recomm-vodeo">
                            <h3><%if $reqtype == 'playererror'%>视频不见了，为你推荐相关内容<%else%>推荐视频<%/if%>：</h3>
                            <div class="v-list">
                                <ul id="end_list_" class="end_list_" data-rec-zome="<%$rec_videos.rec_zone%>" style="height:330px;overflow:hidden;">                   
                                <%foreach $rec_videos.video as $v%>
                                    <li class="v-item" data-id="<%$v._id%>">
                                       <a class="clearfix marmot" href="<%Util::videoPlayUrl($v._id)%>" title="<%$v.title|escape:'html'%>" data--marmot="{'page_id': 'click_recommendation', 'item_id': '<%$v._id%>', 'item_pos': '<%$v@index+1%>', 'rec_zone': '<%$rec_videos.rec_zone%>', 'item_list': ''}" target="_blank">
                                            <div class="v-pic"><img src="<%Util::videoThumbnailUrl($v.thumbnail)%>" alt="<%$v.title|escape:'html'%>"></div>
                                            <div class="v-info">
                                                <p class="title"><%Util::utf8SubStr($v.title, 40)%></p>
                                                <%*<p class="circle">某圈子</p>*%>
                                                <p class="num">
                                                    <span class="played"><%$v.watched_count|number_format%></span>
                                                    <span class="mood"><%$v.mooded_count.total|number_format%></span>
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                <%/foreach%>
                                </ul>                               
                            </div>
                            <div class="ft-btn clearfix">
                                <a href="#" class="bt-pre pre_page_" id="end_list_pre"></a>
                                <a href="#" class="bt-next next_page_" id="end_list_next"></a>
                            </div>
                        </div>                       
                        <div class="curr-video">
                        <%if $search_word%>
						    <div class="v-se">   
									<p class="title"> 
										<a href="javascript:void(0)" hidefocus>	
											<a href="/search?from=player&q=<%urlencode($search_word)%>" target="_blank" title="<%$search_word|escape:"html"%>"><em class="ico"></em>更多&nbsp;&nbsp;<%$search_word|escape:"html"%></a>
										</a>
									</p>
						    </div>
						<%/if%>
                            <div class="v-pic"><img src="<%Util::videoThumbnailUrl($video.thumbnail)%>" alt="<%$video.title|escape:'html'%>"></div>
                            <div class="v-info" title="<%$video.title|escape:'html'%>">
                                <p class="title" style="height:32px;"><%$video.title|escape:'html'%></p>
                                <%if ! empty($circle)%>
                                <p class="circle"><a href="<%Util::circleUrl($circle._id, null, $circle)%>" target="_blank" title="<%$circle.title|escape:"html"%>"><%Util::utf8SubStr($circle.title, 20)%></a></p>
                                <%/if%>
                                <p class="num">
                                    <span class="played"><%$video.watched_count|number_format%></span>
                                    <span class="mood"><%$video.mooded_count.total|number_format%></span>
                                </p>
                            </div>
                            <div class="v-btn">
                                <a href="javascript:reload_page()" class="bt-replay"></a>
                            </div>  
                        </div>                        
                    </div>
