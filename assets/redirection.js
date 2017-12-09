/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function isEmpty(obj) {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

jQuery(document).ready(function($) {
    
    $('.redirection-active').change(function() {
        
        var item = $(this);
        item.prop('disabled', true);

        var changeValue;
        if (item.is(":checked")) {
            changeValue = 1;
        } else {
            changeValue = 0;
        }
        var re_id = item.parent().parent().parent().attr('re_id');
        
         $.post(
            global.ajax, 
            {   
                id: re_id,
                value: changeValue,
                action: 'active_redirection' 
            }, 
            function(data) {
                console.log("Updated status redirection " + re_id + " to " + changeValue);
                item.prop('disabled', false);
            });
    })
    
    $('.button-delete').click(function() {
        
        var item = $(this);
        var row = item.parent().parent();
        item.prop('disabled', true);
        var re_id = row.attr('re_id');
         $.post(
            global.ajax, 
            {   
                id: re_id,
                action: 'delete_redirection' 
            }, 
            function(data) {
                console.log("Deleted redirection: " + re_id);
                row.remove();
            });
    })
    
    $(window).keydown(function(event){
        if(event.keyCode == 13) {
          event.preventDefault();
          return false;
        }
    });
    
    var searchRequest;
    $('#post_search').autoComplete({
            minChars: 2,
            source: function(term, suggest){
                    try { searchRequest.abort(); } catch(e){}
                    searchRequest = $.post(global.ajax, { search: term, action: 'search_site' }, function(res) {
                        if (isEmpty(res.data)) {
                            res.data = ["404"];
                        }
                        suggest(res.data);
                    });
            },
            renderItem: function (item){
                if (item === "404") {
                    return '<div class="autocomplete-suggestion" data-postid="0" data-val="Coupon/Post Not Found">Coupon/Post Not Found</div>';
                } 
                return '<div class="autocomplete-suggestion" data-postid="' + item['ID'] + '" data-val="' + item['post_title'] + '">' + item['post_title'] + '</div>';
            },
            onSelect: function(e, term, item){
                $('#post_id').val(item.data('postid'));
            }
    });
    
    var searchRequestStore;
    $('#store_search').autoComplete({
            minChars: 2,
            source: function(term, suggest){
                    try { searchRequestStore.abort(); } catch(e){}
                    searchRequestStore = $.post(global.ajax, { search: term, action: 'search_store' }, function(res) {
                        if (isEmpty(res.data)) {
                            res.data = ["404"];
                        }
                        suggest(res.data);
                    });
            },
            renderItem: function (item){
                
//                console.log(item);
                
                if (item === "404") {
                    return '<div class="autocomplete-suggestion" data-storeid="0" data-val="Store Not Found">Store Not Found</div>';
                } 
                return '<div class="autocomplete-suggestion" data-storeid="' + item['ID'] + '" data-val="' + item['post_title'] + '">' + item['post_title'] + '</div>';
            },
            onSelect: function(e, term, item){
                $('#store_id').val(item.data('storeid'));
            }
    });
    

    $('#dataTables-example').DataTable({
            responsive: true,
            "bDestroy": true
        });
        
    
     
});

