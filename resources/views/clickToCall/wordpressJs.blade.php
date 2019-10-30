

document.querySelector('table.form-table').classList.remove('form-table');

var snippetId = parseInt('{{$id}}');
var ctcJs = '{!! $apiJs !!}';
var defaultType = parseInt('{!! $type !!}');

if(! localStorage.getItem('snippet-type')) {
    switch (defaultType) {

        case 1:
            localStorage.setItem('snippet-type','open');
            break;
        case 2:
            localStorage.setItem('snippet-type','semiopen');
            break;
        case 3:
            localStorage.setItem('snippet-type','closed');
            break;
    }
}

if (ctcJs && !document.getElementById("ctc_code").value.trim()) {
    document.getElementById("ctc_code").value = ctcJs;
}



document.getElementById('submit').addEventListener('click', function(e) {

    var checked = document.querySelector('input[type="radio"]:checked');
    if(checked) {
        localStorage.setItem('snippet-type',checked.id);

    }

});

document.getElementById('apply-button').addEventListener('click', function(e) {

    e.preventDefault();

    this.setAttribute('disabled','disabled');
    this.classList.add('not-allowed');


    if(document.getElementById('callburn-script')) {
        document.getElementById('callburn-script').remove();
    }
    var contents = document.getElementsByClassName('snippet-main-content');
    if(contents) {
        for (var i = 0; i < contents.length; i++) {
            contents[i].remove();
        }
    }

    try {
        var javascript = document.getElementById("ctc_code").value.replace('<script id="callburn-api-script">', "").replace("</script>", "");
        eval(javascript);

    } catch (e) {
        alert('something went wrong');
    }

    var checkSnippet = setInterval(function () {

        if(window.loaded) {
            document.getElementById('apply-button').removeAttribute('disabled');
            document.getElementById('apply-button').classList.remove('not-allowed');
            window.loaded = false;
            clearInterval(checkSnippet);
        }

    },500)


});


var radios = document.getElementsByClassName('change-type');

var changeJsByType = function(type) {

    var js = document.getElementById("ctc_code").value;
    //var
    var result = js.replace(/var\ type\=parseInt\("[0-9]\"\)/, 'var type=parseInt("' + type + '")');

    document.getElementById("ctc_code").value = result;

};


for (var i = 0; i < radios.length; i++) {

    radios[i].addEventListener('click', function() {
        var id = this.id;

        switch (id) {
            case 'open':
                changeJsByType(1);
                break;
            case 'semiopen':
                changeJsByType(2);
                break;
            case 'closed':
                changeJsByType(3);
                break;
        }

    });



    switch (localStorage.getItem('snippet-type')) {
        case "open":
            document.getElementById("open").checked = true;
            break;
        case "semiopen":
            document.getElementById("semiopen").checked = true;
            break;
        case "closed":
            document.getElementById("closed").checked = true;
            break;
    }

}