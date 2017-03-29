var adminServices = angular.module('adminServices',[]);

adminServices.service('$authService', ['$http', '$rootScope', '$state', '$location', '$window', '$cookies', 'notify', function($http, $rootScope, $state, $location, $window, $cookies, notify){

    var user,
        _getUser = function(){
            var userCookie = $cookies.get('me');
            if (!user && userCookie){
                user = JSON.parse(userCookie);
                $http.defaults.headers.common.Authorization = 'Bearer '+user.accessToken;
                return user;
            } else {
                return user;
            }
        },
        _setUser = function(data){
            user = data;
            $cookies.put('me', JSON.stringify(user));
            $http.defaults.headers.common.Authorization = 'Bearer '+user.accessToken;
            $rootScope.$broadcast('$userChanged');
        },
        _logout = function(){
            $http.get('/logout').then(function(res){
                if (res.data.status){
                    user = $cookies.remove('me');
                    $state.go('login');
                }
            }, function(err){
                notify({
                    message:'Logout Fail!',
                    className:'alert-danger'
                })
            });
        };

    $rootScope.$on('$stateChangeStart', function(event, route){
        user = _getUser();
        if ((!user || !user.accessToken) && route.name != 'login'){
            switch (route.name) {
                case 'register':{
                    break;
                }
                case 'reset-password':{
                    break;
                }
                default :{
                    event.preventDefault();
                    $state.go('login');
                }
            }
        } else if (user && user.onboarding == 1 && (route.name == 'onboard.step-one' ||
                                            route.name == 'onboard.step-two' ||
                                            route.name == 'onboard.step-three')){
            $state.go('app.dashboard({identity:\''+user.identity+'\'})');
        }
    });

    return{
        getUser: _getUser,
        setUser: _setUser,
        logout: _logout
    }
}]);