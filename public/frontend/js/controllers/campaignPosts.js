inspinia.controller('campaignPostCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element','$filter', 'ngDialog',
    function($scope, $http, $state, $auth, notify, $utils, Upload, $element,$filter, ngDialog){

    $scope.loggedUser = $scope.$parent.loggedUser; // getUser after page reload

    $scope.getCampaignManageInformation = function (identity) {
        console.log($scope.loggedUser);
        $http.get('/get-campaign/'+ identity).success(function (res) {
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

    $scope.posts = {};

    $scope.getCampaignPosts = function(){

        angular.forEach($scope.campaignDetailManange,function(){
            $scope.posts.content = $filter('filter')( $scope.brandPosts, $state.params.identity);
        });
        angular.forEach($scope.posts.content,function(post){
            $http.get('/get-influencer/'+ post.influencer_id).success(function (res) {
                post.influencerInf = res.data;
                console.log(res.data)
            });
        });
        console.log($scope.posts);
        console.log($scope.campaignDetailManange)
    };



    $scope.acceptPost = function(postId){
        $http.post('/brand-accept-post/' + postId).success(function (res) {
            console.log(res)
        }).then(function(res){
            console.log(res)
            notify({
                message: 'Post approved',
                classes: 'alert-danger'
            });
        }, function(err){
            notify({
                message: err.data.errors.message,
                classes: 'alert-danger'
            });
        });
    };

    $scope.rejectPost = function (postId) {
        ngDialog.open({
            template: 'views/popUpRejectPost.html',
            className: 'ngdialog-theme-plain',
            data: {
                title: 'Reject Post'
            },
            preCloseCallback: function(event) {
                var rejectTextMessage = $scope.rejectTextModel;
                if (event == 'confirm'){
                    $http.post('/brand-reject-post/' + postId, {
                        reason: rejectTextMessage
                    }).then(function(res){
                        console.log(res)
                        notify({
                            message: 'Post rejected',
                            classes: 'alert-danger'
                        });
                    }, function(err){
                        notify({
                            message: err.data.errors.message,
                            classes: 'alert-danger'
                        });
                    });
                }
            }
        });
    };


    $scope.schedulePost = function (postId) {
        ngDialog.open({
            template: 'views/schedulePostPopUp.html',
            className: 'ngdialog-theme-plain',
            data: {
                title: 'Schedule Post',
                date: ''
            },
            controller: 'campaignPostCtrl',
            preCloseCallback: function(event) {
                var scheduleTime = angular.element(datetime).val()
                if (event == 'confirm'){
                    $http.post('/schedule-post/' + postId, {
                        publish_at: scheduleTime
                    }).then(function(res){
                        console.log(res)
                        notify({
                            message: res.data.message,
                            classes: 'alert-danger'
                        });
                    }, function(err){
                        notify({
                            message: err,
                            classes: 'alert-danger'
                        });
                    });
                }
            }
        });
    };

    $scope.tab = 1;

    $scope.setTab = function(newTab){
        $scope.tab = newTab;
    };

    $scope.isSet = function(tabNum){
        return $scope.tab === tabNum;
    };
}]);
