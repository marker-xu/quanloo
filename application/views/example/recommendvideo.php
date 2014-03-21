<%extends file="common/base.tpl"%>

<%block name="title"%>登录页<%/block%>
<%block name="view_conf"%>
<%/block%>
<%block name="custom_css"%>
<style type="text/css">
table{border:1px solid #69C}
 tr td{border-top:1px solid #69C; border-left:0px; border-right:0px;}
</style>
<%/block%>
<%block name="bd"%>

<div id="bd">          
		<h3>推荐视频查询</h3>
            <form action="/example/recommendvideo" method="post">
            <%Form::hidden('csrf_token', Security::token())%>
            VID: <input type="text" name="vid" value="<%$default.vid%>" style="width:250px;" />&nbsp;&nbsp;
            <input type="submit" value="查询" />
			<%if isset($default.video)%>
			<p>
				视频信息： <br/>
				title- <%$default.video.title%><br/>
				tag- <%implode(", ", $default.video.tag)%><br/>
				desc- <%$default.video.desc%><br/>
				url <a href="<%$default.video.play_url%>" target="_blank"><%$default.video.play_url%></a><br/>
			</p>
			<%/if%>
            </form>
            <%if isset($result)%>
            <div style="margin-top:25px;">
	            <p>
	            	Result: 
	            	<table id="result" rules="rows" >
	            		<%if $result%>
		            		<tr>
		            			<th width="100px">序号</th>
		            			<th width="150px">Vid</th>
		            			<th width="300px">Title</th>
		            			<th width="250px">Tag</th>
		            			<th width="250px">Desc</th>
		            			<th>Url</th>
		            		</tr>
		            		
		            		<%foreach $result as $row%>
		            		<tr style="padding-bottom:10px;" border=0px;>
		            			<td><%($row@index+1)%></td>
		            			<td><%$row._id%></td>
		            			<td><%$row.title%></td>
		            			<td><%implode(", ", $row.tag)%></td>
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
	            <%if isset($alter_result)%>
            	<p>
            		热门推荐补足：
            		<textarea cols=80 style="height:200px;">
	            		<?php print_r($alter_result); ?>
	            	</textarea>
            	</p>
            	<%/if%>
            </div>
            <%/if%>
            </div>
<%/block%>

<%block name="foot_js"%>

<%/block%>