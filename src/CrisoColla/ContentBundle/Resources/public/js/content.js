var asset = $("#asset").html(); //global

if(asset)
{
    asset = asset+"app.php/notheme/";
}
else
{
    asset = "?notheme/";
}

function parentByClassName(element, classname)
{
    if(element.parentNode!=undefined && element.parentNode!=null)
    {
        if(element.parentNode.className.indexOf(classname)!=-1)
        {
            return element.parentNode;
        }
        else
        {
            return parentByClassName(element.parentNode, classname);
        }
    }

    return null;
}

function creator(element)
{
    var creator = parentByClassName(element, "creator");
    var title = creator.getElementsByTagName("input")[0];
    var text = creator.getElementsByTagName("textarea")[0];

    if(text.value!="" || title.value!="")
    {
        $.post( asset+"content/create", { "title": title.value, "text": text.value })
            .done(
                    function(data)
                    {
                        if(!isNaN(data))
                        {
                            $.ajax( asset+"content/"+data )
                                 .done(
                                    function(data)
                                    {
                                        $(creator).next().prepend(data);
                                        ready();
                                    }
                                )
                            ;
                            
                            title.value = "";
                            text.value = "";
                        }
                        else
                        {
                            alert("error");
                        }
                    }
                    )
            .error(
                    function(data)
                    {
                        alert("error");
                    }
                  )
            ;

    }
}

function ready()
{
    $(".content-element").mouseover(function() {
        $(".content-menu").addClass("hide"); // prevent some errors with the drop dawn
        $(this).find(".content-menu").removeClass("hide");

    });

    $(".content-element").mouseout(function() {
        if(!$(this).find(".content-menu").hasClass("open"))
        {
            $(this).find(".content-menu").addClass("hide");
        }
    });

    $(".creator-button").click(function(event) {
        creator(event.target);
    });

    $(".content-size").click(function(event){
        //var menu = parentByClassName(event.target, 'content-menu');
        var element = parentByClassName(event.target, 'content-element');
        var size = (element.className.match (/\bspan\S+/g) || []).join(' ').substr(4);
        var id = $(event.target).data("id");
        var type = $(event.target).data("type");

        $("#sizes a.border").removeClass('active');

        $("#sizes a.border").addClass(function() {
            if($(this).html() == size)
            {
                return "active";
            }
        });

        $("#sizes").data("id", id);
        $("#sizes").data("type", type);
        $("#sizes").data("element", element);
        
        
        $("#sizes").modal("show");
    });

    $("#sizes a.border").click(function(event){
        var size = "span"+event.target.innerHTML;
        var id = $("#sizes").data("id");
        var type = $("#sizes").data("type");
        var element = $("#sizes").data("element");

        if(id && type && element)
        {        
            $.post( asset+"content/update/"+id, { "size": size, "type": type })
            .done(
                    function(data)
                    {
                        if(data == "true")
                        {
                            $(element).removeClass (function (index, css) {
                                return (css.match (/\bspan\S+/g) || []).join(' ');
                            });

                            $(element).addClass(size);

                            $("#sizes").modal("hide");
                        }
                        else
                        {
                            alert("error1");
                        }
                    }
                    )
            .error(
                    function(data)
                    {
                        alert("error");
                    }
                  )
            ;
        }
    });

    $(".creator textarea").css("height", function(){
        return (2 * $(this).css("line-height").substr(0, $(this).css("line-height").indexOf("px")))+"px"
    });

    $(".creator textarea").keyup(function(event){
        
        if(event && event.keyCode) 
        {
            if(event.keyCode==13 | event.keyCode==86 | event.keyCode==8 | event.keyCode==46)
            {
                var lineheight = $(this).css("line-height").substr(0, $(this).css("line-height").indexOf("px"));
                var lines = $(this).val().split("\n").length;

                $(this).css("height", ((lines + 1)*lineheight)+"px");
            }

        }
    });
}

ready();
