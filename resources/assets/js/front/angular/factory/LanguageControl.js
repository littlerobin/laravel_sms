angular.module('frontCallburnApp').factory('LanguageControl',
    function (Restangular) {

        return {
            GetLanguage : function (LocalStorageKey,LanguageList) {
                var browserLanguage = ( navigator.language || navigator.userLanguage) . split('-')[0];
                var lang = {
                    code : "en",
                    name : "ENG"
                }

                if(localStorage.getItem(LocalStorageKey)) {

                    lang.code = localStorage.getItem("CurrentUserLanguageCode");
                    lang.name = localStorage.getItem("CurrentUserLanguageName");

                } else {

                    for (key in LanguageList) {

                        if(LanguageList[key].code == browserLanguage) {
                            lang.code = browserLanguage;
                            lang.name = language.name;
                        }
                    }

                }

                return lang;
            },

            GetLanguagesList: function (data) {
                return Restangular.all('front-data/languages').post(data);
            }

        };





    });