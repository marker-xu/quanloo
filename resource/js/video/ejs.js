;
(function(DOC){
    this.QW = this.QW || {};
    //文档 http://www.cnblogs.com/rubylouvre/archive/2011/10/23/2221295.html
    this.QW.quote =  window.JSON && JSON.stringify || String.quote ||function (str) {
        str = str.replace(/[\x00-\x1f\\]/g, function (chr) {
            var special = metaObject[chr];
            return special ? special : '\\u' + ('0000'+chr.charCodeAt(0).toString(16)).slice(-4);
        });
        return '"' + str.replace(/"/g, '\\"') + '"';
    }
    if(!String.prototype.trim){
        String.prototype.trim = function(){
            return this.replace(/^[\s\xa0]+|[\s\xa0]+$/g, '');
        }
    }
    var
    metaObject = {
        '\b': '\\b',
        '\t': '\\t',
        '\n': '\\n',
        '\f': '\\f',
        '\r': '\\r',
        '\\': '\\\\'
    },
    _startOfHTML = "\t__views.push(",
    _endOfHTML = ");\n",
    sRight = "&>",
    rLeft = /\s*<&\s*/,
    rRight = /\s*&>\s*/,
    rAt = /(^|[^\w\u00c0-\uFFFF_])(@)(?=\w)/g
    var ejs2 = QW.ejs = function(id,data){
        if(!ejs2[id]){
            var rleft = rLeft, rright = rRight, sright = sRight,startOfHTML = _startOfHTML, endOfHTML = _endOfHTML, str , logic,
            el = DOC.getElementById(id);
            if (!el) throw "can not find the target element";
            str = el.innerHTML;
            var arr = str.trim().split(rleft),
            buff = ["var __views = [];\n"],temp = [],i = 0,n = arr.length,els,segment;
            while(i < n){//逐行分析，以防歧义
                segment = arr[i++];
                els = segment.split(rright);
                if(~segment.indexOf(sright) ){//这里不使用els.length === 2是为了避开IE的split bug
                    switch (els[0].charAt(0)) {
                        case "="://处理后台返回的变量（输出到页面的);
                            logic = els[0].substring(1);
                            if(logic.indexOf("@")!==-1){
                                temp.push(startOfHTML, logic.replace(rAt,"$1data."), endOfHTML);
                            }else{
                                temp.push(startOfHTML, logic, endOfHTML);
                            }
                            break;
                        case "#"://处理注释
                            break;
                        default://处理逻辑
                            logic = els[0];
                            if(logic.indexOf("@")!==-1){
                                temp.push(logic.replace(rAt,"$1data."), "\n");
                            }else{
                                temp.push(logic, "\n");
                            }
                    }
                    //处理静态HTML片断
                    els[1] &&  temp.push(startOfHTML, QW.quote.call(null,els[1]), endOfHTML)
                }else{
                    //处理静态HTML片断
                    temp.push(startOfHTML, QW.quote.call(null,els[0]),endOfHTML);
                }
            }

            ejs2[id] = new Function("data",buff.concat(temp).join("")+';return __views.join("");');
            return  ejs2[id]( data )
        }
        return  ejs2[id]( data )
    }
})(document);