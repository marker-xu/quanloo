Dom.ready(function(){

    W("#file_uploader").on("change",function(e){
        W("#file_faker input").setValue(this.value)
    });

    var img = W("#edit_img img")[0];
    if(img){
        //调整图片尺寸与移动其位置都会触发此函数
        var resetImage = function(){
            var el = this._obj || this.Drag;
            //console.log(~~W(el).getCurrentStyle("width"))
            if( parseInt(W(el).getCurrentStyle("width")) > 180){
                "top,left,width,height".replace(/[^, ]+/g, function(name){
                    W("#img_"+name).setValue( W(el).getCurrentStyle(name) )
                });
                el.style.backgroundImage = "url("+ img.src +")"
                el.style.backgroundPosition = "-" + W(el).getCurrentStyle("left") +" -"+W(el).getCurrentStyle("top")
            }else{
                el.style.width = "180px";
                el.style.height = "180px"
            }
        }

        var rs = new QW.Resize("cutter", {
            Max: true,
            mxContainer: "cutter_wrapper",
            onResize:resetImage
        });

        rs.Set("rRightDown", "right-down");
        rs.Set("rLeftDown", "left-down");

        rs.Set("rRightUp", "right-up");
        rs.Set("rLeftUp", "left-up");

        rs.Set("rRight", "right");
        rs.Set("rLeft", "left");

        rs.Set("rUp", "up");
        rs.Set("rDown", "down");
        rs.Scale = true;
        rs.Ratio = 1

        new QW.Drag("cutter", {
            Limit: true,


            mxContainer: "cutter_wrapper",
            onMove:resetImage
        });
    }

    

    
})

