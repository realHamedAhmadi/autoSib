// ==UserScript==
// @name         diabet
// @namespace    http://tampermonkey.net/
// @version      2025-07-15
// @description  try to take over the world!
// @author       You
// @match        https://sib.umsu.ac.ir/sibnew/service/family-form?formId=7971&hash=epWUxlhJN0TX9QQDl1eJ2YNUSY5jLRNb4Os6x5ytKh5Jvv10pwc9u1sRLI%2Bu6g5Cag%2F1ZLzJMWYfIUeeiZciAw%3D%3D&basicVisitId
// @require      https://code.jquery.com/jquery-3.7.1.min.js
// @icon         https://www.google.com/s2/favicons?sz=64&domain=ac.ir
// @grant        none
// ==/UserScript==

(function() {

    setTimeout(function(){
        if($('.v-tab')[0]){
            getLink();
        }
    },1000)

    function getLink(){
        let item=getItem();
        const tabs=$('.v-tab');
        if(item[0]){

        }else{
            tabs.eq(1).click();
            setTimeout(function(){
                item=getItem();

                if(item[0]){
                    console.log(item.find('a')[0],'fdfdfd')
                    window.location.href=item.find('a').attr('href');
                    gethyperTension();
                }else{
                    window.close();
                }
            },1000)
        }
    }

    gethyperTension()
    function gethyperTension(){
        setTimeout(function(){
        },1000)
        $('#answer611').click();
    }
    function getItem(){
        return $('.v-list-item:contains("مراقبت بيمار مبتلا به فشار خون")');
    }
})();
