angular.module('callburnApp').controller('OverviewController',
    ['$scope', '$rootScope', '$state', 'Restangular', '$stateParams', 'campaigns', '$timeout', 'FileUploader', '$sce',
        function ($scope, $rootScope, $state, Restangular, $stateParams, campaigns, $timeout, FileUploader, $sce) {

            $scope.goToNotification = $rootScope.goToNotification;
            $rootScope.currentPage = 'dashboard';
            $rootScope.currentActiveRoute = 'campaign';
            $scope.currentOrder = 'ASC';
            $scope.selectedCampaign = null;

            $scope.showInput = [];

            $rootScope.footerData = {
                first: '<span>You can export statistics into a file</span>' +
                '<span>Just use “export” functionality</span>',
                second: '<span>You have sent a total of</span>' +
                '<span>' + $rootScope.dashboardData.sent_messages + ' messages</span>',
                third: '<span>Scheduled Messages are usefull</span>' +
                '<span>In this moment you have got ' + $rootScope.dashboardData.scheduled_messages + ' scheduled messages</span>'
            }

            var getNewCampaigns = function (data) {
                Restangular.one('campaigns/index-campaigns').get(data).then(function (campaigns) {
                    updateCampaigns(campaigns);
                })
            }

            var updateCampaigns = function (data) {
                $scope.campaigns = data.resource.campaigns;
                $scope.campaignsPage = data.resource.page;
                $scope.pagesCount = Math.ceil(data.resource.campaigns_count / 7);
            }
            updateCampaigns(campaigns);

            if ($stateParams.status) {
                $scope.filterData = {status: $stateParams.status};
            } else {
                $scope.filterData = {status: 'all'};
            }

            $scope.filterCampaigns = function (status) {
                $scope.filterData.status = status || $scope.filterData.status;
                getNewCampaigns($scope.filterData);
            }

            $scope.changeOrder = function (field) {
                $scope.filterData.page = 0;
                if (field == $scope.filterData.order_field) {
                    $scope.currentOrder = ($scope.currentOrder == 'ASC') ? 'DESC' : 'ASC';
                }
                $scope.filterData.order_field = field;
                $scope.filterData.order = $scope.currentOrder;
                getNewCampaigns($scope.filterData);
            }

            $scope.changePage = function (page) {
                if (page < 0 || page > $scope.pagesCount - 1) {
                    return;
                }
                $scope.filterData.page = page;
                getNewCampaigns($scope.filterData);
            }


            $scope.removeCampaign = function (id) {
                $rootScope.startLoader();
                Restangular.one('campaigns/remove-campaign', id).remove().then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        getNewCampaigns({});
                    }
                })
            }

            $scope.changeSelectedCampaign = function (id) {
                $scope.selectedCampaign = id;
            }


            $scope.changeCampaignName = function (id, name) {
                $rootScope.startLoader();
                Restangular.all('campaigns/update-campaign-name').post({
                    'campaign_name': name,
                    'campaign_id': id
                }).then(function (data) {
                    $rootScope.stopLoader();
                    if (data.resource.error.no == 0) {
                        for (index in $scope.campaigns) {
                            if ($scope.campaigns[index]._id == id) {
                                $scope.campaigns[index].campaign_name = name;
                                $scope.showInput[id] = false;
                                break;
                            }
                        }
                    }
                })
            }

            $scope.exportCampaign = function () {
                if (!$scope.selectedCampaign) {
                    alert('No message selected');
                    return;
                }
                for (index in $scope.campaigns) {
                    if ($scope.campaigns[index]._id == $scope.selectedCampaign) {
                        var batchString = $scope.campaigns[index].unique_string_for_batch_grouping;
                        break;
                    }
                }
                if (!batchString) {
                    alert('No message selected');
                    return;
                }
                postData = {campaign_batch: batchString};
                window.location.href = '/phonenumbers/export-statistics?export_data=' + JSON.stringify(postData);
            }

            $scope.getSchedulationTooltipHtml = function (campaign) {
                var str = '';
                for (index in campaign.schedulations) {
                    str += campaign.schedulations[index].scheduled_date + ' - ' + campaign.schedulations[index].calls_limit + ' Receipent(s) <br>';
                }
                return str;
            }

            $scope.getAudioTooltipHtml = function (campaign) {
                if (!campaign) {
                    return;
                }
                //(apiUrl + '?key=' + $rootScope.currentUser.api_token + '&file_id=' + id
                var str = '<audio src="' + apiUrl + '?key=' + $rootScope.currentUser.api_token +
                    '&file_id=' + campaign.campaign_voice_file_id + '" controls style="display:none;" id="campaignAudio' +
                    campaign.campaign_voice_file_id + '"></audio>';
                str = str + '<img src="/assets/callburn/images/play.png" class="compose_method3_icons" onclick="playAudio(' + campaign.campaign_voice_file_id + ')" />&nbsp;&nbsp;Play <br><br>';
                str = str + '<img src="/assets/callburn/images/stop1.png" class="compose_method3_icons" onclick="pauseAudio(' + campaign.campaign_voice_file_id + ')" />&nbsp;&nbsp;Pause <br><br>';
                str = str + '<img src="/assets/callburn/images/fill-76.png" class="compose_method3_icons" (' + campaign.campaign_voice_file_id + ')" />&nbsp;&nbsp;Stop ';
                return str;
            }

            window.playAudio = function (id) {
                var audio = document.getElementById('campaignAudio' + id);
                audio.play();
            }

            window.pauseAudio = function (id) {
                var audio = document.getElementById('campaignAudio' + id);
                audio.pause();
            }

            $scope.getCampaignStatus = function (campaign) {
                switch (campaign.status) {
                    case 'dialing_completed':
                        return 'Sent';
                    case 'start':
                        return 'In Progress';
                    case 'saved':
                        return 'Saved as Draft';
                    case 'scheduled':
                        return 'Scheduled';
                    default:
                        return '';
                }
            }

            $scope.getSuccessOfCampaign = function (campaign) {
                var countOfSuccess = 0;
                for (index in campaign.batches) {
                    countOfSuccess += campaign.batches[index].success_phonenumbers[0] ? campaign.batches[index].success_phonenumbers[0].count : 0;
                }
                return countOfSuccess;
            }

            $scope.getTotalOfCampaign = function (campaign) {
                var countOftotal = 0;
                for (index in campaign.batches) {
                    countOftotal += campaign.batches[index].total_phonenumbers[0] ? campaign.batches[index].total_phonenumbers[0].count : 0;
                }
                return countOftotal;
            }

            $scope.getCostOfCampaign = function (campaign) {
                var cost = 0;
                for (index in campaign.batches) {
                    cost += campaign.batches[index].cost_phonenumbers[0] ? campaign.batches[index].cost_phonenumbers[0].sum : 0;
                }
                return cost;
            }

            $scope.reuseCampaign = function () {
                if (!$scope.selectedCampaign) {
                    alert('Select Message');
                    return;
                }
                for (index in $scope.campaigns) {
                    if ($scope.campaigns[index]._id == $scope.selectedCampaign) {
                        var currentCampaign = $scope.campaigns[index];
                        break;
                    }
                }
                if (currentCampaign.batches.length > 1) {
                    alert('Batch messages can not be reused');
                    return;
                }
                $state.go('campaign.reuse-campaign', {reusing_source: 'both', campaign_id: $scope.selectedCampaign});
            }


            $scope.reuseReceipents = function () {
                if (!$scope.selectedCampaign) {
                    alert('Select Message');
                    return;
                }
                for (index in $scope.campaigns) {
                    if ($scope.campaigns[index]._id == $scope.selectedCampaign) {
                        var currentCampaign = $scope.campaigns[index];
                        break;
                    }
                }
                if (currentCampaign.batches.length > 1) {
                    alert('Batch messages can not be reused');
                    return;
                }
                $state.go('campaign.reuse-campaign', {
                    reusing_source: 'receipents',
                    campaign_id: $scope.selectedCampaign
                });
            }

            $scope.showReuseMessageModal = false;
            $scope.reuseMessage = function () {
                if (!$scope.selectedCampaign) {
                    alert('Select Message');
                    return;
                }
                for (index in $scope.campaigns) {
                    if ($scope.campaigns[index]._id == $scope.selectedCampaign) {
                        var currentCampaign = $scope.campaigns[index];
                        break;
                    }
                }
                if (currentCampaign.batches.length > 1) {
                    alert('Batch messages can not be reused');
                    return;
                }
                $state.go('campaign.reuse-campaign', {reusing_source: 'message', campaign_id: $scope.selectedCampaign});
            }

            $scope.ifVoiceFileSame = function (campaign) {
                if (campaign.batches.length == 1) {
                    return true;
                }

                var voiceMessageids = null;
                for (index in campaign.batches) {
                    if (voiceMessageids == null) {
                        voiceMessageids = [];
                        voiceMessageids.push(campaign.batches[index].campaign_voice_file_id);
                    } else {
                        if (voiceMessageids.indexOf(campaign.batches[index].campaign_voice_file_id) == -1) {
                            return false;
                        }
                    }

                }
                return true;
            }

        }]);