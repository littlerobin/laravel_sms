module.exports=function ($stateProvider) {
    $stateProvider
        .state('campaign', {
            url: '/campaign',
            templateUrl: '/app/engines/campaign/main.html',
            controller: function ($state) {
                if ($state.current.name == 'campaign') {
                    $state.go('campaign.compose');
                }
            }
        })
        .state('campaign.compose', {
            url: '/compose',
            templateUrl: '/app/engines/campaign/compose/views/index.html',
            controller: 'ComposeController',
            params: {audioFile: null},
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                editingCampaign: function () {
                    return null;
                },
                reusingCampaign: function () {
                    return null;
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/app/engines/campaign/compose/services/CampingService.js',
                            '/app/engines/campaign/compose/controllers/ComposeController.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep1Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep2Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep3Controller.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateReplay.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateTransfer.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateCallback.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateDoNotCall.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeConfirmInteractions.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeChangeRepeatCount.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.batch', {
            url: '/batch',
            templateUrl: '/app/engines/campaign/batch/views/index.html',
            controller: 'BatchesController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/batch/controllers/BatchesController.js',
                            '/app/engines/campaign/batch/services/BatchesService.js',
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/assets/callburn/js/records/Fr.voice.js',
                            '/assets/callburn/js/records/libmp3lame.min.js',
                            '/assets/callburn/js/records/recorder.js'
                        ]
                    });
                }]
            }
        })
        .state('campaign.compose-from-contacts', {
            url: '/compose-from-contacts/:contact_ids',
            templateUrl: '/app/engines/campaign/compose/views/index.html',
            controller: 'ComposeController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                editingCampaign: function () {
                    return null;
                },
                reusingCampaign: function () {
                    return null;
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/app/engines/campaign/compose/controllers/ComposeController.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep1Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep2Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep3Controller.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateReplay.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateTransfer.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateCallback.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateDoNotCall.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeConfirmInteractions.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeChangeRepeatCount.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.compose-from-groups', {
            url: '/compose-from-groups/:group_ids',
            templateUrl: '/app/engines/campaign/compose/views/index.html',
            controller: 'ComposeController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                editingCampaign: function () {
                    return null;
                },
                reusingCampaign: function () {
                    return null;
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/app/engines/campaign/compose/controllers/ComposeController.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep1Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep2Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep3Controller.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateReplay.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateTransfer.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateCallback.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateDoNotCall.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeConfirmInteractions.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeChangeRepeatCount.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.reuse-campaign', {
            url: '/reuse/:reusing_source/:campaign_id',
            templateUrl: '/app/engines/campaign/compose/views/index.html',
            controller: 'ComposeController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                editingCampaign: function () {
                    return null;
                },
                reusingCampaign: function (Restangular, $stateParams) {
                    return Restangular.one('campaigns/show-campaign', $stateParams.campaign_id).get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/app/engines/campaign/compose/controllers/ComposeController.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep1Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep2Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep3Controller.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateReplay.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateTransfer.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateCallback.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateDoNotCall.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeConfirmInteractions.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeChangeRepeatCount.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.compose-from-phonenumbers', {
            url: '/compose-from-phonenumbers/:phonenumbers',
            templateUrl: '/app/engines/campaign/compose/views/index.html',
            controller: 'ComposeController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                editingCampaign: function () {
                    return null;
                },
                reusingCampaign: function (Restangular, $stateParams) {
                    return null;
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/app/engines/campaign/compose/controllers/ComposeController.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep1Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep2Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep3Controller.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateReplay.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateTransfer.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateCallback.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateDoNotCall.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeConfirmInteractions.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeChangeRepeatCount.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.edit', {
            url: '/edit/:campaign_id',
            templateUrl: '/app/engines/campaign/compose/views/index.html',
            controller: 'ComposeController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get();
                },
                editingCampaign: function (Restangular, $stateParams) {
                    return Restangular.one('campaigns/show-campaign', $stateParams.campaign_id).get();
                },
                reusingCampaign: function () {
                    return null;
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/compose/services/CampaignComposeService.js',
                            '/app/engines/campaign/compose/controllers/ComposeController.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep1Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep2Controller.js',
                            '/app/engines/campaign/compose/controllers/ComposeStep3Controller.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateReplay.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateTransfer.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateCallback.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeActivateDoNotCall.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeConfirmInteractions.js',
                            '/app/engines/campaign/compose/controllers/modals/ComposeChangeRepeatCount.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.overview', {
            url: '/overview/:status?',
            templateUrl: '/app/engines/campaign/overview/views/index.html',
            controller: 'OverviewController',
            resolve: {
                campaigns: function (Restangular, $stateParams) {
                    return Restangular.one('campaigns/index-campaigns').get($stateParams);
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/overview/controllers/OverviewController.js',
                            '/assets/callburn/js/records/Fr.voice.js',
                            '/assets/callburn/js/records/libmp3lame.min.js',
                            '/assets/callburn/js/records/recorder.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.statistics', {
            url: '/statistics/:campaign_batch?/:action?/:status?/:is_multiple?',
            templateUrl: '/app/engines/campaign/statistics/views/index.html',
            controller: 'StatisticsController',
            params: {repeats_grouping_string: null},
            resolve: {
                phonenumbers: function (Restangular, $stateParams) {
                    return Restangular.one('campaigns/show-campaign-numbers').get($stateParams);
                },
                repeats: function (Restangular, $stateParams) {
                    return Restangular.one('campaigns/get-repeats').get({repeats_grouping_string: $stateParams.repeats_grouping_string});
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/statistics/controllers/StatisticsController.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.templates', {
            url: '/templates',
            templateUrl: '/app/engines/campaign/template/views/index.html',
            controller: 'TemplatesController',
            resolve: {
                audioFiles: function (Restangular) {
                    return Restangular.one('audio-files/audio-templates').get({page: 0});
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/template/controllers/TemplatesController.js',
                        ]
                    });
                }]
            }
        })
        .state('campaign.addtemplate', {
            url: '/add-templates',
            templateUrl: '/app/engines/campaign/add-template/views/index.html',
            controller: 'AddAudioTemplateController',
            resolve: {
                ttsLanguages: function (Restangular) {
                    return Restangular.one('data/tts-languages').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/campaign/add-template/controllers/AddAudioTemplateController.js',
                        ]
                    });
                }]
            }
        })
};