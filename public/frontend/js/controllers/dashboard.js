inspinia.controller('dashboardCtrl', ['$scope', '$http', '$state', '$authService', function($scope, $http, $state, $auth){
    $scope.loggedUser = $scope.$parent.loggedUser;
    //console.log($scope.loggedUser);

    $http.get('/user-profile/' + $scope.loggedUser.identity).success(function (res) {
        $scope.additionalUserInf = res.data;
        $scope.userAvatar = res.data.image_url;
    })

}]);