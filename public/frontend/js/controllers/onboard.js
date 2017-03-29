inspinia.controller('onboardCtrl', ['$scope', '$state', '$utils', 'notify', '$compile', '$authService', '$auth', '$http', 'localStorageService', function($scope, $state, $utils, notify, $compile, $auth, $satellizer, $http, $storage){

    console.log($scope.loggedUser)

    $scope.user = $auth.getUser();
    $scope.getUserMedia = function(){
        var media = $storage.get('userMedia');
        if (media) {
            $scope.user.media = media;
        } else {
            $scope.user.media = {
                socials : [],
                interests : [],
                gender : 'male'
            }
        }
    };

    $scope.getUserMedia();

    $scope.checkState = function(){
        if (($state.current.name == 'onboard.step-two' || $state.current.name == 'onboard.step-three') && !$scope.user.media.socials.length ){
            $state.go('onboard.step-one')
        }
    };

    $scope.checkState();
    $scope.setUserMedia = function(){
        $storage.set('userMedia', $scope.user.media);
    };




    $http.get('/get-interest-list').then(function(res){
        $scope.interests = res.data.data;
    });

    $http.get('/get-interest-list').then(function(res){
        $scope.interests = res.data.data;
        if ($scope.user.media){
            angular.forEach($scope.interests, function(interest){
                angular.forEach($scope.user.media.interests, function(_interest){
                    if (_interest.identity == interest.identity){
                        interest.selected = _interest.selected;
                    }
                });
            });
        }
    });

    $scope.choose = function(interest){
        interest.selected = !interest.selected;
            if (!$scope.user.media.interests.length){
                $scope.user.media.interests.push(interest);
                $scope.setUserMedia()
            } else {
                angular.forEach($scope.user.media.interests, function(_interest, index){
                    if (_interest.identity == interest.identity){
                        $scope.user.media.interests.splice(index,1);
                        $scope.setUserMedia()
                    } else if ($scope.user.media.interests.length == index+1) {
                        $scope.user.media.interests.push(interest);
                        $scope.setUserMedia()
                    }
                })
            }
    };

    $scope.finishOnBoard = function (){
        //localStorage.clear();
        console.log($scope.user)
        //delete $scope.user.accessToken;
        //delete $scope.user.authToken;
        $scope.user.authToken = $scope.loggedUser.accessToken;
        console.log($scope.user)
        $http.post('/edit-profile' ,$scope.user).then(function(res){
            //$auth.setUser(res.data.user);
            $storage.remove('userMedia');
            $state.go('app.dashboardInfluencer');
        })
    };

    $scope.next = function(path){
        if ($scope.user.media.socials.length){
            $state.go(path);
        } else {
            notify({
                message:'Please select at lease one network to proceed!',
                classes: 'alert-info'
            });
        }
    };

    $scope.auth = function (socialNetwork) {
        var socialIndex = $scope.user.media.socials.indexOf(socialNetwork);
        if (socialIndex != -1) {
            $scope.user.media.socials.splice(socialIndex,1);
            $satellizer.unlink(socialNetwork, {
                url:'/unlink'
            }).then(function(res){
                $scope.user.media.socials[socialNetwork] = null;
                $scope.setUserMedia()
            });
        } else {
            $satellizer.authenticate(socialNetwork).then(function(res){
                $scope.user.media.socials.push(socialNetwork);
                localStorage.clear();
                $scope.setUserMedia()
            }).catch(function(response) {
                console.log(response)
            });
        }
    };


    $scope.back = function(path){
        $state.go(path);
    };

}]);