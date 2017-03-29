/**
 * INSPINIA - Responsive Admin Theme
 *
 * Inspinia theme use AngularUI Router to manage routing and views
 * Each view are defined as state.
 * Initial there are written state for all view in theme.
 *
 */

inspinia.config(['$stateProvider', '$urlRouterProvider', '$ocLazyLoadProvider', 'IdleProvider', 'KeepaliveProvider', 'localStorageServiceProvider', '$authProvider', '$httpProvider',
    function($stateProvider, $urlRouterProvider, $ocLazyLoadProvider, IdleProvider, KeepaliveProvider, localStorageServiceProvider, $authProvider, $httpProvider) {

        $httpProvider.interceptors.push('baseUrl');

        localStorageServiceProvider

            .setPrefix('social')
            .setStorageType('sessionStorage');

        // Configure Idle settings
        IdleProvider.idle(5); // in seconds
        IdleProvider.timeout(120); // in seconds

        $authProvider.google({
            clientId: '464868854130-o026f0kajv089blt87o3ike32336avv8.apps.googleusercontent.com',
            url:'/auth/google',
            scope: ['https://www.googleapis.com/auth/youtube.force-ssl'],
            authorizationEndpoint: 'https://accounts.google.com/o/oauth2/auth',
            redirectUri:window.location.origin + '/auth/google',
            optionalUrlParams: ['access_type'],
            accessType: 'offline',
            scopePrefix: null,
            //authHeader: getCookie('me')
        });

        $authProvider.instagram({
            clientId:'8d1e40230ae845c9a1d417dc36ced507',
            name: 'instagram',
            url: '/auth/instagram',
            authorizationEndpoint: 'https://api.instagram.com/oauth/authorize',
            redirectUri: window.location.origin + '/auth/instagram',
            requiredUrlParams: ['scope'],
            scope: ['basic'],
            scopeDelimiter: '+',
            type: '2.0'
        });

        $authProvider.twitter({
            clientId:'Ujphsq5CJm6SUAZ0doblxejKd',
            url: '/auth/twitter',
            authorizationEndpoint: 'https://api.twitter.com/oauth/authenticate',
            redirectUri: window.location.origin+'/auth/twitter',
            type: '1.0',
            popupOptions: { width: 495, height: 645 }
        });

        $urlRouterProvider.otherwise("/signin");

        $ocLazyLoadProvider.config({
            // Set to true if you want to see what and when is dynamically loaded
            debug: false
        });

        $stateProvider
            .state('login', {
                url: "/signin",
                templateUrl: "views/login.html",
                controller:'loginCtrl',
                data: { pageTitle: 'Sign In', specialClass: 'gray-bg' , permissions:['all']},
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/login.js']
                            }
                        ]);
                    }
                }
            })
            .state('reset-password', {
                url: "/reset-password",
                templateUrl: "views/reset-password.html",
                controller:'resetPasswordCtrl',
                data: { pageTitle: 'Reset Password', specialClass: 'gray-bg',  permissions:['all'] },
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                serie:true,
                                files:['css/plugins/angular-notify/angular-notify.min.css','js/plugins/angular-notify/angular-notify.min.js']
                            },
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/reset-password.js']
                            },
                            {
                                name: 'cgNotify',
                                files: ['css/plugins/angular-notify/angular-notify.min.css','js/plugins/angular-notify/angular-notify.min.js']
                            }
                        ]);
                    }
                }
            })
            .state('register', {
                url: "/signup",
                templateUrl: "views/register.html",
                controller:'registerCtrl',
                data: { pageTitle: 'signup', specialClass: 'gray-bg' , permissions:['all']},
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/register.js']
                            },
                            {
                                name:'vcRecaptcha',
                                files:['https://www.google.com/recaptcha/api.js?onload=vcRecaptchaApiLoaded&render=explicit','js/plugins/angular-recaptcha-2.5.0/release/angular-recaptcha.min.js']
                            }
                        ]);
                    }
                }
            })
            .state('app', {
                abstract: true,
                url: "/app",
                templateUrl: "views/common/content.html"
            })
            .state('onboard', {
                url: "/onboard",
                templateUrl: "views/common/onboard-wizard.html",
                controller: 'onboardCtrl',
                data: { pageTitle: 'On Board' , specialClass:'gray-bg'},
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                files: ['css/plugins/steps/jquery.steps.css']
                            },
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/onboard.js']
                            }
                        ]);
                    }
                }
            })
            .state('onboard.step-one', {
                url: '/step-one',
                templateUrl: 'views/wizard/step-one.html',
                data: { pageTitle: 'Social Networks' }
            })
            .state('onboard.step-two', {
                url: '/step-two',
                templateUrl: 'views/wizard/step-two.html',
                data: { pageTitle: 'Interests' }
            })
            .state('onboard.step-three', {
                url: '/step-three',
                templateUrl: 'views/wizard/step-three.html',
                data: { pageTitle: 'Personal Information' },
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                serie:true,
                                files: ['js/plugins/moment/moment.min.js']
                            },
                            {
                                serie:true,
                                name: 'datePicker',
                                files: ['css/plugins/datapicker/angular-datapicker.css','js/plugins/datapicker/angular-datepicker.js']
                            },
                            {
                                serie:true,
                                files: ['css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css']
                            }
                        ]);
                    }
                }
            })
            .state('app.dashboard', {
                url: "/dashboard/:identity",
                templateUrl: "views/dashboard.view.html",
                data:{pageTitle:'Dashboard',  permissions:['brand','admin']},
                controller:'dashboardCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {

                                serie: true,
                                name: 'angular-flot',
                                files: [ 'js/plugins/flot/jquery.flot.js', 'js/plugins/flot/jquery.flot.time.js', 'js/plugins/flot/jquery.flot.tooltip.min.js', 'js/plugins/flot/jquery.flot.spline.js', 'js/plugins/flot/jquery.flot.resize.js', 'js/plugins/flot/jquery.flot.pie.js', 'js/plugins/flot/curvedLines.js', 'js/plugins/flot/angular-flot.js', ]
                            },
                            {
                                name: 'angles',
                                files: ['js/plugins/chartJs/angles.js', 'js/plugins/chartJs/Chart.min.js']
                            },
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/dashboard.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.dashboardInfluencer', {
                url: "/dashboardInf/:identity",
                templateUrl: "views/dashboardInfluencer.html",
                data:{pageTitle:'Dashboard',  permissions:['influencer']},
                controller:'dashboardInfluencerCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {

                                serie: true,
                                name: 'angular-flot',
                                files: [ 'js/plugins/flot/jquery.flot.js', 'js/plugins/flot/jquery.flot.time.js', 'js/plugins/flot/jquery.flot.tooltip.min.js', 'js/plugins/flot/jquery.flot.spline.js', 'js/plugins/flot/jquery.flot.resize.js', 'js/plugins/flot/jquery.flot.pie.js', 'js/plugins/flot/curvedLines.js', 'js/plugins/flot/angular-flot.js', ]
                            },
                            {
                                name: 'angles',
                                files: ['js/plugins/chartJs/angles.js', 'js/plugins/chartJs/Chart.min.js']
                            },
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/dashboardInfluencer.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.profile', {
                url: "/account/:identity",
                templateUrl: "views/profile.view.html",
                data:{pageTitle:'Profile',  permissions:['all']},
                //controller:'profileCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/profile.js']
                            },
                            {
                                name: 'localytics.directives',
                                files: ['css/plugins/chosen/chosen.css','js/plugins/chosen/chosen.jquery.js','js/plugins/chosen/chosen.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.brands', {
                url: "/brands",
                templateUrl: "views/common/common-table.view.html",
                data:{
                    pageTitle:'Brands',
                    permissions:['admin']
                },
                controller:'brandsListCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/brands.js']
                            },
                            {
                                name:'oitozero.ngSweetAlert',
                                files:['js/plugins/sweetalert/sweetalert.min.js', 'js/plugins/sweetalert/angular-sweetalert.min.js', 'css/plugins/sweetalert/sweetalert.css']
                            }
                        ]);
                    }
                }
            })
            .state('app.add-brand', {
                url: "/brands/add-brand",
                templateUrl: "views/edit-brand.view.html",
                data:{
                    pageTitle:'Brands',
                    permissions:['admin']
                },
                controller:'editBrandCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/brands.js']
                            }
                        ]);
                    }
                }
            })



            .state('app.edit-brand', {
                url: "/brands/edit-brand/:identity",
                templateUrl: "views/edit-brand.view.html",
                data:{
                    pageTitle:'Brands',
                    permissions:['admin']
                },
                controller:'editBrandCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/brands.js']
                            }
                        ]);
                    }
                }
            })
            .state('settings', {
                abstract:true,
                url:'/settings',
                templateUrl:'views/common/content.html'
            })
            .state('settings.interests', {
                url:'/interests',
                templateUrl:'views/common/common-table.view.html',
                controller:'interestsListCtrl',
                data:{
                    pageTitle:'Interests',
                    permissions:['admin']
                },
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/interests.js']
                            },
                            {
                                name:'oitozero.ngSweetAlert',
                                files:['js/plugins/sweetalert/sweetalert.min.js', 'js/plugins/sweetalert/angular-sweetalert.min.js', 'css/plugins/sweetalert/sweetalert.css']
                            }
                        ]);
                    }
                }
            })
            .state('settings.edit-interest', {
                url: "/interests/edit-interest/:identity",
                templateUrl: "views/edit-interest.view.html",
                data:{
                    pageTitle:'Interest',
                    permissions:['admin']
                },
                controller:'editInterestCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/interests.js']
                            }
                        ]);
                    }
                }
            })
            .state('influencers',{
                abstract:true,
                url:'/influencers',
                templateUrl:'views/common/content.html'
            })
            .state('influencers.search', {
                url: "/search",
                templateUrl: "views/invite-influencers.view.html",
                data:{
                    pageTitle:'Search Influencers',
                    permissions:['admin', 'brand']
                },
                controller:'influencersListCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/influencers.js']
                            }
                        ]);
                    }
                }
            })
            .state('campaignsView',{
                abstract:true,
                url:'/campaigns',
                templateUrl:'views/common/content.html'
            })
            .state('campaignsView.search', {
                url: "/campaigns",
                templateUrl: "views/campaigns-search.view.html",
                data:{
                    pageTitle:'Search Campaigns',
                    permissions:['influencer']
                },
                controller:'campaignsSearchListCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaignsSearch.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.campaignViewApply', {
                url: "/campaignApply/:identity",
                templateUrl: "views/campaignViewApply.html",
                data:{pageTitle:'Campaign',  permissions:['influencer']},
                //controller:'profileCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaignViewApply.js']
                            }
                        ]);
                    }
                }
            })

            .state('app.campaignPost', {
                url: "/campaigns/post/:identity",
                templateUrl: "views/campaignsPosts.html",
                data:{
                    pageTitle:'Posts',
                    permissions:['admin', 'brand']
                },
                //controller:'campaignPostCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaignPosts.js']
                            },
                            {
                                serie: true,
                                name: 'datePicker',
                                files: ['css/plugins/datapicker/angular-datapicker.css','js/plugins/datapicker/angular-datepicker.js']
                            },
                            {
                                serie: true,
                                files: ['js/plugins/moment/moment.min.js', 'js/plugins/moment/moment-timezone.js', 'js/plugins/daterangepicker/daterangepicker.js', 'css/plugins/daterangepicker/daterangepicker-bs3.css']
                            }
                        ]);
                    }
                }
            })

            .state('app.influencerPost', {
                url: "/postCreate/:identity",
                templateUrl: "views/influencerPost.html",
                data:{pageTitle:'Post',  permissions:['influencer', 'admin']},
                //controller:'profileCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/influencerPost.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.influencerPostView', {
                url: "/postInfluencerView/:identity",
                templateUrl: "views/influencerPostView.html",
                data:{pageTitle:'Post',  permissions:['influencer', 'admin']},
                //controller:'profileCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/influencerPostView.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.campaignView', {
                url: "/campaign/:identity",
                templateUrl: "views/campaignView.html",
                data:{pageTitle:'Campaign',  permissions:['brand','admin']},
                //controller:'profileCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaignView.js']
                            }
                        ]);
                    }
                }
            })
            //.state('campaign.create.step-two', {
            //    url: "/step-two",
            //    templateUrl: "views/create-campaign/campaign-details.view.html",
            //    data:{
            //        pageTitle:'Campaigns',
            //        permissions:['admin', 'brand']
            //    },
            //    resolve: {
            //        loadPlugin: function ($ocLazyLoad) {
            //            return $ocLazyLoad.load([
            //                {
            //                    name:'inspinia', // app name for the controller proper assignment
            //                    files:['js/controllers/campaigns.js']
            //                },
            //                {
            //                    name: 'localytics.directives',
            //                    files: ['css/plugins/chosen/chosen.css','js/plugins/chosen/chosen.jquery.js','js/plugins/chosen/chosen.js']
            //                }
            //            ]);
            //        }
            //    }
            //})
            //.state('campaign.create.step-three', {
            //    url: "/step-three",
            //    templateUrl: "views/create-campaign/campaign-requirements.view.html",
            //    data:{
            //        pageTitle:'Campaigns',
            //        permissions:['admin', 'brand']
            //    },
            //    resolve: {
            //        loadPlugin: function ($ocLazyLoad) {
            //            return $ocLazyLoad.load([
            //                {
            //                    name: 'localytics.directives',
            //                    files: ['css/plugins/chosen/chosen.css','js/plugins/chosen/chosen.jquery.js','js/plugins/chosen/chosen.js']
            //                }
            //            ]);
            //        }
            //    }
            //})
            //.state('campaign.create.step-four', {
            //    url: "/step-four",
            //    templateUrl: "views/create-campaign/campaign-review.view.html",
            //    data:{
            //        pageTitle:'Campaigns',
            //        permissions:['admin', 'brand']
            //    }
            //})
            .state('campaigns',{
                abstract:true,
                url:'/campaigns',
                templateUrl:'views/common/content.html'
            })
            .state('campaigns.campaigns', {
                url:'/campaign',
                templateUrl:'views/common/common-table.view.html',
                data:{
                    pageTitle:'Campaigns',
                    permissions:['admin', 'brand']
                },
                controller:'campaignsListCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaigns.js']
                            },
                            {
                                name:'oitozero.ngSweetAlert',
                                files:['js/plugins/sweetalert/sweetalert.min.js', 'js/plugins/sweetalert/angular-sweetalert.min.js', 'css/plugins/sweetalert/sweetalert.css']
                            }
                        ]);
                    }
                }
            })
            .state('campaigns.campaignsManage', {
                url: "/manage",
                templateUrl: "views/campaignsManage.html",
                data:{pageTitle:'Campaign',  permissions:['brand']},
                controller:'campaignsManageCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaignsManage.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.add-campaign', {
                url: "/campaigns/create",
                templateUrl: "views/edit-campaign.view.html",
                data:{
                    pageTitle:'Campaigns',
                    permissions:['admin', 'brand']
                },
                controller:'editCampaignCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaigns.js']
                            },
                            {
                                name: 'localytics.directives',
                                files: ['css/plugins/chosen/chosen.css','js/plugins/chosen/chosen.jquery.js','js/plugins/chosen/chosen.js']
                            }
                        ]);
                    }
                }
            })
            .state('app.edit-campaign', {
                url: "/campaign/:identity/edit",
                templateUrl: "views/edit-campaign.view.html",
                data:{
                    pageTitle:'Campaigns',
                    permissions:['admin', 'brand']
                },
                controller:'editCampaignCtrl',
                resolve: {
                    loadPlugin: function ($ocLazyLoad) {
                        return $ocLazyLoad.load([
                            {
                                name:'inspinia', // app name for the controller proper assignment
                                files:['js/controllers/campaigns.js']
                            },
                            {
                                name: 'localytics.directives',
                                files: ['css/plugins/chosen/chosen.css','js/plugins/chosen/chosen.jquery.js','js/plugins/chosen/chosen.js']
                            }
                        ]);
                    }
                }
            })

    }])
    .run(function($rootScope, $state) {
        $rootScope.$state = $state;
    });
