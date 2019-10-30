module.exports = function ($stateProvider) {
    $stateProvider
        .state('addressbook', {
            url: '/addressbook',
            templateUrl: '/app/engines/addressbook/main.html',
            controller: function ($state) {
                if ($state.current.name == 'addressbook') {
                    $state.go('addressbook.contacts');
                }
            }
        })
        .state('addressbook.contacts', {
            url: '/contacts',
            templateUrl: '/app/engines/addressbook/contacts/views/index.html',
            controller: 'ContactsController',
            resolve: {
                contacts: function (Restangular) {
                    return Restangular.one('address-book/index-contacts').get();
                },
                groups: function (Restangular) {
                    return Restangular.one('address-book/index-groups').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/addressbook/contacts/controllers/ContactsController.js'
                        ]
                    });
                }]
            }
        })
        .state('addressbook.groups', {
            url: '/groups',
            templateUrl: '/app/engines/addressbook/groups/views/index.html',
            controller: 'GroupsController',
            resolve: {
                groups: function (Restangular) {
                    return Restangular.one('address-book/index-groups').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/addressbook/groups/controllers/GroupsController.js'
                        ]
                    });
                }]
            }
        })
        .state('addressbook.import', {
            url: '/import',
            templateUrl: '/app/engines/addressbook/import/views/import-contact.html',
            controller: 'ContactsImportController',
            resolve: {
                groups: function (Restangular) {
                    return Restangular.one('address-book/index-groups').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/addressbook/import/controllers/ContactsImportController.js'
                        ]
                    });
                }]
            }
        })
        .state('addressbook.export', {
            url: '/export',
            templateUrl: '/app/engines/addressbook/export/views/export-contacts.html',
            controller: 'ContactsExportController',
            resolve: {
                groups: function (Restangular) {
                    return Restangular.one('address-book/index-groups').get();
                },
                contacts: function (Restangular) {
                    return Restangular.one('address-book/index-contacts').get();
                },
                deps: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load({
                        name: 'callburnApp',
                        files: [
                            '/app/engines/addressbook/export/controllers/ContactsExportController.js'
                        ]
                    });
                }]
            }
        })
};