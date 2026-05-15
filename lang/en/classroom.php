<?php

return [
    // Admin
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

    // Teacher
    'teacher' => [
        'title' => 'My Class',
        'list' => 'Class List',
        'description' => 'Manage your classrooms and learning.',
        'new' => 'New Class',
        'empty' => 'You don\'t have any classes yet.',
        'createFirst' => 'Create your first class',
        'form' => [
            'createTitle' => 'Create New Class',
            'editTitle' => 'Edit Class',
            'createDesc' => 'Add a new class to your class list. Class code will be generated automatically.',
            'editDesc' => 'Change your class information here.',
            'name' => 'Class Name',
            'namePlaceholder' => 'e.g., Basic Mathematics',
            'description' => 'Description (Optional)',
            'descriptionPlaceholder' => 'Enter class description...',
            'save' => 'Save',
            'saving' => 'Saving...',
            'cancel' => 'Cancel',
        ],
        'deleteDialog' => [
            'title' => 'Delete Class?',
            'description' => 'This action cannot be undone. Classes with active tasks cannot be deleted.',
            'cancel' => 'Cancel',
            'confirm' => 'Yes, Delete',
        ],
        'detailTitle' => 'Class Detail :name',
        'stats' => [
            'students' => 'Total Students',
            'tasks' => 'Active Tasks',
        ],
        'studentList' => 'Enrolled Students List',
        'table' => [
            'studentName' => 'Student Name',
            'email' => 'Email',
            'empty' => 'No students enrolled in this class yet.',
        ],
    ],

    // Student
    'student' => [
        'title' => 'My Class',
        'listTitle' => 'Class List',
        'description' => 'Manage your classrooms and learning.',
        'joinClass' => 'Join Class',
        'empty' => 'You haven\'t joined any classes yet.',
        'detailTitle' => 'Class Detail :name',
        'card' => [
            'code' => 'Code',
            'copyCode' => 'Copy classroom code',
            'copySuccess' => 'Classroom code :code copied successfully!',
            'teacher' => 'Teacher',
            'noDescription' => 'No description for this class.',
            'viewDetail' => 'View Detail',
            'editClass' => 'Edit Class',
            'delete' => 'Delete',
        ],
        'enrollDialog' => [
            'title' => 'Join Class',
            'description' => 'Enter the class code provided by your teacher.',
            'codeLabel' => 'Class Code',
            'codePlaceholder' => 'e.g., 123456',
            'cancel' => 'Cancel',
            'joining' => 'Joining...',
            'join' => 'Join',
        ],
    ],
];
