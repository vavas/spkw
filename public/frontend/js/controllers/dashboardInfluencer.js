inspinia.controller('dashboardInfluencerCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element){
    $scope.loggedUser = $scope.$parent.loggedUser;

    console.log($scope.loggedUser)

    $http.get('/show-influencer-campaign/' + $scope.loggedUser.identity).success(function (res) {
        $scope.influencersCampaigns = res.data;
    });

    $http.get('/show-invitation/' + $scope.loggedUser.identity).success(function (res) {
        $scope.influencersInvitedCampaigns = res.data;
    });

    $scope.acceptCampaign = function(invitationId){
        $http.get('/influencer-accept-invited/' + invitationId).success(function (res) {
            console.log(res)
        }).then(function(res){
            $http.get('/show-influencer-campaign/' + $scope.loggedUser.identity).success(function (res) {
                $scope.influencersCampaigns = res.data;
            });

            $http.get('/show-invitation/' + $scope.loggedUser.identity).success(function (res) {
                $scope.influencersInvitedCampaigns = res.data;
            });
            console.log(res)
            notify({
                message: res.data.data.message,
                classes: 'alert-danger'
            });
        }, function(err){
            notify({
                message: err.data.errors.message,
                classes: 'alert-danger'
            });
        });
    }

    $scope.rejectCampaign = function(invitationId){
        $http.get('/influencer-reject-invited/' + invitationId).success(function (res) {
            console.log(res)
        }).then(function(res){
            $http.get('/show-influencer-campaign/' + $scope.loggedUser.identity).success(function (res) {
                $scope.influencersCampaigns = res.data;
            });

            $http.get('/show-invitation/' + $scope.loggedUser.identity).success(function (res) {
                $scope.influencersInvitedCampaigns = res.data;
            });
            console.log(res)
            notify({
                message: res.data.data.message,
                classes: 'alert-danger'
            });
        }, function(err){
            notify({
                message: err.data.errors.message,
                classes: 'alert-danger'
            });
        });
    }

}]);