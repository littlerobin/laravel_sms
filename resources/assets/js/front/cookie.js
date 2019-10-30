var base_url = window.location.origin;
var winPath = window.location.pathname;
if(isEUMember && winPath.slice(4, 23) !== 'finish-registration') {
    window.addEventListener("load", function(){
        window.cookieconsent.initialise(
            {
                "palette": {
                    "popup": {
                        "background": "#eaf7f7 ",
                        "text": "#383838 "
                    },
                    "button": {
                        "background": "#22cd78",
                        "text": "#fff "
                    }
                },
                "theme": "classic",
                "position": "bottom-left",
                "content": {
                    "message": "To give you the best possible experience, this website uses cookies. Please accept our ",
                    "dismiss": "Accept",
                    "link": "Cookie Policy.",
                    "href": base_url + "/privacy/2"
                }
            })

    });
}