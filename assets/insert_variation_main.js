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

jQuery(document).ready(function($) {
    
    $("#iframe_sroll_up").click(function(){
    var $contents = $('#iframe-mt').contents();
    $contents.scrollTop(0);
    });

    $("#iframe_sroll_down").click(function(){
        var $contents = $('#iframe-mt').contents();
        $contents.scrollTop($contents.height());
    });
});

