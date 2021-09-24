<?php

    return [

        /*
        |--------------------------------------------------------------------------
        | Admin Menu
        |--------------------------------------------------------------------------
        */

        'administrator' => [
            'side' => [
                [
                    'name' => 'Dashboard',
                    'class' => '',
                    'iconClass' => 'icon-grid',
                    'hasChildren' => false,
                    'routeName' => 'dashboard',
                    'imgIcon' => 'dashboard.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Organisations',
                    'class' => '',
                    'iconClass' => 'icon-briefcase',
                    'hasChildren' => false,
                    'routeName' => 'admin.organisations',
                    'imgIcon' => 'hand.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Enquirers',
                    'class' => '',
                    'iconClass' => 'icon-customer',
                    'hasChildren' => false,
                    'routeName' => 'admin.customers',
                    'imgIcon' => 'customer.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Reports',
                    'class' => '',
                    'iconClass' => 'icon-graph',
                    'hasChildren' => false,
                    'routeName' => 'admin.reports',
                    'imgIcon' => 'financial.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Postcodes',
                    'class' => '',
                    'iconClass' => 'icon-cloud-upload',
                    'hasChildren' => false,
                    'routeName' => 'admin.postcodes',
                    'imgIcon' => 'map.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Notifications',
                    'class' => '',
                    'iconClass' => 'icon-cloud-upload',
                    'hasChildren' => false,
                    'routeName' => 'admin.notifications',
                    'imgIcon' => 'bell.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Activity Log',
                    'class' => '',
                    'iconClass' => 'icon-list',
                    'hasChildren' => false,
                    'routeName' => 'activitylog',
                    'imgIcon' => 'flash.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Org. Locator',
                    'class' => '',
                    'iconClass' => 'icon-target',
                    'hasChildren' => false,
                    'routeName' => 'orglocator',
                    'imgIcon' => 'car-service.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Scrum Board',
                    'class' => '',
                    'iconClass' => 'icon-scrum',
                    'hasChildren' => false,
                    'routeName' => 'scrumboard',
                    'imgIcon' => 'scrum.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Find Store',
                    'class' => '',
                    'iconClass' => 'icon-target',
                    'hasChildren' => false,
                    'routeName' => 'admin.stores',
                    'imgIcon' => 'car-service.svg',
                    'subMenus' => []
                ],
            ],
            'top' => [
                [
                    'name' => 'My Profile',
                    'class' => 'dropdown-item',
                    'iconClass' => 'el-icon-s-custom',
                    'hasChildren' => false,
                    'routeName' => 'my.profile',
                    'subMenus' => []
                ],
                [
                    'name' => 'Admin Settings',
                    'class' => '',
                    'iconClass' => 'icon-user',
                    'hasChildren' => false,
                    'routeName' => 'admin.settings',
                    'subMenus' => []
                ],
                [
                    'name' => 'User Settings',
                    'class' => '',
                    'iconClass' => 'icon-users',
                    'hasChildren' => false,
                    'routeName' => 'admin.users',
                    'imgIcon' => 'organization.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Time Settings',
                    'class' => '',
                    'iconClass' => 'el-icon-timer',
                    'hasChildren' => false,
                    'routeName' => 'admin.time.settings',
                    'subMenus' => []
                ],
                [
                    'name' => 'Roles & Permissions',
                    'class' => '',
                    'iconClass' => 'el-icon-unlock',
                    'hasChildren' => false,
                    'routeName' => 'admin.roles.permissions',
                    'subMenus' => []
                ],
                [
                    'name' => 'Escalation Settings',
                    'class' => 'dropdown-item',
                    'iconClass' => 'el-icon-setting',
                    'hasChildren' => false,
                    'routeName' => 'admin.escalation.settings',
                    'subMenus' => []
                ],
                [
                    'name' => 'Logout',
                    'class' => 'dropdown-item',
                    'iconClass' => 'el-icon-switch-button',
                    'hasChildren' => false,
                    'routeName' => 'logout',
                    'subMenus' => []
                ]
            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Organisation Menu
        |--------------------------------------------------------------------------
        */

        'organisation' => [
            'side' => [
                [
                    'name' => 'Dashboard',
                    'class' => '',
                    'iconClass' => 'icon-grid',
                    'hasChildren' => false,
                    'routeName' => 'dashboard',
                    'imgIcon' => 'dashboard.svg',
                    'subMenus' => []
                ],
            ],
            'top' => [
                [
                    'name' => 'My Profile',
                    'class' => 'dropdown-item',
                    'iconClass' => 'icon-user',
                    'hasChildren' => false,
                    'routeName' => 'profile',
                    'subMenus' => []
                ],
                [
                    'name' => 'Logout',
                    'class' => 'dropdown-item',
                    'iconClass' => 'el-icon-switch-button',
                    'hasChildren' => false,
                    'routeName' => 'logout',
                    'subMenus' => []
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | User Menu
        |--------------------------------------------------------------------------
        */

        'user' => [
            'side' => [
                [
                    'name' => 'Dashboard',
                    'class' => '',
                    'iconClass' => 'icon-grid',
                    'hasChildren' => false,
                    'routeName' => 'dashboard',
                    'imgIcon' => 'dashboard.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Organisations',
                    'class' => '',
                    'iconClass' => 'icon-briefcase',
                    'hasChildren' => false,
                    'routeName' => 'admin.organisations',
                    'imgIcon' => 'hand.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Reports',
                    'class' => '',
                    'iconClass' => 'icon-graph',
                    'hasChildren' => false,
                    'routeName' => 'admin.reports',
                    'imgIcon' => 'financial.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Postcodes',
                    'class' => '',
                    'iconClass' => 'icon-cloud-upload',
                    'hasChildren' => false,
                    'routeName' => 'admin.postcodes',
                    'imgIcon' => 'map.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Notifications',
                    'class' => '',
                    'iconClass' => 'icon-cloud-upload',
                    'hasChildren' => false,
                    'routeName' => 'admin.notifications',
                    'imgIcon' => 'bell.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Activity Log',
                    'class' => '',
                    'iconClass' => 'icon-list',
                    'hasChildren' => false,
                    'routeName' => 'activitylog',
                    'imgIcon' => 'flash.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Org. Locator',
                    'class' => '',
                    'iconClass' => 'icon-target',
                    'hasChildren' => false,
                    'routeName' => 'orglocator',
                    'imgIcon' => 'car-service.svg',
                    'subMenus' => []
                ],
            ],
            'top' => [
                [
                    'name' => 'My Profile',
                    'class' => 'dropdown-item',
                    'iconClass' => 'icon-user',
                    'hasChildren' => false,
                    'routeName' => 'profile',
                    'subMenus' => []
                ],
                [
                    'name' => 'User Settings',
                    'class' => '',
                    'iconClass' => 'icon-users',
                    'hasChildren' => false,
                    'routeName' => 'admin.users',
                    'imgIcon' => 'organization.svg',
                    'subMenus' => []
                ],
                [
                    'name' => 'Logout',
                    'class' => 'dropdown-item',
                    'iconClass' => 'el-icon-switch-button',
                    'hasChildren' => false,
                    'routeName' => 'logout',
                    'subMenus' => []
                ],
            ],
        ],

    ];
