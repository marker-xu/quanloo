<div class="y-hot-video-box">
    <div class="y-box-title">
      <h2>相关视频</h2>
    </div>
    <div class="y-box-main">
      <div class="y-scorll-box"> <a href="#" class="y-scorll-left  y-ico y-radius">向左</a> <a href="#" class="y-scorll-right y-ico y-radius">向右</a>
        <div class="y-scorll-middle">
          <ul class="y-v-list y-inline-ul cfix js-circle-carousel">
        	<%foreach $relatedVideos as $video%>
            <li>
              <dl class="y-v-box y-vbox-1">
                <dt><a href="<%Util::videoPlayUrl($video._id)%>" target="_blank"><img src="<%Util::videoThumbnailUrl($video.thumbnail)%>"></a></dt>
                <dd><a href="<%Util::videoPlayUrl($video._id)%>" target="_blank" title="<%$video.title|escape:'html'%>"><%Util::utf8SubStr($video.title, 16)%></a></dd>
              </dl>
            </li>
            <%/foreach%>
          </ul>
        </div>
      </div>
    </div>
</div>