inspinia.controller('campaignViewApplyCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element','$filter' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element,$filter){

    $scope.loggedUser = $scope.$parent.loggedUser; // getUser after page reload

    $scope.getCampaignInformation = function (identity) {
        console.log($scope.loggedUser)
        $http.get('/get-campaign/' + identity).success(function (res) {
            $scope.campaignDetail = res.data;
            console.log($scope.campaignDetail);
            $scope.campaignSocial = $scope.campaignDetail.social_network.toLowerCase();
        });
        $http.get('/show-influencer-campaign-status/' + identity).success(function (res) {
            $scope.campaignStatusInf = res.data.status;
            if($scope.campaignStatusInf == 'invited'){
                $http.get('/show-invitation/' + $scope.loggedUser.identity).success(function (res) {
                    $scope.invitedCampaignsInf = res.data;
                    $scope.getInvitedCampaign = {};
                    angular.forEach($scope.invitedCampaignsInf,function(campaign){
                        $scope.getInvitedCampaign[campaign.campaign_id] = $filter('filter')( $scope.invitedCampaignsInf, campaign.identity);
                    });
                    console.log($scope.getInvitedCampaign);

                    console.log($scope.getInvitedCampaign[identity])
                });
            }
        });
    };

    $scope.getCampaignInformation($state.params.identity);

    $scope.applyInfluencer = function(){
        $http.get('/apply-to-campaign/' + $state.params.identity).success(function (res) {
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

    $scope.acceptInvitation = function(){
        console.log($scope.getInvitedCampaign);
        $http.get('/influencer-accept-invited/' + $scope.getInvitedCampaign[$state.params.identity][0].identity ).success(function (res) {
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

    $scope.rejectInvitation = function(){
        $http.get('/influencer-reject-invited/' + $scope.getInvitedCampaign[$state.params.identity][0].identity ).success(function (res) {
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
}]);
