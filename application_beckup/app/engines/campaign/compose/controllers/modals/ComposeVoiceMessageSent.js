angular.module('callburnApp').controller('ComposeVoiceMessageSent',
    ['$scope', 'CampaignComposeService', '$state',
        function ($scope, CampaignComposeService, $state) {

            $scope.CampaignComposeService = CampaignComposeService;

            $scope.goToOverview = function () {
                close('success');
                $state.go('campaign.overview');
            };

            $scope.dismissModal = function () {
                close('success');
            }
        }]);