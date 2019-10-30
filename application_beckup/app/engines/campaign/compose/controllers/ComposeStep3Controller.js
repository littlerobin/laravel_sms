angular.module('callburnApp').controller('ComposeStep3Controller',
    ['$scope', '$rootScope', 'ModalService', 'Restangular', 'notify', 'FileUploader',
        'CampaignComposeService', 'CallBournModal', 'CampingService', '$timeout', '$sce',
        function ($scope, $rootScope, ModalService, Restangular, notify, FileUploader,
                  CampaignComposeService, CallBournModal, CampingService, $timeout, $sce) {

            CampaignComposeService.ttsLanguages.forEach(function (item) {
                item.selectView = item.languageName + '-' + item.ttsEngine;
            });
            CampaignComposeService.audioTemplates.forEach(function (item) {
                item.selectView = item.orig_filename + ' - ' + item.length;
            });
            $scope.CampaignComposeService = CampaignComposeService;
            $scope.replayDigit = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
            $scope.transferDigit = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
            $scope.callbackDigit = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
            $scope.doNotCallDigit = {showNumbersSelect: false, onOff: 'off', modalStep: -1, checkboxChecked: false};
            $scope.voiceHuman = {onOff: 'off', checked: true};

            //CampaignComposeService.ttsLanguages = ttsLanguages.resource.languages;
            $scope.ttsCallbackData = {};
            $scope.uploadingCallbackAudioName = '';
            $scope.tempAudioTemplateModel = {};
            $scope.tempNewCreatedModel = {};
            $scope.liveTransferNumbers = [];
            $scope.doNotCallAudioFiles = [];
            $scope.finalStepData = {};
            $scope.campaignData = {};
            $scope.showSaveTemlate = true;
            $scope.campaignData.caller_id = $rootScope.currentUser.numbers[0].phone_number;
            $scope.callbackAudioFiles=[];




            //CampaignComposeService.audioTemplates = audioFiles.resource.files;

            $scope.showInteractionModal = function () {
                CallBournModal.open({
                    scope: {
                        CampaignComposeService: CampaignComposeService
                    },
                    templateUrl: "/app/modals/camping-batch/activate-replay.html",
                }, function (scope) {
                    /**
                     * activated replay digit
                     */
                    scope.replyDigitActivated = function () {
                        if (!CampaignComposeService.replayDigit.checkboxChecked) {
                            return;
                        }
                        $scope.replayDigit.modalStep = -1;
                        $scope.replayDigit.onOff = 'on';
                        CallBournModal.close();
                    };

                });
            };
            /**
             *
             */
            $scope.showBatchesChangeMessageDeliverySpeedModal = function () {
                CallBournModal.open({
                    scope: {campaignData: $scope.campaignData},
                    templateUrl: "/app/modals/camping-batch/change-message-delivery-speed.html",
                }, function (scope) {
                    /**
                     * select sending time
                     * @param data
                     */
                    scope.maxSpeedSelect={
                        0:{value:"",text:"Max Speed"},
                        1:{value:30,text:"Inside 30 minutes"},
                        2:{value:60,text:"Inside 60 minutes"},
                        3:{value:90,text:"Inside 90 minutes"},
                        4:{value:120,text:"Inside 120 minutes"}
                    }
                    scope.selectSendingTime = function (data) {
                        if (data.sending_time == "") {
                            $scope.finalStepData.sendingTime = data.sending_time;
                        } else {
                            $scope.finalStepData.sendingTime = data.sending_time;
                        }

                    }
                });
            }
            /**
             * Voice message
             */
            $scope.showVoiceMessageModal = function () {
                CallBournModal.open({
                    scope: {CampaignComposeService: CampaignComposeService},
                    templateUrl: "/app/modals/camping-batch/voice-message-sent.html",
                }, function (scope) {
                    /**
                     * close modal and  redirect to $state camping .overviw
                     */

                    scope.goToOverview = function () {
                        CallBournModal.close();
                        $state.go('campaign.overview');
                    };
                    /**
                     * close modal
                     */

                    scope.dismissModal = function () {
                        CallBournModal.close();
                    }
                });
            }

            /**
             * show activate compose activate call back modal
             */
            $scope.showComposeActivateCallbackModal = function () {
                CallBournModal.open({
                    scope: {
                        CampaignComposeService: CampaignComposeService,
                        callbackTtsData: $scope.ttsCallbackData,
                        createAudioFromTextForCallback: $scope.createAudioFromTextForCallback,
                        selectCallbackNewCreatedAudioFile: $scope.selectCallbackNewCreatedAudioFile,
                        uploadingCallbackAudioName: $scope.uploadingCallbackAudioName,
                        callbackAudioTemplateSelected: $scope.callbackAudioTemplateSelected,
                        tempAudioTemplateModel: $scope.tempAudioTemplateModel,
                        tempNewCreatedModel: $scope.tempNewCreatedModel,
                        callbackAudioFiles: $scope.callbackAudioFiles
                    },
                    templateUrl: "/app/modals/camping-batch/activate-callback.html"
                }, function (scope) {
                    /**
                     * activate callback digit
                     */
                    scope.activateCallbackDigit = function () {
                        CampaignComposeService.callbackDigit.onOff = "on";
                        CallBournModal.close();
                    };
                    /**
                     * file uloader instance
                     */
                    scope.callbackDigitFileUpload = new FileUploader({
                        url: 'campaigns/upload-audio-file',
                        alias: 'file',
                        autoUpload: true
                    });

                    scope.callbackDigitFileUpload.onAfterAddingFile = function (item) {

                        scope.uploadingCallbackAudioName = item.file.name;
                    };
                    /**
                     * return success data
                     * @param item
                     * @param response
                     * @param status
                     * @param headers
                     */
                    scope.callbackDigitFileUpload.onSuccessItem = function (item, response, status, headers) {
                        $rootScope.stopLoader();
                        if (response.resource.error.no == 0) {
                            scope.uploadingCallbackAudioName = false;
                            var audioData = {
                                source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + response.resource.file._id),
                                file: response.resource.file
                            };

                            $scope.callbackAudioFiles.push(audioData);
                            $scope.callbackAudioFiles.forEach(function (item) {
                                item.viewSelect=item.file.orig_filename + ' - ' + item.file.length
                            });
                        } else {
                            alert(response.resource.error.text);
                        }
                    };
                    /**
                     * file uploaing error action
                     * @param item
                     * @param response
                     * @param status
                     * @param headers
                     */
                    scope.callbackDigitFileUpload.onErrorItem = function (item, response, status, headers) {
                        $scope.errors = response;
                        $rootScope.stopLoader();
                    }
                    /**
                     * start callback upload
                     */
                    scope.startCallbackUpload = function () {
                        $rootScope.startLoader();
                        $scope.callbackDigitFileUpload.uploadAll();
                    }
                    /**
                     * open file select
                     */
                    scope.openCallbackFileSelect = function () {
                        $timeout(function () {
                            angular.element('#campaignCallbackFileInput').trigger('click');
                        }, 100);
                    }
                    scope.selectCallbackNewCreatedAudioFile = function(file){
                        $scope.newCallbackSelectedAudioFile = file;
                        $scope.showNewCreatedCallbacksDropDown = false;
                        $scope.campaignData.callback_voice_file_id = $scope.newCallbackSelectedAudioFile._id;
                    }
                });
            };

            /**
             * Open modal for activating transfer interaction.
             */
            $scope.showTransferModal = function () {
                CallBournModal.open({
                    scope: {
                        CampaignComposeService: CampaignComposeService,
                        currentUser: $rootScope.currentUser,
                        liveTransferNumbers: []
                    },
                    templateUrl: "/app/modals/camping-batch/activate-transfer.html",
                }, function (scope) {
                    if (CampaignComposeService.editingCampaign && CampaignComposeService.editingCampaign.transfer_option) {
                        $scope.liveTransferNumbers = CampaignComposeService.editingCampaign.transfer_option.split();
                    } else {
                        $scope.liveTransferNumbers = [];
                    }
                    /**
                     * activate transfer
                     */
                    scope.activateTransferDigit = function () {
                        if (!CampaignComposeService.transferDigit.checkboxChecked) {
                            return;
                        }
                        $scope.transferDigit.onOff = "on";
                        CampaignComposeService.campaignData.transfer_options = scope.liveTransferNumbers.join();
                        CallBournModal.close();
                    };
                    /**
                     * add remove transsef number
                     * @param number
                     * @param tariffId
                     */
                    scope.addRemoveTransfer = function (number, tariffId) {
                        var index = scope.liveTransferNumbers.indexOf(number);
                        if (index > -1) {
                            scope.liveTransferNumbers.splice(index, 1);
                        } else {
                            scope.liveTransferNumbers.push(number);
                        }
                    }
                });
            }

            /**
             * Open modal for activating callback interaction.
             */
            $scope.showCallbackModal = function () {
                CallBournModal.open({
                    scope: {},
                    templateUrl: "/app/modals/camping-batch/activate-callback.html"
                }, function (scope) {
                    scope.testFunction = function () {
                        alert("ok");
                    }
                });
            }

            /**
             * Open modal for activating do not call interaction.
             */
            $scope.showDoNotCallModal = function () {
                CallBournModal.open({
                    scope: {
                        openCampaignVoiceFileSelect: $scope.openCampaignVoiceFileSelect,
                        CampaignComposeService: CampaignComposeService,
                        donotcallNewCreatedTemp: $scope.donotcallNewCreatedTemp,
                        doNotCallAudioFiles: $scope.doNotCallAudioFiles
                    },
                    templateUrl: "/app/modals/camping-batch/activate-do-not-call.html",
                }, function (scope) {
                    /**
                     * cretae audio from text
                     */
                    scope.createAudioFromTextForDoNotCall = function () {
                        $rootScope.startLoader();
                        CampingService.createAudioFromText($scope.doNotCallTtsData).then(function (data) {
                            $rootScope.stopLoader();
                            if (data.resource.error.no == 0) {
                                $scope.doNotCallTtsData = {language: null};
                                var audioData = {
                                    source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + data.resource.file._id),
                                    file: data.resource.file
                                }
                                $scope.doNotCallAudioFiles.push(audioData);

                            } else {
                                $scope.voiceTtsError = data.resource.error.text;
                            }
                        });
                    };
                    /**
                     * open camping file select
                     */
                    scope.openCampaignVoiceFileSelect = function () {
                        $timeout(function () {
                            angular.element('#campaignDoNotCallFileInput').trigger('click');
                        }, 100);
                    };
                    scope.newDoNotCallSelectedAudioFile = {};
                    scope.showNewCreatedDoNotCallsDropDown = false;
                    scope.isDoNotCallNewCreatedFilePlaying = false;
                    /**
                     * create new audio file
                     * @param file
                     */
                    scope.selectDoNotCallNewCreatedAudioFile = function (file) {
                        scope.newDoNotCallSelectedAudioFile = file;
                        scope.showNewCreatedDoNotCallsDropDown = false;
                    };
                    /**
                     * save new created file
                     */
                    scope.saveNewCreatedAsDoNotCall = function () {
                        scope.campaignData.do_not_call_voice_file_id = scope.newDoNotCallSelectedAudioFile._id;
                    };
                    /**
                     * pasue or play new created file
                     * @param action
                     */
                    scope.playPauseDoNotCallNewCreatedAudio = function (action) {

                        if (!scope.newDoNotCallSelectedAudioFile._id) {
                            return;
                        }
                        var audio = document.getElementById('doNotCallCreatedAudioFile');
                        if (action == 'play') {
                            audio.play();
                            scope.isDoNotCallNewCreatedFilePlaying = true;
                            audio.addEventListener('ended', function () {
                                scope.isDoNotCallNewCreatedFilePlaying = false;
                                scope.$apply();
                            }, false);
                        } else {
                            audio.pause();
                            scope.isDoNotCallNewCreatedFilePlaying = false;
                        }
                    };
                    /**
                     * activate dontcall digit activate
                     */
                    scope.donotcallDigitActivated = function () {
                        if (!CampaignComposeService.doNotCallDigit.checkboxChecked) {
                            return;
                        }
                        $scope.doNotCallDigit.modalStep = -1;
                        $scope.doNotCallDigit.onOff = 'on';
                        CallBournModal.close();
                    }


                    /**
                     * return file upload instance
                     */
                    scope.doNotCallDigitFileUpload = new FileUploader({
                        url: 'campaigns/upload-audio-file',
                        alias: 'file',
                        autoUpload: true
                    });
                    /**
                     * uploading file
                     * @param item
                     */
                    scope.doNotCallDigitFileUpload.onAfterAddingFile = function (item) {
                        scope.uploadingDoNotCallAudioName = item.file.name;
                    }
                    /**
                     * file success uploading
                     * @param item
                     * @param response
                     * @param status
                     * @param headers
                     */
                    scope.doNotCallDigitFileUpload.onSuccessItem = function (item, response, status, headers) {
                        $rootScope.stopLoader();
                        if (response.resource.error.no == 0) {
                            scope.uploadingDoNotCallAudioName = false;
                            var audioData = {
                                source: $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + response.resource.file._id),
                                file: response.resource.file
                            }
                            $scope.doNotCallAudioFiles.push(audioData);
                            $scope.doNotCallAudioFiles.forEach(function (item) {
                                item.viewSelect=item.file.orig_filename + ' - ' + item.file.length
                            });
                        } else {
                            alert(response.resource.error.text);
                        }
                    };
                    /**
                     * on file uploading error
                     * @param item
                     * @param response
                     * @param status
                     * @param headers
                     */
                    scope.doNotCallDigitFileUpload.onErrorItem = function (item, response, status, headers) {
                        $scope.errors = response;
                        $rootScope.stopLoader();
                    }
                    /**
                     * start callback upload
                     */
                    scope.startCallbackUpload = function () {
                        $rootScope.startLoader();
                        scope.doNotCallDigitFileUpload.uploadAll();
                    }
                });
            }

            /**
             * Open modal for changing repeats count
             */
            $scope.showChangeRepeatsCountModal = function () {
                CallBournModal.open({
                    scope: {CampaignComposeService: CampaignComposeService,
                        repeatDevilery:{
                        0:{value:"once",text:"Deliver once"},
                        1:{value:"custom",text:"Custom"}
                    }},
                    templateUrl: "/app/modals/camping-batch/change-repeat-count.html",
                }, function (scope) {
                    scope.saveRepeat = function () {
                        console.log(scope.repeatDevilery);
                        if (scope.repeatSource == 'once' || !CampaignComposeService.campaignData.remaining_repeats || !CampaignComposeService.campaignData.repeat_days_interval) {
                            CampaignComposeService.campaignData.remaining_repeats = null;
                            CampaignComposeService.campaignData.repeat_days_interval = null;
                        }
                        CallBournModal.close();
                    }
                    scope.discardRepeat = function () {
                        CampaignComposeService.campaignData.remaining_repeats = null;
                        CampaignComposeService.campaignData.repeat_days_interval = null;
                        CallBournModal.close();
                    }
                });
            }
            /**
             * caller id modal inject
             */
            $scope.showCallerIdModal = function () {
                CallBournModal.open({
                    scope: {
                        currentUser: $rootScope.currentUser,
                        selectedCallerId: $scope.campaignData.caller_id
                    },
                    templateUrl: "/app/modals/camping-batch/change-caller-id.html"
                }, function (scope) {
                    scope.callerIdSelected = function (number) {
                        scope.selectedCallerId = number;

                    };
                    scope.changeCallerId = function () {
                        $scope.campaignData.caller_id = scope.selectedCallerId;
                        CallBournModal.close();
                    }
                });
            }

            /**
             * shedul modal
             */
            $scope.showSheduleModal = function () {
                CallBournModal.open({
                    scope: {},
                    templateUrl: "/app/modals/send_schedule/schedule_modal.html"
                }, function (scope) {

                });
            }
            /**
             * save as template
             */
            $scope.saveAsTemplate = function () {
                if (!CampaignComposeService.finalStepData.voiceFile._id) {
                    return;
                }
                $rootScope.startLoader();
                CampingService.makeAudioTemplate({id: CampaignComposeService.finalStepData.voiceFile._id}).then(function (data) {
                    if (data.resource.error.no == 0) {
                        $scope.showSaveTemlate = false;
                        $rootScope.stopLoader();
                    }
                })
            }
            /**
             * open send preview modal
             */
            $scope.showSendPreviewModal = function () {
                CallBournModal.open({
                    scope: {campaignData: $scope.campaignData},
                    templateUrl: "/app/modals/camping-batch/send-preview.html"
                }, function (scope) {
                    console.log($scope.campaignData);
                    scope.sendPreviewToYourPhone = function () {
                        CampaignComposeService.doValitation('preview');
                        CallBournModal.close();
                    };
                });
            }

            $scope.playPausePreviewAudio = function (action) {
                if (!CampaignComposeService.finalStepData.voiceFile._id) {
                    return;
                }

                var audio = document.getElementById('compose_step1_play_icon');

                if (action == 'play') {
                    audio.play();
                    $scope.isComposePreviewPlaying = true;
                    audio.addEventListener('ended', function () {
                        $scope.isComposePreviewPlaying = false;
                        $scope.$apply();
                    }, false);
                } else {
                    audio.pause();
                    $scope.isComposePreviewPlaying = false;
                }
            };

            /**
             * Open modal for activating replay interaction.
             */
            /*$scope.showReplayModal = function(){
             $rootScope.showBlurEffect = true;
             ModalService.showModal({
             templateUrl: "/app/engines/campaign/compose/views/modals/activate-replay.html",
             controller: "ComposeActivateReplay"
             }).then(function(modal) {
             modal.close.then(function(result) {
             $rootScope.showBlurEffect = false;
             });
             });
             }

             /!**
             * Open modal for activating transfer interaction.
             *!/
             $scope.showTransferModal = function(){
             $rootScope.showBlurEffect = true;
             ModalService.showModal({
             templateUrl: "/app/engines/campaign/compose/views/modals/activate-transfer.html",
             controller: "ComposeActivateTransfer"
             }).then(function(modal) {
             modal.close.then(function(result) {
             $rootScope.showBlurEffect = false;
             });
             });
             }

             /!**
             * Open modal for activating callback interaction.
             *!/
             $scope.showCallbackModal = function(){
             $rootScope.showBlurEffect = true;
             ModalService.showModal({
             templateUrl: "/app/engines/campaign/compose/views/modals/activate-callback.html",
             controller: "ComposeActivateCallback",
             }).then(function(modal) {
             modal.close.then(function(result) {
             $rootScope.showBlurEffect = false;
             });
             });
             }

             /!**
             * Open modal for activating do not call interaction.
             *!/
             $scope.showDoNotCallModal = function(){
             $rootScope.showBlurEffect = true;
             ModalService.showModal({
             templateUrl: "/app/engines/campaign/compose/views/modals/activate-do-not-call.html",
             controller: "ComposeActivateDoNotCall"
             }).then(function(modal) {
             modal.close.then(function(result) {
             $rootScope.showBlurEffect = false;
             });
             });
             }

             /!**
             * Open modal for changing repeats count
             *!/
             $scope.showChangeRepeatsCountModal = function(){
             $rootScope.showBlurEffect = true;
             ModalService.showModal({
             templateUrl: "/app/engines/campaign/compose/views/modals/change-repeat-count.html",
             controller: "ComposeChangeRepeatCount"
             }).then(function(modal) {
             modal.close.then(function(result) {
             $rootScope.showBlurEffect = false;
             });
             });
             }*/

        }]);