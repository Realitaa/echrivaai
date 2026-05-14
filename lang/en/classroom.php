<?php

return [
    'title' => 'Classroom Management',
    'description' => 'Manage classrooms available on the platform.',
    'search' => 'Search name or classroom code...',
    'empty' => 'No classrooms found.',
    'lookup' => 'Select Teacher...',
    'lookupEmpty' => 'No teachers found',
    'lookupPlaceholder' => 'Search teacher...',
    'table' => [
        'code' => 'Code',
        'name' => 'Class Name',
        'teacher' => 'Teacher',
        'created' => 'Created',
        'actions' => 'Actions',
        'openMenu' => 'Open menu',
    ],
    'actions' => [
        'viewStudents' => 'View Students',
        'delete' => 'Delete',
    ],
    'enrollmentDialog' => [
        'title' => 'Student Data - :name',
        'description' => 'List of students enrolled in this class.',
        'loading' => 'Loading data...',
        'empty' => 'No students in this class.',
    ],
    'deleteDialog' => [
        'title' => 'Are you sure?',
        'description' => 'This action cannot be undone. This will permanently delete this class. Note: Classes with active tasks cannot be deleted.',
        'cancel' => 'Cancel',
        'confirm' => 'Delete',
    ],
];
