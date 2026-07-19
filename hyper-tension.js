// ==UserScript==
// @name         hyper-tension
// @namespace    http://tampermonkey.net/
// @version      2025-07-15
// @description  try to take over the world!
// @author       You
// @match        https://sib.umsu.ac.ir/sibnew/service/family-form?formId=7971*
// @require      https://code.jquery.com/jquery-3.7.1.min.js
// @icon         https://www.google.com/s2/favicons?sz=64&domain=ac.ir
// @grant        none
// ==/UserScript==

(function() {

    let time=2000;
    setTimeout(function(){
        getHyperTension();
    },time)
    function getHyperTension(){
        const checkbox=$("#answer611[value='128980']");
        console.log(checkbox[0],'ok checkbox',checkbox.prop('checked'))
        if (!checkbox.prop('checked')){
            checkbox.closest('.v-input__slot').click();
        }
        let sis=getRandomArray([100,115,120,125,130,135,140])
        let dia=getRandomArray([65,70,75,80,85]);
        $("#answer010021").val(sis);
        $("#answer010021").focus();
        $("#answer010112").val(dia);
        $("#answer010362").val(sis-getRandomArray([5,10]));
        $("#answer010411").val(dia);
        $("#answer1315541").closest('.v-radio').click();
        $("#answer1315570").closest('.v-radio').click();
        $("#answer1315520").closest('.v-radio').click();

        $("#answer1116460").closest('.v-radio').click();
        $("#answer1124470").closest('.v-radio').click();
        $("#answer1315601").closest('.v-radio').click();
        $("#answer1315611").closest('.v-radio').click();
        setTimeout(function(){
            $("#answer1315551").closest('.v-radio').click();
            $("#answer1315560").closest('.v-radio').click();
        },time+=500);
        //submitForm();
    }


    function getRandomArray(array) {
        return array[Math.floor(Math.random() * array.length)]
    }

    function submitForm() {
        setTimeout(function(){
            $('button:contains("ثبت و ذخیره سازی فرم")').click();
            nextForm();
        },time+=500)
    }
    function nextForm() {
        setTimeout(function(){
            const btn= $('button:contains("ثبت نهایی و ذخیره مراقبت")');
            if (btn[0]){
                $('.v-input__slot').find("input[id^='datePicker']").val('');
                submitFinalForm(btn)
            }else{
                nextForm();
            }
        },1000)
    }

    function setOtherCase(finalBtn) {
        setTimeout(function(){
            const input=$('*:contains("علت عدم ارجاع")').closest('.v-input__slot');
            if (!input[0]){
               setOtherCase(finalBtn);
               return false;
            }
            input.click();
            setTimeout(function(){
                $('#list-item-252-3').click();
                input.closest('.v-dialog').find('*:contains("ثبت")').click();
                closeWindow(finalBtn);
            },500)
        },1000)
    }
    function submitFinalForm(finalBtn) {
        setTimeout(function(){
            finalBtn.click();
            if ($('button:contains("ارجاع")')){
                setOtherCase(finalBtn);
            }else{
               closeWindow(finalBtn);
           }
        },1000)
    }

    function closeWindow(finalBtn) {
        setTimeout(function(){
            if ($('button:contains("بستن")').closest('.v-sheet')[0].style.display!=='none'){
                window.close();
            }else {
                //submitFinalForm(finalBtn);
            }
        },1000)
    }
})();
