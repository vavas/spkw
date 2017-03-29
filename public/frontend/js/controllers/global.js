inspinia.controller('globalCtrl', ['$rootScope','$scope' ,'$http', '$state', '$authService', function($rootScope, $scope, $http, $state, $auth){
    $scope.logout = $auth.logout;



    $scope.loggedUser = $auth.getUser(); // getUser after page reload
    $rootScope.$on('$userChanged', function(){
        $scope.loggedUser = $auth.getUser(); // get user if account was changed
        $scope.getUserInformation();
    });

    $scope.getUserInformation = function() {
        if($scope.loggedUser) {
            $http.get('/user-profile/' + $scope.loggedUser.identity).success(function (res) {
                $scope.additionalUserInf = res.data;
                $scope.userAvatar = res.data.image_url;
            })
        }
    };

    $scope.getUserInformation();

    $scope.menu = [
        {
            title:'Dashboard',
            ui_ref:'app.dashboard',
            icon:'fa-th-large',
            permissions:['brand','admin']
        },
        {
            title:'Dashboard',
            ui_ref:'app.dashboardInfluencer',
            icon:'fa-th-large',
            permissions:['influencer']
        },
        {
            title:'Brands',
            ui_ref:'app.brands',
            icon:'fa-money',
            permissions:['admin']
        },
        {
            title:'Campaigns',
            ui_ref:'campaigns',
            icon:'fa-list',
            subItems:[
                {
                    title:'Edit',
                    ui_ref:'campaigns.campaigns',
                    permissions:['admin']
                }
            ],
            permissions:['admin']
        },
        {
            title:'Campaigns',
            ui_ref:'campaigns',
            icon:'fa-list',
            subItems:[
                {
                    title:'Edit',
                    ui_ref:'campaigns.campaigns',
                    permissions:['admin', 'brand']
                },
                {
                    title:'Manage',
                    ui_ref:'campaigns.campaignsManage',
                    permissions:['brand']
                }
            ],
            permissions:['brand']
        },
        {
            title:'Campaigns',
            ui_ref:'campaignsView.search',
            icon:'fa-list',
            permissions:['influencer']
        },
        {
            title:'Influencers',
            ui_ref:'influencers',
            icon:'fa-users',
            subItems:[
                {
                    title:'Search',
                    ui_ref:'influencers.search',
                    permissions:['admin', 'brand']
                }
            ],
            permissions:['admin', 'brand']
        },
        {
            title:'Account',
            ui_ref:'app.profile',
            icon:'fa-user',
            permissions:['all']
        },
        {
            title:'Earnings',
            ui_ref:'earnings',
            icon:'fa-money',
            permissions:['influencer']
        },
        {
            title:'Billing',
            ui_ref:'billing',
            icon:'fa-money',
            subItems:[
                {
                    title:'Invoice',
                    ui_ref:'invoice',
                    permissions:['admin', 'brand']
                }
            ],
            permissions:['admin', 'brand']
        },
        {
            title:'Help',
            ui_ref:'help',
            icon:'',
            permissions:['all']
        },
        {
            title:'Settings',
            ui_ref:'settings',
            icon:'fa-gear',
            subItems:[
                {
                    title:'Interests',
                    ui_ref:'settings.interests',
                    permissions:['admin']
                }
            ],
            permissions:['admin']
        }
    ];
}]);
