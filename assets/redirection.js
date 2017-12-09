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
    
    $(".btn[data-target='#myEditModal']").click(function() {
            var columnHeadings = $("thead th").map(function() {
                      return $(this).text();
                   }).get();
            columnHeadings.pop();
            var columnValues = $(this).parent().siblings().map(function() {
                      return $(this).text();
            }).get();
            var columnChecks = $(this).parent().siblings().map(function() {
                      return $(this).children().children().is(":checked");
            }).get();
            
       var modalBody = $('<div id="modalContent"></div>');
       var modalForm = $('<form role="form" id="edit-redirection-modal" name="edit-redirection-modal" action="admin-ajax.php" method="post"></form>');
       $.each(columnHeadings, function(i, columnHeader) {
           var formGroup;
           var columnID = columnHeader.replace(/ /g, '_').toLowerCase();
           if (columnHeader == "ReID" || columnHeader == "PID") {
               formGroup = $('<div class="form-group hidden"></div>');
           } else {
               formGroup = $('<div class="form-group"></div>');
           }
            formGroup.append('<label for="'+columnHeader+'">'+columnHeader+'</label>');
            
            var disableInput = "";
            if (columnHeader == "Type" || columnHeader == "Title" || columnHeader == "Non/Re/All") {
               disableInput = "disabled";
            } 
            
            if (columnHeader == "Status") {
               var string = '<select class="form-control" name="'+columnID+'" id="'+columnID+'" value="'+columnValues[i]+'" '+ disableInput +'>';
                
               if (columnChecks[i]) {
                   string += '<option value="1" selected>Enable</option><option value="0">Disable</option></select>';
               } else {
                   string += '<option value="1">Enable</option><option value="0" selected>Disable</option></select>';
               }
               
                formGroup.append(string); 
                
            } else {
                formGroup.append('<input class="form-control" name="'+columnID+'" id="'+columnID+'" value="'+columnValues[i]+'" '+ disableInput +'/>'); 
            }
            
            modalForm.append(formGroup);
       });
       modalBody.append(modalForm);
       $('.modal-body').html(modalBody);
     });
     
     $('.modal-footer .btn-primary').click(function() {
        var data = $('#edit-redirection-modal').serializeArray();
        $.post(
            global.ajax, 
            {   
                data: data,
                action: 'update_redirection' 
            }, 
            function(data) {
                $('#close-update-modal').click();
                $("#redirect_url_" + data.data.input.reid).html(data.data.input.redirect_url);
                if (data.data.input.status == "1") {
                    $("#re_active_" + data.data.input.reid).prop('checked', true);
                    $("#re_active_" + data.data.input.reid).parent().attr('class', 'toggle btn btn-xs btn-primary');
                } else {
                    $("#re_active_" + data.data.input.reid).prop('checked', false);
                    $("#re_active_" + data.data.input.reid).parent().attr('class', 'toggle btn btn-xs btn-default off');
                }
            });
            
     });
    
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
            "bDestroy": true,
            "autoWidth": false
        });
        
    
     
});

