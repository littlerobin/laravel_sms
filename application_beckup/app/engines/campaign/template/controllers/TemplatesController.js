angular.module('callburnApp').controller('TemplatesController',
    ['$scope', '$rootScope', '$state', 'Restangular', '$stateParams', 'audioFiles', '$sce',
        function ($scope, $rootScope, $state, Restangular, $stateParams, audioFiles, $sce) {


            $rootScope.currentPage = 'dashboard';


            $scope.goToNotification = $rootScope.goToNotification;

            $scope.filterData = {type: 'ALL'};
            $scope.checkedTemplates = {};
            $scope.isAllChecked = false;
            $scope.showFilesInput = [];

            $scope.checkUncheckAll = function () {
                $scope.isAllChecked = !$scope.isAllChecked;
                for (index in $scope.templates) {
                    $scope.checkedTemplates[$scope.templates[index]._id] = $scope.isAllChecked;
                }
            }

            $scope.chanegActiveTab = function (tab) {
                if ($scope.filterData.type == tab) {
                    return;
                }
                $scope.filterData.type = tab;
                Restangular.one('audio-files/audio-templates').get($scope.filterData).then(function (data) {
                    updateTemplates(data);
                });
            }

            var updateTemplates = function (templates) {
                $scope.templates = templates.resource.files;
                $scope.templatesPage = templates.resource.page;
                $scope.templatesPagesCount = Math.ceil(templates.resource.count / 10);

                $rootScope.footerData = {
                    first: '<span></span>' +
                    '<span></span>',
                    second: '<span>You have got a total of</span>' +
                    '<span>' + templates.resource.count + ' templates</span>',
                    third: '<span></span>' +
                    '<span></span>'
                }
            }

            updateTemplates(audioFiles);

            $scope.changeTemplatesPage = function (page) {
                if (page < 0 || page >= $scope.templatesPagesCount) {
                    return;
                }
                var postData = $scope.filterData;
                postData.page = page;
                Restangular.one('audio-files/audio-templates').get(postData).then(function (data) {
                    updateTemplates(data);
                });
            }

            $scope.deleteAudioTemplates = function () {
                Restangular.all('audio-files/remove-audio-templates').post({ids: $scope.checkedTemplates}).then(function (data) {
                    $scope.changeTemplatesPage($scope.templatesPage);
                });
            }

            $scope.sendMessageAgain = function () {
                var audioTemplateId = null;
                for (index in $scope.checkedTemplates) {
                    if ($scope.checkedTemplates[index]) {
                        audioTemplateId = index;
                        break;
                    }
                }
                var audioFile = false;
                for (index in $scope.templates) {
                    if ($scope.templates[index]._id == audioTemplateId) {
                        audioFile = $scope.templates[index];
                        break;
                    }
                }
                if (!audioFile) {
                    return;
                }
                $state.go('campaign.compose', {audioFile: audioFile});
            }


            $scope.changeFileName = function (id, name) {
                $rootScope.startLoader();
                Restangular.one('audio-files/update-file-name', id).put({name: name}).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        for (index in $scope.templates) {
                            if ($scope.templates[index]._id == id) {
                                $scope.templates[index].orig_filename = name;
                                $scope.showFilesInput[id] = false;
                                break;
                            }
                        }
                    }
                })
            }

            $scope.isFilePlaying = false;
            $scope.$watch('checkedTemplates', function (newVal) {
                $scope.isFilePlaying = false;
            }, 1)
            $scope.playPauseAudio = function (action) {
                var audioTemplateId = null;
                for (index in $scope.checkedTemplates) {
                    if ($scope.checkedTemplates[index]) {
                        audioTemplateId = index;
                        break;
                    }
                }
                if (!audioTemplateId) {
                    return;
                }
                var audio = document.getElementById('messageAudioFile');
                if (action == 'play') {
                    audio.play();
                    $scope.isFilePlaying = true;
                    audio.addEventListener('ended', function () {
                        $scope.isFilePlaying = false;
                        $scope.$apply();
                    }, false);
                } else {
                    audio.pause();
                    $scope.isFilePlaying = false;
                }
            }

            $scope.getAudioSource = function () {
                var audioTemplateId = null;
                for (index in $scope.checkedTemplates) {
                    if ($scope.checkedTemplates[index]) {
                        audioTemplateId = index;
                        break;
                    }
                }
                var audioFile = false;
                for (index in $scope.templates) {
                    if ($scope.templates[index]._id == audioTemplateId) {
                        audioFile = $scope.templates[index];
                        break;
                    }
                }
                if (!audioFile) {
                    return;
                }
                return $sce.trustAsResourceUrl(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + audioFile._id);
            }

            $scope.isOnlyOneSelected = function () {
                var count = 0;
                for (index in $scope.checkedTemplates) {
                    if ($scope.checkedTemplates[index]) {
                        count++;
                        if (count > 1) {
                            return false;
                        }
                    }
                }
                if (count == 1) {
                    return true;
                }
                return false;
            }
        }]);