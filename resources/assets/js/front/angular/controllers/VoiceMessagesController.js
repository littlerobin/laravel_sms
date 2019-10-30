angular.module('frontCallburnApp').controller('VoiceMessagesController',
    ['$scope', '$rootScope', '$document',
    function ($scope, $rootScope, $document) {
        var path = "/laravel_assets/audios/" + window.location.href.split('/')[3] + "/";

        $scope.audios = [
            path + 'auth.mp3',
            path + 'notify.mp3',
            path + 'promote.mp3',
        ];

        $scope.audioIndex = 0;
        $scope.play = true;

        $scope.choiceAudio = function (index) {
            var audio = document.getElementById("audio-file");
            audio.pause();
            $scope.play = true;
            $scope.audioIndex = index;
        };

        $scope.playAudio = function () {
            var audio = document.getElementById("audio-file");
            audio.load();
            audio.play();
            audio.addEventListener("ended", function () {
                $scope.play = true;
                $scope.$apply();
            });
            $scope.play = !$scope.play
        };

        $scope.pauseAudio = function () {
            var audio = document.getElementById("audio-file");
            audio.load();
            audio.pause();
            $scope.play = !$scope.play
        }

}]);
