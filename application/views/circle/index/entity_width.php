
<%if $circleEntity.entity_type == 'tv'%>
<div class="y-warehouse-box-width cfix">
	<div class="r">
    	<%Util::episodesPager($circleEntity.episode, 13, $circleEntity.finished)%>
	</div>
    <dl class="y-inline-dl cfix y-warehouse-dl l">
        <dt>
        	<a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank">
        		<img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>">
        	</a>
        </dt>
        <dd>
            <h3><a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank"><%Util::utf8SubStr($circleEntity.title, 30)%></a></h3>
            <%if $circleEntity.released_date%>
            <p>上映时间：<%Util::utf8SubStr($circleEntity.released_date, 20)%></p>
            <%/if%>
            <%if $circleEntity.cast%>
            <p>主演：<%Util::utf8SubStr($circleEntity.cast, 54)%></p>
            <%/if%>
        </dd>
    </dl>
    <div class="y-warehouse-text l">
    	剧情简介：<%Util::utf8SubStr($circleEntity.desc, 120)%>
    	<a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank">详细信息&gt;&gt;</a>
	</div>
    <span class="y-ico y-ico-flag y-ico-flag-tv">电视剧</span>
</div>
<%elseif $circleEntity.entity_type == 'movie'%>
<div class="y-warehouse-box-width cfix y-warehouse-box-movie">
    <dl class="y-inline-dl cfix y-warehouse-dl l">
        <dt>
        	<a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank">
        		<img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>">
        	</a>
        </dt>
        <dd>
            <h3><a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank"><%Util::utf8SubStr($circleEntity.title, 48)%></a></h3>
            <%if $circleEntity.director%>
            <p>导演：<%Util::utf8SubStr($circleEntity.director, 42)%></p>
            <%/if%>
            <%if $circleEntity.cast%>
            <p>主演：<%Util::utf8SubStr($circleEntity.cast, 42)%></p>
            <%/if%>
            <ul class="y-inline-dl cfix y-warehouse-list-li ">
                <li>
                    <dl class="cfix">
                      <dt></dt>
                      <dd><a class="marmot" href="<%$circleEntity.play_url%>" data--marmot="{page_id:'click_entity'}" target="_blank">播放影片</a></dd>
                    </dl>
                </li>
            </ul>
        </dd>
    </dl>
    <div class="y-warehouse-text l">
    	剧情简介：<%Util::utf8SubStr($circleEntity.desc, 160)%>
    	<a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank">详细信息&gt;&gt;</a>
    </div>
    <span class="y-ico y-ico-flag y-ico-flag-movie">电影</span>
</div>
<%elseif $circleEntity.entity_type == 'zongyi'%>
<div class="y-warehouse-box-width cfix y-warehouse-box-variety">
    <dl class="y-inline-dl cfix y-warehouse-dl l">
        <dt><img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>"></dt>
        <dd>
            <h3><%Util::utf8SubStr($circleEntity.title, 30)%></h3>
            <%if $circleEntity.cast%>
            <p>主持人：<%Util::utf8SubStr($circleEntity.cast, 22)%></p>
            <%/if%>
            <%if $circleEntity.region%>
            <p>地区：<%Util::utf8SubStr($circleEntity.region, 24)%></p>
            <%/if%>
        </dd>
    </dl>
    <ul class="y-inline-ul y-inline-dl cfix y-warehouse-list-li l">
    	<%foreach $circleEntity.episode as $episode%>
        	<%if $episode@index < 6%>
            <li>
                <dl class="cfix">
                  <dt></dt>
                  <dd><a class="marmot" href="<%$episode.play_url%>" data--marmot="{page_id:'click_entity'}" target="_blank" title="<%$episode.pubtime%> <%$episode.title|escape:'html'%>"><%$episode.pubtime%> <%Util::utf8SubStr($episode.title, 16)%></a></dd>
                </dl>
            </li>
            <%/if%>
        <%/foreach%>
    </ul>
    <span class="y-ico y-ico-flag y-ico-flag-variety">综艺</span>
</div>
<%elseif $circleEntity.entity_type == 'animation'%>
<div class="y-warehouse-box-width cfix">
	<div class="r">
	<div class="y-warehouse-list-a r">
    	<%Util::episodesPager($circleEntity.episode, 13, $circleEntity.finished)%>
	</div>
	</div>
    <dl class="y-inline-dl cfix y-warehouse-dl l">
        <dt><img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>"></dt>
        <dd>
            <h3><%Util::utf8SubStr($circleEntity.title, 30)%></h3>
            <%if $circleEntity.released_date%>
            <p>上映时间：<%Util::utf8SubStr($circleEntity.released_date, 20)%></p>
            <%/if%>
            <%if $circleEntity.region%>
            <p>地区：<%Util::utf8SubStr($circleEntity.region, 24)%></p>
            <%/if%>
            <%if $circleEntity.genre%>
            <p>类型：<%Util::utf8SubStr($circleEntity.genre, 24)%></p>
            <%/if%>
        </dd>
    </dl>
    <div class="y-warehouse-text l">剧情简介：<%Util::utf8SubStr($circleEntity.desc, 130)%></div>
    <span class="y-ico y-ico-flag y-ico-flag-anime">动漫</span>
</div>
<%elseif $circleEntity.entity_type == 'star'%>
<div class="y-warehouse-box-width cfix y-warehouse-box-people">
    <dl class="y-inline-dl cfix y-warehouse-dl l">
        <dt><img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>"></dt>
        <dd>
            <h3><%Util::utf8SubStr($circleEntity.name, 30)%></h3>
            <%if $circleEntity.gender%>
            <p>性别：<%Util::utf8SubStr($circleEntity.gender, 24)%></p>
            <%/if%>
            <%if $circleEntity.birthday%>
            <p>生日：<%Util::utf8SubStr($circleEntity.birthday, 24)%></p>
            <%/if%>
            <%if $circleEntity.region%>
            <p>地区：<%Util::utf8SubStr($circleEntity.region, 24)%></p>
            <%/if%>
        </dd>
    </dl>
    <div class="y-warehouse-text l">人物简介：<%Util::utf8SubStr($circleEntity.desc, 130)%></div>
    <ul class="y-inline-ul y-inline-dl cfix y-warehouse-list-li l">
    	<%foreach $circleEntity.works as $work%>
        	<%if $work@index < 6%>
            <li>
                <dl class="cfix">
                  <dt></dt>
                  <dd><a class="marmot" href="<%Util::entityInfoUrl($work)%>" data--marmot="{page_id:'click_entity'}" target="_blank" title="<%$work.title|escape:'html'%>"><%Util::utf8SubStr($work.title, 16)%></a></dd>
                </dl>
            </li>
            <%/if%>
        <%/foreach%>
    </ul>
    <span class="y-ico y-ico-flag y-ico-flag-people">人物</span>
</div>
<%/if%>
