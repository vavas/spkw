/**
 * Created by A.vachik on 12.01.2016.
 */
var adminUtils = angular.module('adminUtils', [
    'datatables',
    'datatables.buttons'
]);

adminUtils.factory('$utils', ['$filter', '$http', '$q', 'DTOptionsBuilder', '$authService', 'notify',
    function($filter, $http, $q, DTOptionsBuilder, $auth, notify){

    return {
        genders: ['Male', 'Female'],
        socials: ['All', 'Instagram', 'Vine', 'Youtube', 'Twitter'],
        ages: ['18-24', '25-36', '37-42', '43+'],
        //interests : ['Motorcycles','Festivals', 'Rock&Roll'],
        states: ['New York', 'Alaska', 'Alabama'],
        makeDtGetRequest: function(apiEndpoint, method){
            return DTOptionsBuilder.newOptions()
                .withDOM('<"html5buttons"B>lTfgitpr')
                .withOption('processing', true) //for show progress bar
                //.withOption('responsive', true) //for show progress bar
                .withOption('serverSide', true) // for server side processing
                .withPaginationType('full_numbers') // for get full pagination options // first / last / prev / next and page numbers
                .withDisplayLength(10) // Page size
                .withOption('aaSorting',[0,'desc']) // for default sorting column // here 0 means first column
                .withOption('ajax', {
                                        url:apiBaseUrl + apiEndpoint,
                                        type: method,
                                        headers:{'Authorization':'Bearer '+ $auth.getUser().accessToken},
                                        data: function(request, dtInstance) {

                                            return {
                                                page:(request.start/request.length)+1,
                                                length:request.length,
                                                order_by:request.columns[request.order[0].column].data,
                                                order_direction:request.order[0].dir,
                                                search:request.search.value
                                            }
                                        },
                                        error:function(err){

                                            notify({
                                                message:err.statusText + ' ' + err.status,
                                                classes:'alert alert-danger'
                                            })
                                        }
                                    }).withDataProp('data');
        }

    }
}]).factory('baseUrl', function(){
    return {
        request: function (config) {

            if ((config.url.indexOf('.html') == -1) && (config.url != '/auth/twitter' && config.url != '/auth/instagram' && config.url != '/auth/google') ){ //disable loading views from https://api-docs.sparkwoo.com
                config.url = apiBaseUrl + config.url;
                return config;
            } else {
                return config;
            }
        }
    }
});