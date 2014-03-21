
<div class="y-warehouse-box r">
	<%if $circleEntity.entity_type == 'tv'%>
    <dl class="y-inline-dl cfix y-warehouse-dl">
        <dt>
        	<a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank">
        		<img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>">
        	</a>
        </dt>
        <dd>
            <h3><a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank"><%Util::utf8SubStr($circleEntity.title, 24)%></a></h3>
            <%if $circleEntity.released_date%>
            <p>上映时间：<%Util::utf8SubStr($circleEntity.released_date, 14)%></p>
            <%/if%>
            <%if $circleEntity.cast%>
            <p>主演：<%Util::utf8SubStr($circleEntity.cast, 42)%></p>
            <%/if%>
        </dd>
    </dl>
    <%Util::episodesPager($circleEntity.episode, 8, $circleEntity.finished)%>
    <span class="y-ico y-ico-flag y-ico-flag-tv">电视剧</span>
    <%elseif $circleEntity.entity_type == 'movie'%>
    <dl class="y-inline-dl cfix y-warehouse-dl">
        <dt>
        	<a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank">
        		<img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>">
        	</a>
        </dt>
        <dd>
            <h3><a href="<%Util::entityInfoUrl($circleEntity)%>" target="_blank"><%Util::utf8SubStr($circleEntity.title, 24)%></a></h3>
            <%if $circleEntity.director%>
            <p>导演：<%Util::utf8SubStr($circleEntity.director, 18)%></p>
            <%/if%>
            <%if $circleEntity.cast%>
            <p>主演：<%Util::utf8SubStr($circleEntity.cast, 18)%></p>
            <%/if%>
        </dd>
        <dd>
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
   
    <span class="y-ico y-ico-flag y-ico-flag-movie">电影</span>
    <%elseif $circleEntity.entity_type == 'zongyi'%>
    <dl class="y-inline-dl cfix y-warehouse-dl">
        <dt><img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>"></dt>
        <dd>
            <h3><%Util::utf8SubStr($circleEntity.title, 24)%></h3>
            <%if $circleEntity.cast%>
            <p>主持人：<%Util::utf8SubStr($circleEntity.cast, 16)%></p>
            <%/if%>
            <%if $circleEntity.region%>
            <p>地区：<%Util::utf8SubStr($circleEntity.region, 18)%></p>
            <%/if%>
        </dd>
        <dd>
            <ul class="y-inline-dl cfix y-warehouse-list-li ">
                <%foreach $circleEntity.episode as $episode%>
                    <%if $episode@index < 3%>
                    <li>
                        <dl class="cfix">
                          <dt></dt>
                          <dd><a class="marmot" data--marmot="{page_id:'click_entity'}" href="<%$episode.play_url%>" target="_blank" title="<%$episode.pubtime%> <%$episode.title|escape:'html'%>"><%$episode.pubtime%> <%Util::utf8SubStr($episode.title, 30)%></a></dd>
                        </dl>
                    </li>
                    <%/if%>
                <%/foreach%>
            </ul>        
        </dd>
    </dl>

    <span class="y-ico y-ico-flag y-ico-flag-variety">综艺</span>
    <%elseif $circleEntity.entity_type == 'animation'%>
    <dl class="y-inline-dl cfix y-warehouse-dl">
        <dt><img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>"></dt>
        <dd>
            <h3><%Util::utf8SubStr($circleEntity.title, 24)%></h3>
            <%if $circleEntity.released_date%>
            <p>上映时间：<%Util::utf8SubStr($circleEntity.released_date, 14)%></p>
            <%/if%>
            <%if $circleEntity.region%>
            <p>地区：<%Util::utf8SubStr($circleEntity.region, 18)%></p>
            <%/if%>
            <%if $circleEntity.genre%>
            <p>类型：<%Util::utf8SubStr($circleEntity.genre, 18)%></p>
            <%/if%>
        </dd>
    </dl>
    <%Util::episodesPager($circleEntity.episode, 8, $circleEntity.finished)%>
    <span class="y-ico y-ico-flag y-ico-flag-anime">动漫</span>
    <%elseif $circleEntity.entity_type == 'star'%>
    <dl class="y-inline-dl cfix y-warehouse-dl">
        <dt><img src="<%Util::videoThumbnailUrl($circleEntity.thumbnail)%>"></dt>
        <dd>
            <h3><%Util::utf8SubStr($circleEntity.name, 24)%></h3>
            <%if $circleEntity.gender%>
            <p>性别：<%Util::utf8SubStr($circleEntity.gender, 18)%></p>
            <%/if%>
            <%if $circleEntity.birthday%>
            <p>生日：<%Util::utf8SubStr($circleEntity.birthday, 18)%></p>
            <%/if%>
            <%if $circleEntity.region%>
            <p>地区：<%Util::utf8SubStr($circleEntity.region, 18)%></p>
            <%/if%>
        </dd>
        <dd>
            <ul class="y-inline-dl cfix y-warehouse-list-li ">
                <%foreach $circleEntity.works as $work%>
                    <%if $work@index < 3%>
                    <li>
                        <dl class="cfix">
                          <dt></dt>
                          <dd><a class="marmot" data--marmot="{page_id:'click_entity'}" href="<%Util::entityInfoUrl($work)%>" target="_blank" title="<%$work.title|escape:'html'%>"><%Util::utf8SubStr($work.title, 38)%></a></dd>
                        </dl>
                    </li>
                    <%/if%>
                <%/foreach%>
            </ul>        
        </dd>
    </dl>

    <span class="y-ico y-ico-flag y-ico-flag-people">人物</span>
    <%/if%>
</div>