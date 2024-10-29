function showDivOnCheck(checkBoxId, divId) {
    var checkBox = document.getElementById(checkBoxId);
    var divToShow = document.getElementById(divId);
    if (checkBox.checked == true){
        divToShow.style.display = "";
    } else {
        divToShow.style.display = "none";
    }
}


function connect_merchant_center(url) {
    window.open(url);
    window.addEventListener('focus',function () {
        jQuery.post('#', {get_merchant_center_choices:true});
        location.reload();
    });
}


function startGCREditMode(){
    jQuery("input,select").prop("disabled", false);
    jQuery('#gcr_edit_button').hide();
    jQuery('#gcr_save_button').show();
}

