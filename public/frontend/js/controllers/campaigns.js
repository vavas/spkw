inspinia.controller('campaignsListCtrl', ['$scope', '$state', '$http', 'notify', '$authService', '$utils', '$compile','DTColumnBuilder' , 'SweetAlert',
    function( $scope, $state, $http, notify, $auth, $utils, $compile, DTColumnBuilder, SweetAlert){

        $scope.sectionHeader = 'Campaign List';
        $scope.breadcrumb = [
            {
                url:'app.dashboard',
                name:'Home'
            }
        ];
        $scope.loggedUser = $scope.$parent.loggedUser;

        $scope.dtInstance ={};

        $scope.dtOptions = $utils.makeDtGetRequest('/get-campaign-list', 'GET')
            .withOption('createdRow', function(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withButtons([
                {
                    text: 'Add Campaign',
                    className:'btn-add-item',
                    action: function (){
                        $state.go('app.add-campaign')
                    }
                }
            ]);

        $scope.dtColumns = [
            DTColumnBuilder
                .newColumn('title')
                .withTitle('Campaign Title')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('brand_name')
                .withTitle('Brand Name')
                .notSortable()
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('social_network')
                .withTitle('Network')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('type')
                .withTitle('Type')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('submission_deadline')
                .withTitle('Submission Deadline')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('application_deadline')
                .withTitle('Application Deadline')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('guidelines')
                .withTitle('Guidelines')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>')
                .renderWith(function(data, type, full, meta){
                    var guidelines = '';
                    if (data.length){
                        for (var i=0; i<data.length; i++){
                            //console.log(data[i].interest_name)
                            guidelines  += '<span class="badge m-r-sm m-b-sm">'+data[i].text+'</span>'
                        }
                        return guidelines;
                    } else {
                        return '<span class="text-muted">Not set</span>';
                    }
                }),
            DTColumnBuilder
                .newColumn('media')
                .withTitle('Media')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('interests')
                .withTitle('Interests')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>')
                .renderWith(function(data, type, full, meta){
                    var interests = '';
                    if (data.length){
                        for (var i=0; i<data.length; i++){
                            //console.log(data[i].interest_name)
                            interests  += '<span class="badge m-r-sm m-b-sm">'+data[i].interest_name+'</span>'
                        }
                        return interests;
                    } else {
                        return '<span class="text-muted">Not set</span>';
                    }
                }),
            DTColumnBuilder
                .newColumn('compensation')
                .withTitle('Compensation')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('minimum_reach')
                .withTitle('Minimum Reach')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('posting_date')
                .withTitle('Posting Date')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('condition')
                .withTitle('Condition')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('identity')
                .withTitle('Actions')
                .notSortable()
                .renderWith(function(data, type, full, meta){

                return  '<a ng-if="loggedUser.role == \'brand\'" class="btn btn-primary m-r-md btn-xs" ui-sref="app.edit-campaign({identity:\''+ data +'\'})" >' +
                        '<i class="fa fa-pencil" title="Edit Campaign"></i> Edit' +
                        '</a>' +
                        '<a ng-if="loggedUser.role == \'brand\'" class="btn btn-warning m-r-md btn-xs" ui-sref="app.manage-campaign({identity:\''+ data +'\'})" >'+
                        '<i class="fa fa-list" title="Manage Campaign"></i> Manage' +
                        '</a>'+
                        '<a ng-if="loggedUser.role == \'admin\'" class="btn btn-danger m-r-md btn-xs" ng-click="deleteCampaign(\''+data+'\')">' +
                        '<i class="fa fa-trash" title="Delete Campaign"></i> Delete' +
                        '</a>';
                })
        ];

        $scope.deleteCampaign = function(identity){
            SweetAlert.swal({
                    title: "Are you sure?",
                    text: "Your will not be able to recover this Campaign!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Confirm",
                    cancelButtonText: "Cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: true },
                function (isConfirm) {
                    if (isConfirm) {
                        $http.post('/del-campaign', {identity:identity}).success(function(res){
                            if (res.status){
                                $scope.dtInstance.reloadData();
                                notify({
                                    message:'Campaign Deleted Successful!',
                                    classes: 'alert-info'
                                });
                            }
                        }).error(function(res){
                            notify({
                                message:'Error!',
                                classes: 'alert-danger'
                            });
                        })
                    }
                })
        }

}]).controller('editCampaignCtrl', ['$scope', '$http', 'notify', 'Upload', '$state', '$utils', 'Upload',
    function($scope, $http, notify, Upload, $state, $utils, $upload){

    $scope.loggedUser = $scope.$parent.loggedUser;

    //$scope.interests = $utils.interests;

    $scope.getIinterests = function(){
        $http.get('/get-interest-list').success(function(res){
            if (res.status){
                $scope.interests = res.data;
            }
        }).error(function(res){
            notify({
                message:'Error Getting Interests!',
                classes: 'alert-danger'
            });
        });
    };

    $scope.getIinterests();

    $scope.breadcrumb = [
        {
            url:'app.dashboard',
            name:'Home'
        }
    ];

    $scope.networks = ['Twitter', 'Vine', 'Instagram', 'YouTube'];
    $scope.types = ['public','private'];
    $scope.statuses = ['open','closed'];
    $scope.visibilities = ['draft','published'];
    $scope.campaignMedia = ['image', 'video', 'gif', 'brand provided'];
    $scope.disclosures = ["#Sp","#sp", "#SP", "#sponsored", "#Ad", "#ad", "#AD" ,"null"];

    $scope.getBrands = function(){
        $http.get('/get-brand-list').success(function(res){
            if (res.status){
                $scope.brands = res.data;
            }
        }).error(function(res){
            notify({
                message:'Error Getting Brands!',
                classes: 'alert-danger'
            });
        });
    };

    if ($scope.loggedUser.role == 'admin'){
        $scope.getBrands();
    }

    $scope.isNewCampaign = function(identity){
        if (identity){
            $scope.sectionHeader = 'Edit Campaign';
            $http.get('/get-campaign/'+identity).success(function(res){
                if (res.status){
                    $scope.campaign = res.data;
                    //Model correction for ui datePicker plugin (it requires Date object instead of string)
                    $scope.campaign.submission_deadline = new Date ($scope.campaign.submission_deadline);
                    $scope.campaign.application_deadline = new Date ($scope.campaign.application_deadline);
                    $scope.campaign.posting_date = new Date ($scope.campaign.submission_deadline);
                    $scope.url = '/edit-campaign/'+identity;
                }
            }).error(function(res){
                console.log(res);
            });
            return false;
        } else {
            $scope.sectionHeader = 'Create Campaign';
            $scope.campaign = {
                title: "",
                social_network: "twitter",
                type: "public",
                submission_deadline: new Date(),
                application_deadline: new Date(),
                guidelines: [],
                media: "image",
                interests: [],
                compensation: 0,
                other_compensation:'',
                minimum_reach: 1,
                posting_date: new Date(),
                visibility:'draft',
                disclosure:"",
                status:'open',
                hashtag: [],
                mention: [],
                url:'',
                campaign_image:''
            };
            if ($scope.loggedUser.role == 'brand'){
                $scope.campaign.brand_id = $scope.loggedUser.identity;
            } else {
                $scope.campaign.brand_id = '';
            }
            $scope.url = '/create-campaign';
            return true; //new campaign , proceeding with creating new one
        }
    };

    $scope.isNewCampaign($state.params.identity);

    $scope.dateOptions = {
        dateDisabled: function(data) {
            var date = data.date,
                mode = data.mode;
            return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
        },
        format: 'yyyy-MM-dd',
        maxDate: new Date(2020, 5, 22),
        minDate: new Date(),
        startingDay: 1
    };
    $scope.altInputFormats = ['yyyy-MM-dd'];

    $scope.uploadImage = function(file){
        if (file){
            $upload.upload({
                url: '/upload',
                data: {file: file}
            }).then(function (res) {
                $scope.campaign.campaign_image = res.data.data.url;
            }, function (resp) {
                notify({
                    message:'Error upload status: ' + resp.status,
                    classes:'alert alert-danger'
                });

            });
        }
    };

    $scope.saveCampaign = function(url){
        $http.post(url, $scope.campaign).success(function(res){
            if (res.status){
                if ($scope.isNewCampaign($state.params.identity)){
                    $scope.campaignMessage = 'Success! You can add another one, or go back to Campaign List.';
                } else {
                    $scope.campaignMessage = 'Campaign Edit Success!';
                }

                notify({
                    message:$scope.campaignMessage,
                    classes: 'alert-info'
                });
            }
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                if ($scope.editCampaignForm[field]){
                    $scope.editCampaignForm[field].$setValidity('serverError', false);
                    $scope.editCampaignForm[field].$error.errorMessage = message;
                }
            });
        })
    }
}]);
//    .controller('createCampaignCtrl', ['$scope', '$http', 'notify', 'Upload', '$state', '$utils', 'localStorageService',
//    function($scope, $http, notify, Upload, $state, $utils, localStorageService){
//
//
//
//    $scope.campaign = {
//        min_influencer_reach: 2
//    };
//
//    $scope.loggedUser = $scope.$parent.loggedUser;
//
//    $scope.interests = $utils.interests;
//
//    $scope.sectionHeader = 'Edit Campaign';
//    $scope.breadcrumb = [
//        {
//            url:'app.dashboard',
//            name:'Home'
//        }
//    ];
//
//
//    $scope.networks = ['Twitter', 'Vine', 'Instagram', 'YouTube'];
//    $scope.types = ['public','private'];
//    $scope.conditions = ['project','public'];
//    $scope.campaignMedia = ['image', 'video', 'gif', 'brand provided'];
//    $scope.hashTags = ['#image', '#video', '#gif', '#brand'];
//    $scope.mentions = ['@some', '@new', '@mention'];
//    $scope.disclosures = ["#Sp","#sp", "#SP", "#sponsored", "#Ad", "#ad", "#AD" ,"null"];
//
//    $scope.getBrands = function(){
//        $http.get('/get-brand-list').success(function(res){
//            if (res.status){
//                $scope.brands = res.data;
//            }
//        }).error(function(res){
//            notify({
//                message:'Error Getting Brands!',
//                classes: 'alert-danger'
//            });
//        });
//    };
//
//    if ($scope.loggedUser.role == 'admin'){
//        $scope.getBrands();
//    }
//
//    $scope.isNewCampaign = function(identity){
//        if (identity){
//            $http.get('/get-campaign/'+identity).success(function(res){
//                if (res.status){
//                    $scope.campaign = res.data;
//                    //Model correction for ui datePicker plugin (it requires Date object instead of string)
//                    $scope.campaign.submission_deadline = new Date ($scope.campaign.submission_deadline);
//                    $scope.campaign.application_deadline = new Date ($scope.campaign.submission_deadline);
//                    $scope.campaign.posting_date = new Date ($scope.campaign.submission_deadline);
//                    $scope.url = '/edit-campaign/'+identity;
//                }
//            }).error(function(res){
//                console.log(res);
//            });
//            return false;
//        } else {
//            var storedCampaign = localStorageService.get('campaign');
//            if (storedCampaign){
//                $scope.campaign = storedCampaign;
//            } else {
//                $scope.campaign = {
//                    brand_id: $scope.loggedUser.identity,
//                    title: "",
//                    social_network: "twitter",
//                    type: "public",
//                    submission_deadline: new Date(),
//                    application_deadline: new Date(),
//                    guidelines: [{text:'Guideline'}],
//                    media: "image",
//                    interests: [],
//                    hash_tags: [],
//                    mentions: [],
//                    compensation: "",
//                    minimum_reach: 1,
//                    posting_date: new Date(),
//                    condition: "public",
//                    disclosure:""
//                };
//                localStorageService.set('campaign', $scope.campaign);
//            }
//            $scope.url = '/create-campaign';
//            return true; //new campaign , proceeding with creating new one
//        }
//    };
//
//    $scope.isNewCampaign($state.params.identity);
//
//    $scope.dateOptions = {
//        dateDisabled: function(data) {
//            var date = data.date,
//                mode = data.mode;
//            return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
//        },
//        format: 'yyyy-MM-dd',
//        maxDate: new Date(2020, 5, 22),
//        minDate: new Date(),
//        startingDay: 1
//    };
//    $scope.altInputFormats = ['yyyy-MM-dd'];
//
//    $scope.saveCampaign = function(url){
//        $http.post(url, $scope.campaign).success(function(res){
//            if (res.status){
//                if ($scope.isNewCampaign($state.params.identity)){
//                    $scope.campaignMessage = 'Success! You can add another one, or go back to Campaign List.';
//                } else {
//                    $scope.campaignMessage = 'Campaign Edit Success!';
//                }
//
//                notify({
//                    message:$scope.campaignMessage,
//                    classes: 'alert-info'
//                });
//            }
//        }).error(function(res){
//            angular.forEach(res.errors, function(message, field){
//                $scope.editCampaignForm[field].$setValidity('serverError', false);
//                $scope.editCampaignForm[field].$error.errorMessage = message;
//            });
//        })
//    };
//
//    $scope.next = function(path, $form){
//        localStorageService.set('campaign', $scope.campaign);
//        if ($form.$valid){
//            $state.go(path);
//        } else {
//            notify({
//                message:'Please fill all fields to proceed!',
//                classes: 'alert-info'
//            });
//        }
//    };
//
//    $scope.back = function(path){
//        localStorageService.set('campaign', $scope.campaign);
//        $state.go(path);
//    };
//}]);