# Spesifikasi Teknis: Student Submission & AI Feedback System

## 1. Konteks Proyek
**Project Name:** Echrivaai (AI-Powered Classroom Management)
**Tech Stack:** Laravel 13, Inertia.js (Vue.js), Tailwind CSS 4, Laravel 13 AI SDK.
**Fitur Utama:** Sistem pengumpulan tugas (submission) oleh siswa yang secara otomatis diproses oleh AI untuk memberikan feedback naratif dan skor berdasarkan rubrik, dengan mempertimbangkan histori submission sebelumnya (progress tracking).

---

## 2. Instruksi Alur Kerja untuk AI Agent
Ikuti langkah-langkah berikut secara berurutan:
1.  **Generate Test Code:** Buat file `tests/Feature/Student/SubmissionTest.php` berdasarkan Test Suite di bawah. Gunakan standar Testing Laravel 13.
2.  **User Review:** Berikan kode tes tersebut kepada user untuk ditinjau.
3.  **Backend Implementation:** Setelah user menyetujui, lanjutkan dengan mengimplementasikan Controller, Service, FormRequest, dan logic lainnya untuk membuat tes tersebut **PASSED**.
4.  **Standard SDK:** Pastikan implementasi AI menggunakan dokumentasi resmi **Laravel 13 AI SDK** (https://laravel.com/docs/13.x/ai-sdk).

---

## 3. Test Suite: Student/SubmissionTest

### A. Viewing & Access (Index)
1.  `student can view task detail inside an enrolled classroom`
2.  `student cannot view task detail inside an unenrolled classroom` (403/404)
3.  `student can see the list of their submission attempts (history) for a task`
4.  `student can view task detail and history even AFTER the deadline`
5.  `student cannot view an UNPUBLISHED task` (Draft milik Teacher)

### B. Submission Logic (Store)
6.  `student can make a submission for a task inside an enrolled classroom`
7.  `student can make multiple submission attempts`
8.  `student can make a submission EXACTLY at the deadline` (Boundary test `<=`)
9.  `student cannot make a submission past task deadline`
10. `student cannot submit using an UNOWNED temporary file ID` (Validation Error 422 - `file` key)
11. `student cannot submit using a NON-EXISTENT temporary file ID` (Validation Error 422)
12. `student cannot make a new submission while the previous one is still being processed by AI` (Concurrency lock)
13. `submission creation triggers AI feedback service immediately` (Tanpa draft mode)

### C. AJAX Detail & AI Interaction (Show)
14. `student can view specific submission details via AJAX` (accordion detail)
15. `student can view the "Processing" state if AI feedback is not yet ready`
16. `student can view AI feedback and rubric scores (AI score) once generated`
17. `student receives AI feedback that compares current attempt with previous attempt progress` (Logic: "Lebih baik", "Menurun", dll)
18. `student can view teacher score and feedback alongside AI feedback if available` (Data `score_teacher` & `feedback_teacher` bersifat opsional)
19. `student cannot view detail of other student's submission`

### D. Global RBAC
20. `guest cannot access any student submission routes`
21. `non-student (teacher/admin) cannot access student submission routes`
22. `student cannot access teacher-specific submission management routes`

---

## 4. Hal Penting untuk Diperhatikan (Edge Cases & Logic)

1.  **AI Context Memory:** Saat melakukan submission baru, AI Service harus menerima konteks/histori dari submission sebelumnya untuk memberikan perbandingan progress yang akurat.
2.  **File Integrity:** Validasi `temporary_file_id` harus memastikan file tersebut ada dan milik `user_id` yang sedang login. Setelah valid, pindahkan file dari storage sementara ke storage permanen.
3.  **Rubric Consistency:** AI harus mengisi skor pada tiap kriteria yang didefinisikan di `TaskRubric` ke dalam tabel `submission_rubric_scores`.
4.  **Teacher Override:** Sistem harus mendukung tampilan berdampingan antara feedback otomatis (AI) dan feedback manual (Teacher) jika pengajar memutuskan untuk memberikan penilaian manual.
5.  **Mocking AI SDK:** Saat membuat tes, gunakan fitur mocking dari Laravel 13 AI SDK agar tidak melakukan request nyata ke API LLM.
6.  **Progress Labels:** Backend harus mengirimkan data perbandingan (numerik atau string) yang konsisten agar frontend dapat melakukan switch case untuk label progress.

---

**Link Dokumentasi Utama:**
- Laravel AI SDK: [https://laravel.com/docs/13.x/ai-sdk](https://laravel.com/docs/13.x/ai-sdk)
