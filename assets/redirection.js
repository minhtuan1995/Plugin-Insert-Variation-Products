/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//
//$(window).load(function ()
//{
//    
//});

function isEmpty(obj) {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

jQuery(document).ready(function($) {
    
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
                            res.data = ["Not Found"];
                        }
                        suggest(res.data);
                    });
            },
            renderItem: function (item){
                if (item === "Not Found") {
                    return '<div class="autocomplete-suggestion" data-postid="0" data-val="Not Found">Not Found</div>';
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
                    searchRequestStore = $.post(global.ajax, { search: term, action: 'search_site' }, function(res) {
                        if (isEmpty(res.data)) {
                            res.data = ["Not Found"];
                        }
                        suggest(res.data);
                    });
            },
            renderItem: function (item){
                if (item === "Not Found") {
                    return '<div class="autocomplete-suggestion" data-storeid="0" data-val="Not Found">Not Found</div>';
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

