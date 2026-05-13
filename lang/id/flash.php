<?php

return [
  "admin" => [
    "classroom" => [
      "deleted" => "Kelas berhasil dihapus.",
      "activeTasks" => "Kelas tidak dapat dihapus karena memiliki tugas yang aktif.",
    ],
    "task" => [
      "deleted" => "Tugas berhasil dihapus.",
      "activeTasks" => "Tugas tidak dapat dihapus karena sudah memiliki pengumpulan.",
    ],
    "user" => [
      "created" => "Pengguna berhasil dibuat.",
      "updated" => "Pengguna berhasil diperbarui.",
      "deleted" => "Pengguna berhasil dihapus.",
      "approve" => "Pendaftaran guru berhasil disetujui.",
    ]
  ],
  "setting" => [
    "profile" => [
      "updated" => "Profil berhasil diperbarui.",
    ],
    "security" => [
      "updated" => "Kata sandi berhasil diperbarui.",
    ]
  ],
  "student" => [
    "classroom" => [
      "enrolled" => "Berhasil masuk ke kelas!",
      "alreadyEnrolled" => "Anda sudah terdaftar di kelas ini!",
    ],
    "submission" => [
      "deadlineOver" => "Batas waktu untuk tugas ini telah berakhir.",
      "unfinishedPrevious" => "Pengumpulan Anda sebelumnya masih diproses. Harap tunggu.",
      "created" => "Pengumpulan berhasil dibuat! Harap tunggu sementara AI memproses umpan balik Anda."
    ]
  ],
  "teacher" => [
    "classroom" => [
      "created" => "Kelas berhasil dibuat!",
      "updated" => "Kelas berhasil diperbarui!",
      "deleted" => "Kelas berhasil dihapus!",
    ],
    "task" => [
      "created" => "Tugas berhasil dibuat!",
      "updated" => "Tugas berhasil diperbarui!",
      "deleted" => "Tugas berhasil dihapus!",
      "publish" => "Tugas berhasil diterbitkan!",
      "unpublish" => "Tugas berhasil batal diterbitkan!",
      "published" => "Anda tidak dapat :action tugas yang sudah diterbitkan!",
      "alreadyPublished" => "Tugas sudah diterbitkan!",
      "hasSubmission" => "Tugas tidak dapat di-:action karena sudah memiliki pengumpulan!",
      "notPublished" => "Tugas belum diterbitkan!"
    ]
  ]
];
