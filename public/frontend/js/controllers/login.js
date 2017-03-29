inspinia.controller('loginCtrl', ['$scope', '$state', '$http', '$authService', 'notify', function($scope, $state, $http, $auth, notify){
    $scope.user = {
        email:'',
        password:''
    };

    $scope.newUser = {
        first_name: "",
        last_name: "",
        email: "",
        password: "",
        password_confirmation: ""
    };

    $scope.showLoginForm = true;
    $scope.showForgetForm = false;
    $scope.showSignUpForm = false;

    $scope.login = function(){
        $http.post('/login', $scope.user).success(function (res){
            if(res.status){
                $auth.setUser(res.data);
                console.log(res)
                if(res.data.role == 'influencer'){
                    $state.go('app.dashboardInfluencer');
                } else {
                    $state.go('app.dashboard');
                }
            }
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                if (field == 'message'){
                    notify({
                        message:message,
                        classes: 'alert-info'
                    });
                    return true;
                } else {
                    $scope.loginForm[field].$setValidity('serverError', false);
                    $scope.loginForm[field].$error.errorMessage = message;
                }
            })
        });
    };

    $scope.signUp = function(){
        $http.post('/sign-up', $scope.newUser).success(function (res){
            if(res.status){
                $state.go('app.wizard');
            }
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                $scope.signUpForm[field].$setValidity('serverError', false);
                $scope.signUpForm[field].$error.errorMessage = message;
            })
        });
    };

}]);