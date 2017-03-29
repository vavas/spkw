inspinia.controller('influencersListCtrl', ['$scope', '$state', '$http', 'notify', '$authService', '$utils', '$compile','DTColumnBuilder','ngDialog',
    function( $scope, $state, $http, notify, $auth, $utils, $compile, DTColumnBuilder, ngDialog){

        $scope.sectionHeader = 'Influencers List';
        $scope.breadcrumb = [
            {
                url:'app.dashboard',
                name:'Home'
            }
        ];
        $scope.loggedUser = $scope.$parent.loggedUser;
        $scope.utils = $utils;

        $scope.filters = {
            socials:{
                all:true
            },
            gender:{},
            age:{},
            reach:{},
            interests:{}
        };

        $http.get('/get-campaign-list').then(function(res){
            if (res.status){
                $scope.allCampaigns = res.data.data;
            }
        }, function(err){
            console.log(err);
        });
        $http.get('/get-interest-list').then(function(res){
            if (res.status){
                $scope.allInterests = res.data.data;
            }
        }, function(err){
            console.log(err);
        });

        $scope.dtInstance = {};

        $scope.dtOptions = $utils.makeDtGetRequest('/get-influencers-list', 'GET')
            .withOption('createdRow', function(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withButtons([])
            .withOption('ajax', {
                url: apiBaseUrl+'/get-influencers-list',  // apiBaseUrl is defined in utils.js module
                type: 'GET',
                headers:{'Authorization':'Bearer '+ $auth.getUser().accessToken},
                data: function(request, dtInstance,data) {
                    return {
                        page:(request.start/request.length)+1,
                        length:request.length,
                        order_by:request.columns[request.order[0].column].data,
                        order_direction:request.order[0].dir,
                        search:request.search.value,
                        filters:$scope.filters
                    }
                }
            });

        $scope.dtColumns = [
            DTColumnBuilder
                .newColumn('identity')
                .withTitle('Influencer')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>')
                .renderWith(function(data, type, full, meta){
                    var socialInfo = function(followers, name, icon){
                        if (data){
                            return '<div class="col-lg-3 col-md-3 col-sm-6">'+
                                '<div class="widget text-center">'+
                                '<div class="m-b-md">'+
                                '<i class="fa '+icon+' fa-4x"></i>'+
                                '<h1>'+followers+'</h1>'+
                                '<h3 class="font-bold no-margins">'+
                                'Followers'+
                                '</h3>'+
                                '<h3 class="m-xs">'+name+'</h3>'+
                                '</div>'+
                                '</div>'+
                                '</div>';
                        } else {
                            return '<div class="col-lg-2 col-md-6"></div>'
                        }
                    };
                    return '<div class="contact-box">'+
                                '<div class="col-lg-2 col-md-6">'+
                                    '<div class="text-center">'+
                                        '<div class="m-t-xs font-bold"><a href="#/app/account/'+data+'">'+full.first_name +' '+ full.last_name+'</a></div>'+
                                        '<img alt="image" class="m-t-xs img-responsive inline" src="'+full.image_url+'">'+
                                        '<address>'+
                                            '<p><i class="fa fa-map-marker"></i> ' +
                                                full.location_city + '/'+full.location_state+
                                            '</p>'+
                                        '</address>'+

                                    '</div>'+
                                '</div>'+
                        '<div class="col-lg-10">' +
                            '<div class="row">'+
                                socialInfo(full.twitter_followers, full.twitter_screen_name, 'fa-twitter')+socialInfo(full.youtube_subscribers, full.youtube_name, 'fa-youtube')+socialInfo(full.instagram_followers, full.instagram_name, 'fa-instagram')/*+socialInfo(full.vine, 'fa-vine')*/+
                                '<div class="col-lg-3 col-md-3 col-sm-6">'+
                                    '<div class="dropdown" uib-dropdown>'+
                                            '<div class="dropdown-toggle btn btn-info" uib-dropdown-toggle> Invite <span class="caret"></span></div>'+
                                            '<ul role="menu" class="dropdown-menu">'+
                                            //'<li ng-repeat="campaign in allCampaigns"><a href="" ng-click="assignCampaign(\''+data+'\', campaign.identity)">{{campaign.title}}</a></li>'+
                                            '<li ng-repeat="campaign in allCampaigns"><a ng-click="inviteCampaign(\''+data+'\', campaign.identity, campaign)">{{campaign.title}}</a></li>'+
                                            '<li><hr></li>'+
                                            '<li><a href="#/app/campaign/create">Create Campaign</a></li>'+
                                            '</ul>'+
                                    '</div>'+
                                '</div>'+
                            '<div class="clearfix"></div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="clearfix"></div>';
                })
        ];
        $scope.inviteCampaign = function (userId, campaignId, campaign) {

            $scope.campaignInf = campaign;
            ngDialog.open({
                template: 'views/popUpInvite.html',
                className: 'ngdialog-theme-plain',
                data: {
                    title: $scope.campaignInf.title,
                    compensation: $scope.campaignInf.compensation,
                    other_compensation: $scope.campaignInf.other_compensation
                },
                preCloseCallback: function(event) {
                    var inviteTextMessage = $scope.inviteMessageModel;
                    if ($scope.inviteCompensationIn){
                        var inviteCompensation = $scope.inviteCompensationIn;
                    } else {
                        var inviteCompensation = $scope.campaignInf.compensation;
                    }
                    if ($scope.inviteOtherCompensationIn){
                        var inviteOtherCompensation = $scope.inviteOtherCompensationIn;
                    } else {
                        var inviteOtherCompensation = 'none';
                    }
                    console.log(inviteCompensation)
                    if (event == 'confirm'){
                        $http.post('/invite-influencer-to-campaign', {
                            influencer_id:userId,
                            campaign_id: campaignId,
                            compensation: inviteCompensation,
                            consideration: inviteOtherCompensation,
                            message: inviteTextMessage
                        }).then(function(res){
                            console.log(res)
                            notify({
                                message: res.data.data.message,
                                classes: 'alert-danger'
                            });
                        }, function(err){
                            notify({
                                message: err.data.errors.message,
                                classes: 'alert-danger'
                            });
                        });
                    }
                }
            });
        };



        $scope.doFilters = function(){
            console.log($scope.filters);
            $scope.dtInstance.reloadData();
        };

        $scope.change = function(){
            console.log($scope.filters);
            //$scope.dtInstance.reloadData();
        };

}]).controller('editInterestCtrl', ['$scope', '$http', 'notify', 'Upload', '$state', function($scope, $http, notify, Upload, $state){

    $scope.sectionHeader = 'Edit Interest';
    $scope.breadcrumb = [
        {
            url:'app.dashboard',
            name:'Home'
        }
    ];

    $scope.statuses = ['active', 'inactive'];

    $scope.isNew = function(identity){
        if (identity){
            $http.get('/get-brand/'+identity).success(function(res){
                if (res.status){
                    $scope.brand = res.data;
                    $scope.url = '/edit-brand/'+identity;
                    $scope.sectionHeader = 'Edit Interest';
                }
            }).error(function(res){
                console.log(res);
            });
            return false;
        } else {
            $scope.file = null;
            $scope.interest = {
                name: "",
                description: "",
                status: "inactive"
            };
            $scope.url = '/create-interest';
            $scope.sectionHeader = 'Add Interest';
            return true;
        }
    };

    $scope.isNew($state.params.identity);


    $scope.save = function(url){
        $http.post(url, $scope.interest).success(function(res){
            if (res.status){
                if ($scope.isNew($state.params.identity)){
                    $scope.message = 'Success! You can add another one, or go back to Brand List.';
                } else {
                    $scope.message = 'Brand Edit Success!';
                }
                notify({
                    message:$scope.message,
                    classes: 'alert-info'
                });
            }
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                if ($scope.editInterestForm[field]){
                    $scope.editInterestForm[field].$setValidity('serverError', false);
                    $scope.editInterestForm[field].$error.errorMessage = message;
                }
            });
        })
    }
}]);