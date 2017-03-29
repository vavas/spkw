inspinia.controller('campaignViewCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element){

    $scope.loggedUser = $scope.$parent.loggedUser; // getUser after page reload

    $scope.getCampaignInformation = function (identity) {
        console.log($scope.loggedUser)
        $http.get('/get-campaign/' + identity).success(function (res) {
            $scope.campaignDetail = res.data;
            console.log($scope.campaignDetail);
            $scope.campaignSocial = $scope.campaignDetail.social_network.toLowerCase();
            $scope.campaignInfluencers = $scope.campaignDetail.influencers;
            console.log($scope.campaignInfluencers)
        });
    };

    $scope.acceptToCampaign = function(invitationId){
        $http.get('/brand-accept-application/' + invitationId).success(function (res) {
        }).then(function(res){
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

    $scope.rejectToCampaign = function(invitationId){
        $http.get('/brand-reject-application/' + invitationId).success(function (res) {
            console.log(res)
        }).then(function(res){
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


    $scope.getCampaignInformation($state.params.identity);

    $scope.tab = 1;

    $scope.setTab = function(newTab){
        $scope.tab = newTab;
    };

    $scope.isSet = function(tabNum){
        return $scope.tab === tabNum;
    };
}]);

