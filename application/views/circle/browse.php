<%extends file="common/base.tpl"%>
<%block name="seo_meta"%>
<%if empty($cur_cat_name)%>
    <meta name="keywords" content="视频,最热视频,视频分享,视频推荐,视频SNS社区,视频圈子,视频类Pinterest" />
    <meta name="description" content="圈乐汇集各类视频圈子，不同兴趣的视频爱好者可以在这里找到感兴趣的视频圈子，每个圈子均汇聚了全网最火、最热的相关视频，通过分享、推荐等社交行为，视频发烧友可以体验到最酷的视频社交乐趣。">    
<%else%>
    <%$cur_cat_name_escape=HTML::chars($cur_cat_name)%>
    <%if $cur_select_tag == ''%>
    <meta name="keywords" content="<%$cur_cat_name_escape%>视频,<%$cur_cat_name_escape%>社区,<%$cur_cat_name_escape%>分享,<%$cur_cat_name_escape%>推荐,<%$cur_cat_name_escape%>SNS社区,视频类Pinterest" />
    <meta name="description" content="圈乐汇总了各种<%$cur_cat_name_escape%>视频圈子，并提供海量<%$cur_cat_name_escape%>视频资料，在这里，你能加入你所感兴趣的任何<%$cur_cat_name_escape%>视频圈子，尽享<%$cur_cat_name_escape%>视频带给你的最大乐趣。">
    <%else%>
        <%$cur_select_tag_escape=HTML::chars($cur_select_tag)%>
    <meta name="keywords" content="<%$cur_select_tag_escape%>,<%$cur_select_tag_escape%>视频,<%$cur_cat_name_escape%>视频,<%$cur_cat_name_escape%>社区" />
    <meta name="description" content="圈乐汇总了各种<%$cur_select_tag_escape%>视频，并提供海量<%$cur_cat_name_escape%>视频资料。">
    <%/if%>
<%/if%>
    <link rel="canonical" href="<%Util::circleCatUrl($cur_cat_key, $cur_select_tag, ['offset' => $pager.offset])%>" />
<%/block%>
<%block name="title" prepend%><%if $cur_cat_name%><%if $cur_select_tag == ''%><%$cur_cat_name_escape%>视频发现、分享和推荐<%else%><%$cur_select_tag_escape%>视频<%/if%>-<%$cur_cat_name_escape%>视频圈子汇总<%else%>全网最新、最热视频在线观看就在视频圈子社区<%/if%><%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="custom_css"%>
	<link rel="stylesheet" type="text/css" href="<%#resUrl#%>/css/video/group.css?v=<%#v#%>">
<%/block%>
<%block name="custom_js"%>
    <script>
        CIRCLE_ID = "<%$cur_cat_id|escape:'javascript'%>"||"0";        
        GROUP_ADD_URL = '/circle/addCatTag';
        GROUP_DEL_URL = '/circle/delCatTag';
    </script> 
    <script type="text/javascript" src="<%#resUrl#%>/js/video/group.js?v=<%#v#%>"></script>
<%/block%>

<%block name="bd"%>
        <div id="bd" class="layout_box">
            <%include file="inc/search.inc"%>
            <!--圈子内容-->
			<div id="circlepage" class="clearfix">
				<div class="circlelist">
					<%include file="guesslike/topcircles.php" circle_list=$circle_list.data pager=$pager no_marmot=1 inline%>
				</div>
                <div class="side">
                    <div class="circlecates">
                        <div class="hd">
                            <h3>圈子分类</h3>
                        </div>
                        <div class="bd">
                            <div class="tag_wrap cls" id="group_cat_list">
                                <a href="/category/all"<%if ! $cur_cat_name%> class="on"<%/if%>><span>全部</span></a>
                            <%$arrCatIdToKeyMap = Model_Data_Circle::$arrUrlKeyForCategorys%>
                            <%foreach $categorys as $k => $v%>
                                <a href="/category/<%$arrCatIdToKeyMap[$k]%>"<%if $cur_cat_name == $v%> class="on"<%/if%>><span><%$v|escape:"html"%></span></a>
                            <%/foreach%>
                            </div>
                        </div>
                    </div>
                    <!--标签-->
                    <div class="circletags">
                        <div class="hd">
                            <div id="xplus" class="xplus marmot xplus_tag" data--marmot="{page_id:'click_addtag'}"></div><h3>标签筛选</h3>
                        </div>
                        <div class="bd" id="tag_box" url_base="/category/<%$cur_cat_key%>">
                            <div class="tagadd clearfix" style="display: none; ">
                                <form id="formaddtag">
                                    <input class="ipt-text" placeholder="输入我的tag">
                                    <input class="ipt-submit" type="button" style="cursor:pointer;">
                                </form>
                            </div>
                            <div class="taglist clearfix">
                                <div class="tag-item tag-sys<%if empty($cur_select_tag)%> tag-item-on<%/if%>">
                                    <a href="/category/all"><span class="tag_link">不限</span></a>
                                </div>
                                <%$cat_user_tag_tmp=array_fill_keys((array)$cat_user_tag, true)%>
                                <%foreach $cat_sys_tag as $v%>
                                <%if ! isset($cat_user_tag_tmp[$v])%>
                                <%*如果系统tag和用户自定义的相同，则只显示用户的*%>
                                <div class="tag-item tag-sys<%if $cur_select_tag == $v%> tag-item-on<%/if%>">
                                    <a href="/category/<%$cur_cat_key%>/<%urlencode($v)%>"><span class="tag_link"><%$v|escape:"html"%></span></a>
                                </div>
                                <%/if%>
                                <%/foreach%>
                                <%foreach $cat_user_tag as $v%>
                                <div class="tag-item tag-cus<%if $cur_select_tag == $v%> tag-item-on<%/if%>">
                                    <a href="/category/<%$cur_cat_key%>/<%urlencode($v)%>"><span class="con tag_link"><%$v|escape:"html"%></span></a>
                                    <span class="del" data="<%urlencode($v)%>"></span>
                                </div>
                                <%/foreach%>                                
                            </div>
                        </div>
                        <div class="ft"></div>
                    </div>
                    <!--//标签 -->
                </div>
			</div>
            <!--end 圈子内容-->
        </div>
<%/block%>
<%block name="custom_foot"%>    
<script type="text/marmot">
{
    "page_id"   : "category<%$cur_cat_key%>"
}
</script>
<%/block%>
