inspinia.controller('resetPasswordCtrl', ['$scope', '$state', '$http', 'notify', function($scope, $state, $http, notify){
    $scope.user = {
        email: ""
    };
    $scope.resetPassword = function(){
        $http.post('/reset-password', $scope.user).success(function (res){
            if(res.status){
                $scope.user = {
                    email: ""
                };
                notify({
                    message:' Reset password success. Please check your email!',
                    classes: 'alert-info'
                });
            }
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                $scope.resetPasswordForm[field].$setValidity('serverError', false);
                $scope.resetPasswordForm[field].$error.errorMessage = message;
            })
        });
    };

}]);