inspinia.controller('interestsListCtrl', ['$scope', '$state', '$http', 'notify', '$authService', '$utils', '$compile','DTColumnBuilder' , 'SweetAlert',
    function( $scope, $state, $http, notify, $auth, $utils, $compile, DTColumnBuilder, SweetAlert){

        $scope.sectionHeader = 'Interests List';
        $scope.breadcrumb = [
            {
                url:'app.dashboard',
                name:'Home'
            }
        ];
        $scope.loggedUser = $auth.getUser();

        $scope.dtInstance ={};

        $scope.dtOptions = $utils.makeDtGetRequest('/get-interest-list', 'GET')
            .withOption('createdRow', function(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withButtons([
                {
                    text: 'Add Interest',
                    className:'btn-add-item',
                    action: function (){
                        $state.go('settings.edit-interest')
                    }
                }
            ]);

        $scope.dtColumns = [
            DTColumnBuilder
                .newColumn('interest_name')
                .withTitle('Name')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('status')
                .withTitle('Status')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>')
                .renderWith(function(data, type, full, meta){
                    var badgeType;
                    switch (data){
                        case 'active':{
                            badgeType = 'badge-primary';
                            break;
                        }
                        case 'inactive':{
                            badgeType = 'badge-warning';
                            break;
                        }
                    }
                    return '<span class="badge '+badgeType+'">'+data+'</span>';
                }),
            DTColumnBuilder
                .newColumn('identity')
                .withTitle('Actions')
                .notSortable()
                .renderWith(function(data, type, full, meta){

                return  '<a class="btn btn-primary m-r-md btn-xs" ui-sref="settings.edit-interest({identity:\''+ data +'\'})" >' +
                        '<i class="fa fa-pencil" title="Edit Interest"></i> Edit' +
                        '</a>' +
                        '<a class="btn btn-danger m-r-md btn-xs" ng-click="delete(\''+data+'\')">' +
                        '<i class="fa fa-trash" title="Delete Interest"></i> Delete' +
                        '</a>';
                })
        ];

        $scope.delete = function(identity){
            SweetAlert.swal({
                    title: "Are you sure?",
                    text: "Your will not be able to recover this Interest!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Confirm",
                    cancelButtonText: "Cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: true },
                function (isConfirm) {
                    if (isConfirm) {
                        $http.post('/del-interest', {identity:identity}).success(function(res){
                            if (res.status){
                                $scope.dtInstance.reloadData();
                                notify({
                                    message:'Interest Deleted Successful!',
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
            $http.get('/get-interest/'+identity).success(function(res){
                if (res.status){
                    $scope.interest = res.data;
                    $scope.url = '/edit-interest/'+identity;
                    $scope.sectionHeader = 'Edit Interest';
                }
            }).error(function(res){
                console.log(res);
            });
            return false;
        } else {
            $scope.interest = {
                interest_name: "",
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
                    $scope.message = 'Success! You can add another one, or go back to Interest List.';
                } else {
                    $scope.message = 'Interest Edit Success!';
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