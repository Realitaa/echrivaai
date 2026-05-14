<?php

return [
    'title' => 'User Management',
    'description' => 'Manage users registered on the platform.',
    'search' => 'Search name or email...',
    'empty' => 'No users found.',
    'filter' => [
        'roles' => [
            'all' => 'All Roles',
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student',
        ],
        'statuses' => [
            'all' => 'All Statuses',
            'approved' => 'Approved',
            'unapproved' => 'Unapproved',
        ],
    ],
    'table' => [
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'status' => 'Status',
        'joined' => 'Joined',
        'actions' => 'Actions',
    ],
    'actions' => [
        'revoke' => 'Revoke Approval',
        'edit' => 'Edit',
        'approve' => 'Approve',
        'delete' => 'Delete',
    ],
    'createDialog' => [
        'title' => 'Add User',
        'description' => 'Add a new user to the system.',
        'create' => 'Add User',
        'creating' => 'Saving...',
        'close' => 'Cancel',
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'role' => 'Role',
        'roles' => [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student',
        ],
    ],
    'editDialog' => [
        'title' => 'Edit User',
        'description' => 'Modify user data here.',
        'update' => 'Save Changes',
        'updating' => 'Saving...',
        'close' => 'Cancel',
        'passwordNote' => 'Leave blank if not changing',
    ],
    'deleteDialog' => [
        'title' => 'Are you sure?',
        'description' => 'This action cannot be undone. This will permanently delete the user from the system.',
        'cancel' => 'Cancel',
        'confirm' => 'Delete',
    ],
];
