inspinia.controller('influencerPostViewCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element){

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

    $scope.getPostInformation = function(identity){
        console.log(identity)
        $http.get('/influencer-post-data/' + identity).success(function (res) {
            $scope.postData = res.data;
            $scope.postStatus = $scope.postData.status;
            console.log($scope.postData)
            if ($scope.postStatus == 'rejected'){
                console.log($scope.postStatus)
                $scope.postText = $scope.postData.text;
                //$scope.postItem = $scope.postData;
                $scope.postItem.image_url = $scope.postData.image_url;
                $scope.uploadedImg = true;
            }
        });
    };

    console.log($state.params.identity)
    $scope.getPostInformation($state.params.identity);

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
                console.log('Error status: ' + resp.status);
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

    $scope.resubmitPost = function(identity){

        $scope.postItem.text = $scope.postData.text;
        console.log($scope.postItem);
        $http.post('/resubmit-post/' + identity, $scope.postItem).success(function (res) {
            console.log(res)
            notify({
                message: 'Post resubmited',
                classes: 'alert-danger'
            });
        })
    }

}]);
