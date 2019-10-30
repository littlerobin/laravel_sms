var base_url = window.location.origin;
function chooseLang (lang) {

    if (!lang.id) { return lang.text; }
    var $lang = $(
        '<span class="d-flex flex-row justify-content-start align-items-center"><img src="' +base_url+ '/laravel_assets/callburn/images/flags/' + lang.element.value.toLowerCase() + '.svg" class="img-flag" /> ' + '<span class="hidden-xs">' + lang.text + '</span>' + '</span>'
    );
    return $lang;
};

$(".choose_lang").select2({
    templateResult: chooseLang,
    templateSelection: chooseLang,
    minimumResultsForSearch: -1,
    dropdownAutoWidth : true,
    width: 'auto'
});