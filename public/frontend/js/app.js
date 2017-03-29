/**
 * INSPINIA - Responsive Admin Theme
 *
 */

var inspinia = angular.module('inspinia', [
    'ui.router',                    // Routing
    'ngFileUpload',                    // File Upload
    'oc.lazyLoad',                  // ocLazyLoad
    'ui.bootstrap',                 // Ui Bootstrap
    'pascalprecht.translate',       // Angular Translate
    'ngIdle',                       // Idle timer
    'ngCookies',                       // Idle timer
    'ngSanitize',                    // ngSanitize
    'adminUtils',                    // utils
    'adminDirectives',                    // utils
    'adminServices',                    // utils
    'cgNotify',                    // notifications
    'LocalStorageModule',
    'satellizer',
    'ngDialog',
    'angular-loading-bar'
]);

// Other libraries are loaded dynamically in the config.js file using the library ocLazyLoad