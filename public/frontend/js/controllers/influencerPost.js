inspinia.controller('influencerPostCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element){

    $scope.loggedUser = $scope.$parent.loggedUser; // getUser after page reload

    $scope.getCampaignInformation = function (identity) {
        console.log($scope.loggedUser)
        $http.get('/get-campaign/' + identity).success(function (res) {
            $scope.campaignDetail = res.data;
            console.log($scope.campaignDetail);
            $scope.campaignSocial = $scope.campaignDetail.social_network.toLowerCase();
            $scope.postValidation($scope.campaignDetail);
        });
    };

    console.log($state.params.identity)

    $scope.getCampaignInformation($state.params.identity);

    $scope.postItem = {};

    $scope.postItem.campaign_id = $state.params.identity;



    $scope.postValidation = function(campaignInf){
        $scope.campaignHashtag = campaignInf.hashtag.split(",");
        $scope.campaignMentions = campaignInf.mention.split(",");
        $scope.textRequiredHashtags = $scope.campaignHashtag;
        $scope.textRequiredMentions = $scope.campaignMentions;
    }

    $scope.textValidationPost = function(){
        angular.forEach($scope.campaignHashtag,function(hashTag) {
            if ($scope.postText.indexOf(hashTag) >= 0){
                var itemExist =  $scope.textRequiredHashtags.indexOf(hashTag);
                $scope.textRequiredHashtags.splice(itemExist, 1);
            }
        });
        angular.forEach($scope.campaignMentions,function(mention) {
            if ($scope.postText.toLowerCase().indexOf(mention) >= 0){
                var itemExist = $scope.textRequiredHashtags.indexOf(mention);
                $scope.textRequiredMentions.splice(itemExist, 1);
            }
        });
    };

    $scope.uploadImg = function(file){
        console.log(file);
        if (file){
            //$scope.postItem.image_url.$error.serverError = false;
            $scope.uploadingImg = true;
            Upload.upload({
                url: '/upload',
                data: {file: file}
            }).then(function (res) {
                $scope.postItem.image_url = res.data.data.url;
                $scope.uploadingImg = false;
                $scope.uploadedImg = true;
                console.log($scope.postItem)
            }, function (resp) {
                notify({
                    message: resp.data.errors.message,
                    classes: 'alert-danger'
                });
                $scope.uploadingImg = false;
                //console.log(resp.data.errors.message);
            }, function (evt) {
                $scope.progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                console.log($scope.progressPercentage);
            });
        }
    };
    $scope.uploadVideo = function(file){
        console.log(file);
        if (file){
            //$scope.postItem.image_url.$error.serverError = false;
            $scope.uploadingVideo = true;
            Upload.upload({
                url: '/upload',
                data: {file: file}
            }).then(function (res) {
                $scope.postItem.video_url = res.data.data.url;
                $scope.uploadingVideo = false;
                $scope.uploadedVideo = true;
                console.log($scope.postItem)
            }, function (resp) {
                console.log('Error status: ' + resp.status);
            }, function (evt) {
                $scope.progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                console.log($scope.progressPercentage);
            });
        }
    };

    $scope.createPost = function(){
        $scope.postItem.text = $scope.postText;
        $http.post('/create-post', $scope.postItem).success(function (res) {
            console.log(res)
            notify({
                message: 'Post created',
                classes: 'alert-danger'
            })
        }).error(function(res){
            notify({
                message: res.errors.message,
                classes: 'alert-danger'
            });
        })
    }

}]);
