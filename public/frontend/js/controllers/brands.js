inspinia.controller('brandsListCtrl', ['$scope', '$state', '$http', 'notify', '$authService', '$utils', '$compile','DTColumnBuilder' , 'SweetAlert',
    function( $scope, $state, $http, notify, $auth, $utils, $compile, DTColumnBuilder, SweetAlert){

        $scope.sectionHeader = 'Brand List';
        $scope.breadcrumb = [
            {
                url:'app.dashboard',
                name:'Home'
            }
        ];
        $scope.loggedUser = $scope.$parent.loggedUser;

        $scope.dtInstance ={};

        $scope.dtOptions = $utils.makeDtGetRequest('/get-brand-list', 'GET')
            .withOption('createdRow', function(row, data, dataIndex) {
                $compile(angular.element(row).contents())($scope);
            })
            .withButtons([
                {
                    text: 'Add Brand',
                    className:'btn-add-item',
                    action: function (){
                        $state.go('app.add-brand')
                    }
                }
            ]);
        $scope.dtColumns = [
            DTColumnBuilder
                .newColumn('brand_name')
                .withTitle('Brand Name')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
                //.renderWith(function(data, type, full, meta) {
                //    return data.$id;
                //}),
            DTColumnBuilder
                .newColumn('first_name')
                .withTitle('First Name')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('last_name')
                .withTitle('Last Name')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('email')
                .withTitle('Email')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>'),
            DTColumnBuilder
                .newColumn('image_url')
                .withTitle('Logo')
                .withOption('defaultContent', '<span class="text-muted">Not set</span>')
                .renderWith(function(data, type, full, meta) {
                    var logoUrl = 'img/no-photo.jpg';
                    if (data){
                        logoUrl = data;
                    }
                    return '<img class="img-table-preview center-block" src="'+logoUrl+'">';
                }),
            DTColumnBuilder
                .newColumn('identity')
                .withTitle('Actions')
                .notSortable()
                .renderWith(function(data, type, full, meta){
                    return '<a class="btn btn-primary m-r-md" ui-sref="app.edit-brand({identity:\''+ data +'\'})" >' +
                        '<i class="fa fa-pencil" title="Edit Brand"></i>' +
                        '</a>' +
                        '<a class="btn btn-primary m-r-md" ng-click="loginAs(\''+data+'\')">' +
                        '<i class="fa fa-sign-in" title="Login as Brand"></i>' +
                        '</a>'+
                        '<a class="btn btn-danger" ng-click="deleteBrand(\''+data+'\')">' +
                        '<i class="fa fa-trash" title="Delete Brand"></i>' +
                        '</a>'
                })
        ];

        $scope.loginAs = function(identity){
            console.log(identity)
            $http.get('/login-as/' + identity).success(function(res){
                $auth.setUser(res.data);
                $scope.loggedUser = res.data;
                $state.go('app.dashboard');
                $state.reload();
            }).error(function(res){
                notify({
                    message:'Error!',
                    classes: 'alert-danger'
                });
            })
        }

        $scope.deleteBrand = function(identity){
            SweetAlert.swal({
                    title: "Are you sure?",
                    text: "Your will not be able to recover this Brand file!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Confirm",
                    cancelButtonText: "Cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: true },
                function (isConfirm) {
                    if (isConfirm) {
                        $http.post('/del-brand', {identity:identity}).success(function(res){
                            if (res.status){
                                $scope.dtInstance.reloadData();
                                notify({
                                    message:'Brand Deleted Successful!',
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

}]).controller('editBrandCtrl', ['$scope', '$http', 'notify', 'Upload', '$state', function($scope, $http, notify, Upload, $state){

    $scope.sectionHeader = 'Edit Brand';
    $scope.breadcrumb = [
        {
            url:'app.dashboard',
            name:'Home'
        }
    ];


    $scope.isNewBrand = function(identity){
        if (identity){
            $http.get('/get-brand/'+identity).success(function(res){
                if (res.status){
                    $scope.brand = res.data;
                    $scope.url = '/edit-brand/'+identity;
                }
            }).error(function(res){
                console.log(res);
            });
            return false;
        } else {
            $scope.file = null;
            $scope.brand = {
                brand_name: "",
                first_name: "",
                last_name: "",
                email: "",
                image_url: "img/no-photo.jpg"
            };
            $scope.url = '/create-brand';
            return true;
        }
    };

    $scope.isNewBrand($state.params.identity);

    $scope.upload = function(file){
        if (file){
            $scope.editBrandForm.image_url.$error.serverError = false;
            $scope.uploading = true;
            Upload.upload({
                url: '/upload',
                data: {file: file}
            }).then(function (res) {
                $scope.brand.image_url = res.data.data.url;
                $scope.uploading = false;
            }, function (resp) {
                console.log('Error status: ' + resp.status);
            }, function (evt) {
                $scope.progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                console.log($scope.progressPercentage);
            });
        }
    };

    $scope.saveBrand = function(url){
        $http.post(url, $scope.brand).success(function(res){
            if (res.status){
                if ($scope.isNewBrand($state.params.identity)){
                    $scope.brandMessage = 'Success! You can add another one, or go back to Brand List.';
                } else {
                    $scope.brandMessage = 'Brand Edit Success!';
                }
                notify({
                    message:$scope.brandMessage,
                    classes: 'alert-info'
                });
            }
        }).error(function(res){
            angular.forEach(res.errors, function(message, field){
                $scope.editBrandForm[field].$setValidity('serverError', false);
                $scope.editBrandForm[field].$error.errorMessage = message;
            });
        })
    }
}]);