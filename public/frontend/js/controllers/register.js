inspinia.controller('registerCtrl', ['$scope', '$auth', '$state', '$http', 'notify', 'vcRecaptchaService', function($scope,$auth, $state, $http, notify, vcRecaptchaService){
    $scope.user = {
        first_name: "",
        last_name: "",
        email: "",
        password: "",
        password_confirmation: ""
    };

    $scope.captcha = {
        key:'6LdlfR8TAAAAAFM3vJH7h8h8Utr9QAieZ22Yeu3y',  // prod key
        //key:'6LfW9BoTAAAAAFHxce6rQfNgpBBVsq-NlnNeqeh8',  // prod key
        //key:'6Lcj9RoTAAAAACBDfJYzHJxbWPyI12vwNxHGF4Fl', // dev key
        response: null,
        widgetId:null
    };

    $scope.setCaptchaResponse = function (response) {
        console.info('Response available');
        $scope.captcha.error = false;
        $scope.captcha.response = response;
    };

    $scope.setCaptchaWidgetId = function (widgetId) {
        console.info('Created widget ID: %s', widgetId);
        $scope.captcha.widgetId = widgetId;
    };

    $scope.cbCaptchaExpiration = function() {
        console.info('Captcha expired. Resetting response object');
        vcRecaptchaService.reload($scope.captcha.widgetId);
        $scope.captcha.response = null;
    };

    $scope.register = function(){
        if ($scope.captcha.response){
            $http.post('/sign-up', $scope.user).success(function (res){
                if(res.status){
                    $scope.user = {
                        first_name: "",
                        last_name: "",
                        email: "",
                        password: "",
                        password_confirmation: ""
                    };
                    notify({
                        message:'Registration Successful. Please check your email to activate account!',
                        classes: 'alert-info'
                    });
                    $auth.setUser(res.data);
                    window.location.href = res.data.url;
                }
            }).error(function(res){
                angular.forEach(res.errors, function(message, field){
                    $scope.signUpForm[field].$setValidity('serverError', false);
                    $scope.signUpForm[field].$error.errorMessage = message;
                })
            });
        } else {
            $scope.captcha.error = true;
            $scope.captcha.errorMessage = 'Confirm that you are not a robot!';
        }
    };

}]);