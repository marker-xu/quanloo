Dom.ready(function(){
    QW.pageGlobal = QW.pageGlobal || {};
    var trim = StringH.trim;   

    var aurl = function(url){
        return QW.Config.get('host') + url;
    }

    use('Ajax', function(){
        /*加关注*/
        var guesst_temp = '<li class="cls"><a href="{0}" class="ret-circle-title" target="_blank">{1}</a><a href="#" class="ret-circle-add b-follow" data-action="{\'id\':\'{2}\'}"></a></li>';
        W('#btn-guanzhu').on('click', function(e){
            e.preventDefault();
                    
            var $srcData = {
                'type': 'btn-guanzhu'
                , 'source': this
                , 'event': e.type
            };
            
            VideoAction.userinfo(function(u){
                QW.pageGlobal.panelConcern = QW.pageGlobal.panelConcern || new Dialog(g('panel-follow'));
                var _t = (new Date()).getTime();
                QW.Ajax.get(aurl('/circle/concern'), {
                    title : W('#btn-guanzhu').attr('data-kw'),
                    _t : _t
                }, function(d){
                    if('ok' != d.err && 'circle.already_subscribed' != d.err){
                        return;
                    }
                    /*更新浮层内容*/
                    var data = d.data,
                        circleUrl = PageUtil.circleUrl(data._id, null, data);
                    W('#circlename').html(data.title);
                    W('#circletitle').html(data.title).attr('href', circleUrl);
                    W('#circleUrl').attr('href', circleUrl);
                    W('#tn_path').attr('src', PageUtil.circlePreviewPic(data.tn_path));
                    W('#circle-follow-btn').attr('data-action', "{'id':'" + data._id + "'}");
                    /*获取“你可能感兴趣圈子”*/
                    QW.Ajax.get(aurl('/circle/related'), {
                        query : W('#btn-guanzhu').attr('data-kw'),
                        count : 6,
                        _t : _t
                    }, function(c){
                        if(c.err != 'ok'){
                            return;
                        }
                        /*更新猜你喜欢圈子*/
                        var _guessItems = [];
                        W('#guess-title').html('rel' == c.data.type? '你可能感兴趣的圈子' : '大家正在关注的圈子');
                        c.data.circles.forEach(function(i){
                            _guessItems.push(StringH.format(guesst_temp, PageUtil.circleUrl(i._id, null, i), i.title, i._id));
                        });
                        W('#guess-list').html(_guessItems.join(''));
                        QW.pageGlobal.panelConcern.show();
                    });
                });
            }, $srcData);
        });
        W('#panel-follow .x-close').click(function(e){
            e.preventDefault();
            QW.pageGlobal.panelConcern.hide();
        });
    });
});