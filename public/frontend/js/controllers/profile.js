inspinia.controller('profileCtrl', ['$scope' ,'$http', '$state', '$authService', 'notify', '$utils', 'Upload','$element' , function($scope, $http, $state, $auth, notify, $utils, Upload, $element){

    $scope.loggedUser = $scope.$parent.loggedUser; // getUser after page reload

    $http.get('/get-interest-list').then(function(res){
        $scope.interests = res.data.data;
    });

    $scope.profileMode = 'view';

    $http.get('/get-states-list/').then(function (res) {
        $scope.locations = res.data.data;
        //console.log($scope.locations)
        $scope.location_state = [];
        angular.forEach($scope.locations, function(key, value){
            $scope.location_state.push(key.state);
            //console.log(key.state)
        });
        //console.log($scope.location_state)
    });



    $scope.interests = $utils.interests;

    $scope.genders = $utils.genders;

    $scope.oldProfile = {};

    $scope.upload = function(file){
        if (file){
            $scope.editProfileForm.image_url.$error.serverError = false;
            $scope.uploading = true;
            Upload.upload({
                url: '/upload',
                data: {file: file}
            }).then(function (res) {
                $scope.loggedUser.image_url = res.data.data.url;
                $scope.uploading = false;
            }, function (resp) {
                console.log('Error status: ' + resp.status);
            }, function (evt) {
                $scope.progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                console.log($scope.progressPercentage);
            });
        }
    };


        $scope.getUserProfile = function (identity) {
            if ($scope.loggedUser.identity == identity) {
            $http.get('/user-profile/' + identity).success(function (res) {
                delete res.data.accessToken;
                angular.extend($scope.loggedUser, res.data);
                $scope.getUserFullInformation(identity, $scope.loggedUser.role)
            }).error(function (err) {
                console.log(err);
            });
            } else {
                $http.get('/user-profile/' + identity).success(function (res) {
                    $scope.profileView = true;
                    $scope.userShow = res.data;
                    //console.log($scope.userShow);
                    $scope.getUserFullInformation(identity, $scope.userShow.role)
                }).error(function (err) {
                    console.log(err);
                });
            }
        };

    $scope.getUserFullInformation = function(identity, role){
        $http.get('/get-'+role+'/' + identity).success(function (res) {
            //console.log(res);
            $scope.userSocialData = res.data;
        }).error(function (err) {
            console.log(err);
        });
    }

    $scope.getUserProfile($state.params.identity);

    $scope.editProfile = function(){
        $scope.oldProfile = angular.copy($scope.loggedUser);
        $scope.profileMode = 'edit';
    };

    $scope.cancelEditProfile = function(){
        $scope.loggedUser = angular.copy($scope.oldProfile);
        $scope.profileMode = 'view';
    };

    $scope.updateProfile = function(){
        $http.post('/edit-profile', $scope.loggedUser).success(function(res){
            delete res.data.accessToken;
            $scope.loggedUser = angular.extend(res.data);
            $scope.oldProfile = {};
            notify({
                message:'Profile Updated Successful!',
                classes: 'alert-info'
            });
            $scope.profileMode = 'view';
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                $scope.editProfileForm[field].$setValidity('serverError', false);
                $scope.editProfileForm[field].$error.errorMessage = message;
            });
        })
    };

    $scope.passwordChanging = false;

    $scope.changePassword = function(){
        $scope.passwordChanging = true;
    };

    $scope.updatePassword = function(){
        $http.post('/change-password', $scope.passwordData).success(function(res){
            $scope.passwordChanging = false;
            $scope.passwordData = {};
            notify({
                message:'Password Updated Successful!',
                classes: 'alert-info'
            });
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                $scope.changePasswordForm[field].$setValidity('serverError', false);
                $scope.changePasswordForm[field].$error.errorMessage = message;
            })
        })
    };

    $scope.cancelChangePassword = function(){
        $scope.passwordChanging = false;
        $scope.passwordData = {};
    }
}]);
