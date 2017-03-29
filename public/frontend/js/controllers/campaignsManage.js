inspinia.controller('campaignsManageCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element','$filter' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element,$filter){

    $scope.loggedUser = $scope.$parent.loggedUser; // getUser after page reload

    $scope.getCampaignManageInformation = function (identity) {
        console.log($scope.loggedUser);
        $http.get('/brand-view-campaigns/').success(function (res) {
            $scope.campaignDetailManange = res.data;
            console.log($scope.campaignDetailManange);
            $http.get('/brand-posts-list/').success(function (res) {
                $scope.brandPosts = res.data;
                console.log($scope.brandPosts);
                $scope.getCampaignPosts();
            });
        });
    };

    $scope.getCampaignManageInformation($state.params.identity);

    $scope.getCampaignPosts = function(){
        $scope.posts = {};
        angular.forEach($scope.campaignDetailManange,function(campaign){
            $scope.posts[campaign.identity] = $filter('filter')( $scope.brandPosts, campaign.identity);
        });
        console.log($scope.posts);
    };

}]);

