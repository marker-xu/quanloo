<%extends file="common/base.tpl"%>

<%block name="title"%>登录页<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="bd"%>
<div id="bd">          
			<h3>推荐视频查询</h3>
            <form action="/example/guessuser" method="post">
            <%Form::hidden('csrf_token', Security::token())%>
            	<p>用户ID: <input type="text" name="uid" value="<%$default.uid%>" style="width:250px;" /></p>
            	<p>视频显示的记录数: <input type="text" name="v_count" value="<%$default.v_count%>" style="width:250px;" /></p>
            	<p>圈子显示的记录数: <input type="text" name="c_count" value="<%$default.c_count%>" style="width:250px;" /></p>
            <input type="submit" value="查询" />
            <%if isset($default.user)%>
			<p>
				用户信息： <br/>
				nick- <%$default.user.nick|escape:"html"%><br/>
				email- <%$default.user.email|escape:"html"%><br/>
				intro- <%$default.user.intro|escape:"html"%><br/>
			</p>
			<%/if%>
            </form>
            <%if isset($result)%>
            <div style="margin-top:25px;">
            	
	            <p>
	            	推荐的视频（推荐类型：1 相关推荐， 2 圈子推荐，3 首页推荐）: 
	            	<table border="1px">
	            		<%if $result.videos%>
	            		<tr>
	            			<th width="150px">Vid</th>
	            			<th width="300px">Title</th>
	            			<th width="250px">Tag</th>
	            			<th width="100px">推荐类型</th>
	            			<th width="250px">Desc</th>
	            			<th>Url</th>
	            		</tr>
	            		
	            		<%foreach $result.videos as $row%>
	            		<tr style="padding-bottom:10px;">
	            			<td><%$row._id%></td>
	            			<td><%$row.title%></td>
	            			<td><%implode(", ", $row.tag)%></td>
	            			<td><%if isset($vid_from[$row._id])%><%$vid_from[$row._id]%><%/if%></td>
	            			<td><%$row.desc%></td>
	            			<td><a href="<%$row.play_url%>" target="_blank"><%$row.play_url%></a></td>
	            		</tr>	
	            		<%/foreach%>
	            		<%else%>
	            		<tr>
	            			<td colspan="4">No Result</td>
	            		</tr>
	            		<%/if%>
	            	</table>
	            </p>	
            	<p>
            		推荐的圈子：
            		
            		<ul>
            		<%if $result.circles%>
            		<%foreach $result.circles as $row%>
            			<li><%$row._id%>--<%$row.title%>&nbsp&nbsp;-- <%if isset($cid_from[$row._id])%><%$cid_from[$row._id]%><%/if%></li>
            		<%/foreach%>
            		<%else%>
            		No Result
            		<%/if%>
            		</ul>
            	</p>
            </div>
            <%/if%>
            </div>
<%/block%>

<%block name="foot_js"%>

<%/block%>