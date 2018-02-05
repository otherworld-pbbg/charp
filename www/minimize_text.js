jQuery(function(){
//http://jsfiddle.net/iambriansreed/bjdSF/
    var minimized_elements = $('p.minimize');
    
    minimized_elements.each(function(){    
        var t = $(this).text();        
        if(t.length < 120) return;
        
        $(this).html(
            t.slice(0,120)+'<span>... </span><a href="#" class="more">[Show more]</a>'+
            '<span style="display:none;">'+ t.slice(120,t.length)+' <a href="#" class="less">[Show less]</a></span>'
        );
        
    }); 
    
    $('a.more', minimized_elements).click(function(event){
        event.preventDefault();
        $(this).hide().prev().hide();
        $(this).next().show();        
    });
    
    $('a.less', minimized_elements).click(function(event){
        event.preventDefault();
        $(this).parent().hide().prev().show().prev().show();    
    });

});
