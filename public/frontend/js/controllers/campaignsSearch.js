inspinia.controller('campaignsSearchListCtrl', ['$scope', '$state', '$http', 'notify', '$authService', '$utils', '$compile','DTColumnBuilder',
    function( $scope, $state, $http, notify, $auth, $utils, $compile, DTColumnBuilder){

        $scope.sectionHeader = 'Campaigns List';
        $scope.breadcrumb = [
            {
                url:'app.dashboard',
                name:'Home'
            }
        ];
        $scope.loggedUser = $scope.$parent.loggedUser;
        $scope.utils = $utils;

        $scope.filters = {
            socials:{},
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

        $scope.dtOptions = $utils.makeDtGetRequest('/get-campaign-list', 'GET')
            .withOption('createdRow', function(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withButtons([])
            .withOption('ajax', {
                url: apiBaseUrl+'/get-campaign-list',  // apiBaseUrl is defined in utils.js module
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
                    return '<div class="contact-box">'+
                        '<div class="col-lg-2 col-md-6">'+
                        '<div class="text-center">'+
                        '<img alt="image" class="m-t-xs img-responsive inline" src="'+full.brand_logo+'">'+
                        '</div>'+
                        '</div>'+
                        '<div class="col-lg-10">' +
                        '<div class="row">'+
                            '<div class="col-md-9">' +
                                '<div class="col-md-12">' +
                            '<h3>'+full.title +'</h3>'+
                        '<strong>Details: </strong>'+full.details+'</div>' +
                            '<div class="col-md-6"><strong>Apply By: </strong>'+full.submission_deadline+'</div><div class="col-md-6"><strong>Post Approval Deadline: </strong>'+full.posting_date+'</div>'+
                        '</div>'+
                        '<div class="col-lg-3 col-md-3 col-sm-6">'+
                        '<div>'+
                        '<div class="campaignSocialInf"><i class="fa fa-'+full.social_network.toLowerCase()+'"></i></div>'+
                        '<a href="#/app/campaignApply/'+full.identity+'"><div class="btn btn-info" uib-dropdown-toggle> View Campaign </div></a>'+
                        '</div>'+
                        '</div>'+
                        '<div class="clearfix"></div>'+
                        '</div>'+
                        '</div>'+
                        '<div class="clearfix"></div>';
                })
        ];


        $scope.doFilters = function(){
            console.log($scope.filters);
            $scope.dtInstance.reloadData();
        };

        $scope.change = function(){
            console.log($scope.filters);
            //$scope.dtInstance.reloadData();
        };

    }])