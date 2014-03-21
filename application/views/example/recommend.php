<%extends file="common/base.tpl"%>

<%block name="title"%>登录页<%/block%>
<%block name="view_conf"%>
<%/block%>

<%block name="bd"%>
<div id="bd">          
            <h3>圈子推荐视频查询</h3>
            <form action="/example/recommend" method="post">
            <%Form::hidden('csrf_token', Security::token())%> 
            <p><input type="radio" name="type" value=0 <%if !$default.type%>checked<%/if%> />首页推荐 
            <input type="radio" name="type" value=1 <%if $default.type%>checked<%/if%> />圈子推荐</p>
            <p>圈子ID: <input type="text" name="cid" value="<%$default.cid%>" style="width:250px;" /></p>
            <p>offset: <input type="text" name="offset" value="<%$default.offset%>" />&nbsp;&nbsp;
            count: <input type="text" name="count" value="<%$default.count%>" />
            </p>
            <input type="submit" value="查询" />
			<%if isset($default.circle)%>
			<p>
				圈子信息： <br/>
				title- <%$default.circle.title%><br/>
			</p>
			<%/if%>
            </form>
            <%if isset($result)%>
            <div style="margin-top:25px;">
            	<%if isset($home_page_max_num)%>
            	<p>最大序号： <%$home_page_max_num%></p>
            	<%/if%>
	            <p>
	            	视频列表
	            	<table border="1px">
	            		<%if $result%>
	            		<tr>
	            			<th width="150px">Vid</th>
	            			<th width="150px">Rec_type</th>
	            			<th width="150px">timestamp</th>
	            			<th width="150px">sort_num</th>
	            			<th width="150px">up_date</th>
	            			<th width="300px">Title</th>
	            			<th width="250px">Tag</th>
	            			<th>Url</th>
	            		</tr>
	            		
	            		<%foreach $result as $row%>
	            		<tr style="padding-bottom:10px;" align="right">
	            			<td><%$row._id%></td>
	            			<td><%$row.rec_type%></td>
	            			<td><%date("Y-m-d H:i:s", $row.timestamp)%></td>
	            			<td><%$row.sort_num%></td>
	            			<td><%date("Y-m-d H:i:s", $row.upload_time->sec)%></td>
	            			<td><%$row.title%></td>
	            			<td><%if $row.tag%><%implode(", ", $row.tag)%><%/if%></td>
	            			<td><%if $row.play_url%><a href="<%$row.play_url%>" target="_blank"><%$row.play_url%><%/if%></a></td>
	            		</tr>	
	            		<%/foreach%>
	            		<%else%>
	            		<tr>
	            			<td colspan="4">No Result</td>
	            		</tr>
	            		<%/if%>
	            	</table>
	            </p>	
            </div>
            <%/if%>
            </div>
<%/block%>

<%block name="foot_js"%>

<%/block%>