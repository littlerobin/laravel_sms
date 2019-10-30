angular.module('callburnApp').factory('CampingService',
    function ($sce, Restangular) {
        return {
            createAudioFromText: createAudioFromText,
            createFileFromBase : createFileFromBase,
            campaignsBatchSend : campaignsBatchSend,
            makeAudioTemplate  : makeAudioTemplate,
            getAudioTemplates  : getAudioTemplates
        };
        /**
         * create audio from text
         */
        function createAudioFromText(data) {
            return Restangular.all('campaigns/create-audio-from-text').post(data);
        }

        /**
         * create file from base
         * @param params
         * @returns {*}
         */
        function createFileFromBase(params) {
            return Restangular.all('campaigns/create-file-from-base64').post(params);
        }

        /**
         * return camping data
         * @param params
         * @returns {*}
         */
        function campaignsBatchSend(params) {
            return Restangular.all('campaigns/batch-send').post(params);
        }

        /**
         * make audio templates
         * @param params
         * @returns {*}
         */
        function makeAudioTemplate(params) {
            return Restangular.all('audio-files/make-audio-template').post(params);
        }

        /**
         * get audio templates
         * @returns {*}
         */
        function getAudioTemplates() {
            return Restangular.one('audio-files/audio-templates').get();
        }
    });