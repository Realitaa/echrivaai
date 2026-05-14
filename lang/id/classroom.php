<?php

return [
    // Admin
    'title' => 'Manajemen Kelas',
    'description' => 'Kelola kelas yang tersedia di platform.',
    'search' => 'Cari nama atau kode kelas...',
    'empty' => 'Tidak ada kelas ditemukan.',
    'lookup' => 'Pilih Guru...',
    'lookupEmpty' => 'Tidak ada guru ditemukan',
    'lookupPlaceholder' => 'Cari guru...',
    'table' => [
        'code' => 'Kode',
        'name' => 'Nama Kelas',
        'teacher' => 'Guru',
        'created' => 'Dibuat',
        'actions' => 'Aksi',
        'openMenu' => 'Buka menu',
    ],
    'actions' => [
        'viewStudents' => 'Lihat Siswa',
        'delete' => 'Hapus',
    ],
    'enrollmentDialog' => [
        'title' => 'Data Siswa - :name',
        'description' => 'Daftar siswa yang terdaftar di kelas ini.',
        'loading' => 'Memuat data...',
        'empty' => 'Tidak ada siswa di kelas ini.',
    ],
    'deleteDialog' => [
        'title' => 'Apakah Anda yakin?',
        'description' => 'Tindakan ini tidak dapat dibatalkan. Ini akan menghapus kelas ini secara permanen. Catatan: Kelas yang memiliki tugas aktif tidak dapat dihapus.',
        'cancel' => 'Batal',
        'confirm' => 'Hapus',
    ],

    // Teacher
    'teacher' => [
        'title' => 'Kelasku',
        'list' => 'Daftar Kelas',
        'description' => 'Kelola ruang kelas dan pembelajaran Anda.',
        'new' => 'Kelas Baru',
        'empty' => 'Anda belum memiliki kelas.',
        'createFirst' => 'Buat kelas pertama Anda',
        'form' => [
            'createTitle' => 'Buat Kelas Baru',
            'editTitle' => 'Edit Kelas',
            'createDesc' => 'Tambahkan kelas baru ke daftar kelas Anda. Kode kelas akan dibuat secara otomatis.',
            'editDesc' => 'Ubah informasi kelas Anda di sini.',
            'name' => 'Nama Kelas',
            'namePlaceholder' => 'Contoh: Matematika Dasar',
            'description' => 'Deskripsi (Opsional)',
            'descriptionPlaceholder' => 'Masukkan deskripsi kelas...',
            'save' => 'Simpan',
            'saving' => 'Menyimpan...',
            'cancel' => 'Batal',
        ],
        'deleteDialog' => [
            'title' => 'Hapus Kelas?',
            'description' => 'Tindakan ini tidak dapat dibatalkan. Kelas yang sudah memiliki tugas aktif tidak dapat dihapus.',
            'cancel' => 'Batal',
            'confirm' => 'Ya, Hapus',
        ],
    ],
];
