<?php

return [
    'title' => 'Manajemen Pengguna',
    'description' => 'Kelola pengguna yang terdaftar di platform.',
    'search' => 'Cari nama atau email...',
    'empty' => 'Tidak ada pengguna ditemukan.',
    'filter' => [
        'roles' => [
            'all' => 'Semua Peran',
            'admin' => 'Admin',
            'teacher' => 'Guru',
            'student' => 'Siswa',
        ],
        'statuses' => [
            'all' => 'Semua Status',
            'approved' => 'Disetujui',
            'unapproved' => 'Belum Disetujui',
        ],
    ],
    'table' => [
        'name' => 'Nama',
        'email' => 'Email',
        'role' => 'Peran',
        'status' => 'Status',
        'joined' => 'Bergabung',
        'actions' => 'Aksi',
    ],
    'actions' => [
        'revoke' => 'Cabut Approval',
        'edit' => 'Edit',
        'approve' => 'Setujui',
        'delete' => 'Hapus',
    ],
    'createDialog' => [
        'title' => 'Tambah Pengguna',
        'description' => 'Tambahkan pengguna baru ke sistem.',
        'create' => 'Tambah Pengguna',
        'creating' => 'Menyimpan...',
        'close' => 'Batal',
        'name' => 'Nama',
        'email' => 'Email',
        'password' => 'Password',
        'role' => 'Peran',
        'roles' => [
            'admin' => 'Admin',
            'teacher' => 'Guru',
            'student' => 'Siswa',
        ],
    ],
    'editDialog' => [
        'title' => 'Edit Pengguna',
        'description' => 'Ubah data pengguna di sini.',
        'update' => 'Simpan Perubahan',
        'updating' => 'Menyimpan...',
        'close' => 'Batal',
        'passwordNote' => 'Kosongkan jika tidak diubah',
    ],
    'deleteDialog' => [
        'title' => 'Apakah Anda yakin?',
        'description' => 'Tindakan ini tidak dapat dibatalkan. Ini akan menghapus pengguna secara permanen dari sistem.',
        'cancel' => 'Batal',
        'confirm' => 'Hapus',
    ],
];
