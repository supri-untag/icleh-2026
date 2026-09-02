# PRD — ICLEH 2026 Conference Management System

## 1. Ringkasan Produk

**Nama produk:** ICLEH 2026 Conference Management System  
**Penyelenggara:** Fakultas Hukum, Universitas 17 Agustus 1945 Semarang  
**Event:** 5th International Conference on Law, Economy and Health (ICLEH) and Call for Papers  
**Tema:** *“Reimagining Law, Economy and Health in the Age of Artificial Intelligence: Advancing Human Dignity, Justice and Sustainable Governance”*  
**Tanggal konferensi:** 11–12 November 2026  
**Waktu:** 08.00–16.00 WIB  
**Lokasi:** Hybrid, Gedung Pemuda Fakultas Hukum Universitas 17 Agustus 1945 Semarang

Sistem dibuat sebagai **satu aplikasi terintegrasi** yang mencakup:

1. Website publik konferensi.
2. Registrasi peserta/presenter.
3. Akun peserta/presenter.
4. Submission abstrak dan full paper.
5. Verifikasi pembayaran.
6. Review paper.
7. Letter of Acceptance (LoA).
8. Penjadwalan parallel session/chamber.
9. Presensi.
10. Sertifikat.
11. Pengumuman dan notifikasi.
12. Dashboard panitia/admin.
13. Pengelolaan speaker, program, timeline, biaya, konten website, dan dokumen.
14. Pelaporan dan ekspor data.

Referensi gaya struktur website publik: **MINDS 2026 — Faculty of Mathematics and Natural Sciences, Universitas Indonesia** (`conference.sci-ui.id/minds/`), tetapi identitas visual, konten, fitur registrasi, submission, dashboard peserta, serta dashboard admin dibuat khusus ICLEH 2026.

---

# 2. Tujuan

Sistem harus menggantikan alur yang tersebar di website, Google Form, email, spreadsheet, dan submission system terpisah menjadi satu platform.

Tujuan utama:

- Pengunjung memperoleh seluruh informasi ICLEH 2026 dari satu website.
- Peserta mendaftar dan memiliki akun konferensi.
- Presenter dapat submit abstrak, revisi, dan full paper.
- Panitia dapat memverifikasi registrasi dan pembayaran.
- Reviewer dapat melakukan review melalui sistem.
- Panitia dapat menerbitkan LoA.
- Panitia dapat menentukan chamber, jadwal, moderator, operator, dan presenter.
- Peserta dapat mengunduh dokumen resmi dari dashboard.
- Admin dapat mengubah konten website tanpa mengubah kode.
- Data konferensi dapat diekspor ke Excel/CSV/PDF sesuai kebutuhan panitia.

---

# 3. Identitas Visual

Desain mengikuti materi poster ICLEH 2026.

## 3.1 Warna utama

Gunakan token warna agar mudah diganti:

```css
--icleh-red: #C60000;
--icleh-red-dark: #8F0000;
--icleh-red-light: #F02A2A;
--icleh-gold: #E2B13C;
--icleh-gold-light: #F5D873;
--icleh-black: #161616;
--icleh-white: #FFFFFF;
--icleh-gray: #F5F5F5;
--icleh-gray-dark: #525252;
```

Karakter visual:

- merah ICLEH sebagai primary color;
- emas untuk accent/highlight;
- putih/abu terang sebagai background;
- elemen hitam untuk typography;
- gradient merah–emas boleh digunakan secara terbatas pada hero, section divider, badge, dan CTA;
- hindari tampilan terlalu ramai seperti poster; website harus tetap modern, bersih, akademik, dan mudah dibaca.

## 3.2 Branding

Asset logo/foto akan diambil dari file project yang diberikan kemudian.

Hero harus mempunyai:

- 5th ICLEH 2026;
- International Conference on Law, Economy, and Health;
- tema konferensi;
- tanggal 11–12 November 2026;
- lokasi;
- tombol **Register Now**;
- tombol **Submit Abstract**;
- tombol **View Program**.

---

# 4. Pengguna dan Hak Akses

Gunakan Role-Based Access Control (RBAC).

## 4.1 Public

Tanpa login:

- melihat Home;
- About;
- Speakers;
- Topics/Scopes;
- Important Dates;
- Registration Fee;
- Program;
- Guide for Authors;
- Publication;
- Venue;
- Contact;
- FAQ;
- Announcement;
- download template;
- registrasi/login.

## 4.2 Participant

Peserta non-presenter:

- profil;
- status registrasi;
- upload bukti pembayaran;
- status pembayaran;
- invoice/receipt jika tersedia;
- melihat agenda;
- QR/presensi;
- download certificate;
- notifikasi.

## 4.3 Presenter/Author

Semua hak Participant +:

- membuat submission;
- menambah co-author;
- memilih topic;
- upload abstract;
- melihat hasil review;
- mengirim revisi;
- mengunduh LoA;
- upload full paper;
- memilih output publikasi jika nanti diaktifkan;
- melihat chamber/jadwal presentasi;
- download presenter certificate.

## 4.4 Reviewer

- melihat assignment review;
- blind-review manuscript;
- memberi skor;
- komentar untuk author;
- komentar internal editor;
- recommendation:
  - Accept
  - Minor Revision
  - Major Revision
  - Reject
- melihat histori review.

## 4.5 Scientific Committee / Editor

- screening submission;
- assign reviewer;
- menerima/menolak hasil review;
- menetapkan status final;
- request revision;
- approve abstract;
- approve full paper;
- menetapkan kandidat best paper;
- export submission.

## 4.6 Finance

- melihat registrasi;
- verifikasi pembayaran;
- reject pembayaran dengan alasan;
- mengubah status payment;
- export pembayaran;
- membuat rekap pendapatan;
- mengelola kategori fee jika diizinkan.

## 4.7 Event / Program Committee

- membuat chamber;
- menjadwalkan presenter;
- assign moderator/operator;
- mengelola rundown;
- mengelola attendance;
- mencetak daftar hadir.

## 4.8 Admin / Super Admin

Semua hak, termasuk:

- users;
- roles/permissions;
- content CMS;
- configuration;
- conference;
- registration;
- submission;
- payment;
- review;
- program;
- attendance;
- LoA;
- certificate;
- email templates;
- audit log.

---

# 5. Website Publik

Struktur public site mengadaptasi pola website konferensi MINDS 2026 tetapi dengan konten ICLEH.

## 5.1 Navigation

Desktop sticky navigation:

- Home
- About
- Speakers
- Topics
- Important Dates
- Registration
- Guide for Authors
- Program
- Publication
- Venue
- Contact
- Login
- **Register Now** (CTA)

Mobile menggunakan offcanvas/hamburger.

## 5.2 Home

Urutan section:

1. Hero
2. Conference countdown
3. About ICLEH
4. Conference highlights
5. Keynote Speakers
6. Speakers
7. Conference Topics
8. Important Dates
9. Registration Fee
10. Publication Opportunities
11. Conference Program preview
12. Venue
13. Partners/Sponsors
14. CTA Register
15. Contact
16. Footer

Semua section yang bersifat informasi harus dapat dikelola dari admin CMS.

---

# 6. Konten Konferensi

## 6.1 Tema

**REIMAGINING LAW, ECONOMY AND HEALTH IN THE AGE OF ARTIFICIAL INTELLIGENCE: ADVANCING HUMAN DIGNITY, JUSTICE AND SUSTAINABLE GOVERNANCE**

## 6.2 Topics / Conference Scopes

### 1. AI, Constitutionalism and Governance
- AI governance
- constitutional law
- public policy
- digital government
- regulatory innovation
- rule of law

### 2. Human Rights, Human Dignity and Digital Society
- human rights
- privacy
- personal data protection
- digital citizenship
- equality
- digital inclusion

### 3. AI, Justice and the Future of Legal Systems
- law enforcement
- judiciary
- legal technology
- cybercrime
- digital evidence
- dispute resolution

### 4. Digital Economy, Business and Sustainable Development
- fintech
- digital trade
- taxation
- blockchain
- ESG
- sustainable economy

### 5. AI, Health and Human Well-Being
- medical law
- health technology
- telemedicine
- patient safety
- bioethics
- public health

### 6. Cybersecurity, Data Governance and Digital Sovereignty
- cybersecurity
- cyber resilience
- critical infrastructure
- data governance
- digital sovereignty
- cross-border data flow

### 7. Ethics, Responsible Innovation and the Future of Humanity
- AI ethics
- algorithmic accountability
- responsible innovation
- philosophy of technology
- future society

### 8. Pancasila, Global Justice and Human-Centered Governance
- Pancasila studies
- constitutional values
- comparative governance
- global justice
- Global South perspective
- sustainable governance

---

# 7. Jadwal Penting

Konfigurasi awal:

| Kegiatan | Tanggal |
|---|---|
| Registration | 14 September – 14 October 2026 |
| Abstract Submission | 14 September – 14 October 2026 |
| Conference | 11–12 November 2026 |
| Full Paper Submission | 16–27 November 2026 |

Tanggal wajib disimpan di database/config admin, bukan hardcoded di Blade/frontend.

Admin dapat:

- menambah milestone;
- mengubah tanggal;
- menentukan apakah milestone tampil di website;
- memberi status `upcoming`, `open`, `closed`, `completed`.

---

# 8. Registration Fee

Konfigurasi awal berdasarkan proposal:

| Category | Fee |
|---|---:|
| Internal Participant / Student | Rp300.000 |
| General Participant | Rp450.000 |
| Presenter — Online / Offline + ISBN Proceedings | Rp1.250.000 |

Publication notes:

- Selected papers may be considered for Scopus Q2 journal publication.
- Reward publication for 2 selected presenters in SINTA 3 and SINTA 4 journals.

Semua fee harus configurable dari admin.

Data kategori fee:

- uuid
- conference_id
- name
- description
- participant_type
- attendance_mode
- amount
- currency
- active
- quota nullable
- registration_start
- registration_end

---

# 9. Alur Registrasi

## 9.1 Register Account

Form minimal:

- full name
- email
- WhatsApp
- institution
- country
- password
- password confirmation
- consent/privacy checkbox

Email harus unik.

Setelah register:

1. email verification;
2. login;
3. complete profile;
4. pilih participation type.

## 9.2 Participant Registration

Peserta memilih:

- Internal/Student
- General Participant
- Presenter

Jika Presenter:

- attendance mode:
  - Offline
  - Online

Student/Internal dapat diminta upload:

- student card / proof of status.

## 9.3 Status Registrasi

State:

```text
DRAFT
→ REGISTERED
→ WAITING_PAYMENT
→ PAYMENT_SUBMITTED
→ PAYMENT_VERIFIED
→ CONFIRMED
→ ATTENDED
→ COMPLETED
```

Additional terminal states:

```text
PAYMENT_REJECTED
CANCELLED
```

---

# 10. Payment Workflow

Tahap pertama menggunakan manual transfer + upload proof. Arsitektur harus mudah dikembangkan ke payment gateway.

## Participant

1. sistem menampilkan account payment;
2. participant upload proof;
3. participant melihat status.

## Finance/Admin

1. membuka pending payment;
2. melihat participant, amount, fee category dan proof;
3. Verify / Reject;
4. jika Reject wajib mengisi reason;
5. jika Verify:
   - status registrasi diperbarui;
   - kirim notification;
   - payment audit trail tersimpan.

Payment fields:

- uuid
- registration_id
- payment_code
- method
- amount
- currency
- proof_file
- paid_at
- submitted_at
- verified_at
- verified_by
- status
- rejection_reason
- notes

---

# 11. Submission Workflow

Presenter hanya dapat membuat submission setelah minimal melakukan registrasi. Aturan apakah payment harus verified sebelum submit dibuat configurable.

## 11.1 Submission form

- title
- topic
- abstract text optional
- keywords
- corresponding author
- authors/co-authors
- affiliations
- country
- file abstract
- notes

## 11.2 Co-Author

Author data:

- name
- email
- affiliation
- country
- corresponding_author bool
- presenter bool
- order

## 11.3 Submission State

```text
DRAFT
→ ABSTRACT_SUBMITTED
→ SCREENING
→ UNDER_REVIEW
→ REVISION_REQUIRED
→ REVISION_SUBMITTED
→ ABSTRACT_ACCEPTED
→ ABSTRACT_REJECTED
```

Setelah abstract accepted:

```text
ABSTRACT_ACCEPTED
→ LOA_ISSUED
→ FULL_PAPER_SUBMITTED
→ FULL_PAPER_REVIEW
→ FULL_PAPER_REVISION_REQUIRED
→ FULL_PAPER_ACCEPTED
→ SCHEDULED
→ PRESENTED
→ COMPLETED
```

Histori setiap perubahan status wajib disimpan.

---

# 12. Review System

## 12.1 Assignment

Scientific Committee memilih reviewer.

Support:

- single reviewer;
- multiple reviewers;
- due date;
- blind review.

## 12.2 Review Form

Score configurable, default:

- Relevance to conference theme
- Originality
- Research clarity
- Methodology
- Discussion/findings
- Academic contribution
- Quality of writing

Setiap item skala 1–5.

Fields tambahan:

- comments for author;
- confidential comments for editor;
- recommendation;
- review attachment optional.

---

# 13. Letter of Acceptance (LoA)

LoA dibuat setelah status **ABSTRACT_ACCEPTED**.

Fitur:

- template dapat dikelola admin;
- nomor surat otomatis/manual;
- QR verification;
- PDF;
- issued date;
- signer;
- digital signature image optional.

Dashboard peserta menyediakan tombol:

**Download Letter of Acceptance**

Public page:

`/verify/loa/{code}`

Menampilkan valid/tidak, participant, title, conference, dan issue date.

---

# 14. Full Paper

Setelah abstract accepted:

- presenter upload full paper;
- versioning;
- admin/reviewer dapat request revision;
- author mengupload versi baru tanpa menghapus file lama;
- final manuscript ditandai oleh editor.

File record menyimpan:

- version;
- original_filename;
- storage_path;
- mime;
- size;
- uploaded_by;
- uploaded_at.

---

# 15. Program & Parallel Session

Admin dapat membuat:

## Chamber

Contoh:

- Chamber 1
- Chamber 2
- Chamber 3

Data:

- name;
- room;
- meeting URL optional;
- capacity;
- operator;
- moderator.

## Schedule

- conference_day;
- session;
- start_time;
- end_time;
- type:
  - Registration
  - Opening
  - Keynote
  - Plenary
  - Break
  - Parallel
  - Award
  - Closing
- title;
- speaker/submission;
- chamber;
- moderator;
- operator;
- notes.

Program publik diambil dari database.

Presenter melihat:

- date;
- time;
- chamber;
- room/Zoom;
- moderator;
- presentation order.

---

# 16. Speaker Management

Initial list dari proposal:

### Keynote Speaker
- Rector, Universitas 17 Agustus 1945 Semarang
- Dean, Faculty of Law, Universitas 17 Agustus 1945 Semarang

### Speakers
1. Prof. Stefan Koos — Universität der Bundeswehr, Germany
2. Prof. Kumaralingam Amirthalingam — National University of Singapore
3. Prof. Albert LEE — Chinese University of Hong Kong
4. Prof. Dr. Anggraeni Endah Kusumaningrum, S.H., M.Hum — Universitas 17 Agustus 1945 Semarang
5. Siti Farahiya, PhD — Universiti Kebangsaan Malaysia
6. Dr. Eugenia Brandao da Silva, S.H., M.H — Universidade Oriental Timor Lorosa'e
7. Dr. Ahmed Kheir Osman, LL.B., LL.M — Somali National University
8. Jeremy Balang — MahWengKwai & Associates, Malaysia
9. Jerry G Tambun, S.H., LLB., LLM., SJD — ICHLaS

Speaker admin fields:

- type;
- name;
- title;
- affiliation;
- country;
- biography;
- topic/title;
- photo;
- attendance mode;
- display order;
- active.

---

# 17. Attendance

Support QR.

Participant dashboard:

- **My QR Code**

Admin/operator:

- scan QR;
- manual check-in;
- filter by day/session.

Attendance data:

- participant;
- date;
- session;
- check-in;
- check-out optional;
- recorded_by;
- method.

---

# 18. Certificates

Certificate type:

- Participant
- Presenter
- Speaker
- Keynote Speaker
- Moderator
- Reviewer
- Committee
- Best Paper

Features:

- configurable template;
- certificate number;
- QR verification;
- PDF;
- automatic name/title;
- eligible only when business rule satisfied.

Public verification:

`/verify/certificate/{code}`

---

# 19. Best Paper / Award

Scientific committee can:

- nominate submission;
- input score;
- set award category;
- select winner;
- publish result.

Awards appear on website only after admin publishes.

---

# 20. Notification

Channels phase 1:

- in-app;
- email.

Events:

- account verification;
- registration successful;
- payment submitted;
- payment verified/rejected;
- abstract submitted;
- review result;
- revision requested;
- abstract accepted/rejected;
- LoA issued;
- full paper reminder;
- schedule published/changed;
- certificate available.

Prepare abstraction for WhatsApp integration later.

All email templates managed by admin.

---

# 21. Participant Dashboard

Sidebar/menu:

1. Dashboard
2. My Profile
3. Registration
4. Payment
5. My Submission
6. Review / Revision
7. Full Paper
8. Letter of Acceptance
9. Conference Program
10. Attendance / QR
11. Certificate
12. Notifications
13. Help

Dashboard cards:

- Registration status
- Payment status
- Submission status
- LoA status
- Presentation schedule
- Certificate status

Timeline component shows complete participant journey.

---

# 22. Admin Dashboard

Template admin akan diberikan di project dan **wajib digunakan**, jangan mengganti dengan template admin lain.

Dashboard:

- total registered;
- total verified;
- participant vs presenter;
- internal/student/general;
- online/offline presenters;
- pending payments;
- abstracts submitted;
- under review;
- accepted;
- rejected;
- full papers;
- presenters per topic;
- chamber capacity;
- attendance;
- revenue;
- recent activity.

Menus:

### Dashboard

### Conference
- Conference Settings
- Important Dates
- Registration Fees
- Topics
- Speakers
- Venue
- Partners/Sponsors
- FAQ
- Announcements

### Participants
- All Participants
- Registrations
- Payments
- Attendance

### Submissions
- Abstracts
- Full Papers
- Review Assignments
- Review Results
- Revision
- Accepted Papers
- Best Paper

### Program
- Days
- Sessions
- Chambers
- Schedule
- Moderators
- Operators

### Documents
- LoA
- Certificates
- Templates
- Verification

### CMS
- Homepage
- Pages
- Navigation
- Media
- Contact

### Reports
- Registration
- Payment
- Submission
- Reviewer
- Program
- Attendance
- Certificate

### System
- Users
- Roles & Permissions
- Email Templates
- Settings
- Audit Logs

---

# 23. CMS

Konten public tidak boleh hardcoded kecuali fallback/default seed.

Admin dapat mengelola:

- hero;
- about;
- conference highlights;
- speakers;
- topics;
- dates;
- fee;
- publication;
- venue;
- contacts;
- FAQ;
- sponsors;
- footer;
- navigation;
- announcements.

Gunakan media library untuk image/file.

---

# 24. Teknologi

Implementasi utama:

- **Laravel** backend.
- Blade untuk server-rendered public/admin jika template project berbasis Blade.
- Vite.
- Axios untuk HTTP/AJAX.
- SweetAlert2 untuk confirmation/notification UI.
- DataTables untuk tabel admin.
- Select2 untuk select kompleks.
- Dropzone.js untuk upload.
- Queue untuk email/PDF.
- **Mail Service terpusat** untuk seluruh pengiriman email aplikasi.
- Cron/Laravel Scheduler untuk reminder.
- UUID untuk primary/business IDs aplikasi.
- MySQL/MariaDB.
- Storage abstraction agar local storage dapat dipindahkan ke S3/Google Drive/object storage.

Tidak memakai CDN untuk dependency frontend jika package tersedia via npm.

Business logic **tidak ditaruh penuh di Controller**.

Struktur wajib:

```text
HTTP Request
    ↓
Form Request
    ↓
Controller
    ↓
DTO
    ↓
Service
    ↓
Repository / Eloquent
    ↓
Database
```

Gunakan:

- DTO;
- Service Layer;
- Form Request;
- Policy/Gate;
- Action class bila operation kompleks;
- Enum untuk status;
- Observer/Event/Listener untuk side effect;
- Queue Job untuk email dan generate file.

---

# 24A. Mail Service

Seluruh pengiriman email wajib menggunakan **Mail Service terpusat**. Controller, Service domain, Action, Listener, atau Job tidak boleh membangun konfigurasi SMTP atau logika pengiriman email secara langsung.

Struktur yang disarankan:

```text
app/
├── Services/
│   └── Mail/
│       ├── MailService.php
│       ├── MailTemplateService.php
│       └── MailLogService.php
├── DTOs/
│   └── Mail/
│       └── SendMailDTO.php
├── Mail/
│   ├── AccountVerificationMail.php
│   ├── RegistrationConfirmationMail.php
│   ├── PaymentVerifiedMail.php
│   ├── PaymentRejectedMail.php
│   ├── AbstractSubmittedMail.php
│   ├── RevisionRequestedMail.php
│   ├── AbstractAcceptedMail.php
│   ├── AbstractRejectedMail.php
│   ├── LoaIssuedMail.php
│   ├── FullPaperReminderMail.php
│   ├── SchedulePublishedMail.php
│   └── CertificateAvailableMail.php
├── Jobs/
│   └── SendConferenceMailJob.php
```

## Tanggung Jawab `MailService`

`MailService` minimal menangani:

- pemilihan template email;
- recipient/to;
- cc/bcc bila diperlukan;
- subject;
- variable/template data;
- attachment;
- queue dispatch;
- retry;
- failure handling;
- logging;
- conference-specific sender name;
- reply-to;
- mail transport/configuration abstraction.

Contoh penggunaan:

```php
$mailDto = SendMailDTO::fromArray([
    'to' => $participant->email,
    'template' => 'abstract_accepted',
    'subject' => 'Abstract Accepted - ICLEH 2026',
    'data' => [
        'participant' => $participant,
        'submission' => $submission,
    ],
]);

$this->mailService->queue($mailDto);
```

Business service cukup memanggil Mail Service:

```php
public function acceptSubmission(
    Submission $submission,
    AcceptSubmissionDTO $dto
): Submission {
    return DB::transaction(function () use ($submission, $dto) {
        // update submission
        // simpan status history
        // generate LoA / dispatch action

        $this->mailService->queue(
            SendMailDTO::abstractAccepted($submission)
        );

        return $submission->refresh();
    });
}
```

## Email Templates

Template email disimpan di database agar dapat diubah dari admin.

Minimal fields:

```text
uuid
conference_id nullable
code
name
subject
body_html
body_text nullable
from_name nullable
reply_to nullable
active
created_at
updated_at
```

Template code minimal:

```text
email_verification
registration_success
payment_submitted
payment_verified
payment_rejected
abstract_submitted
review_result
revision_requested
abstract_accepted
abstract_rejected
loa_issued
full_paper_reminder
schedule_published
schedule_changed
certificate_available
```

Template mendukung placeholder, misalnya:

```text
{{ participant_name }}
{{ conference_name }}
{{ submission_title }}
{{ payment_amount }}
{{ loa_number }}
{{ schedule_date }}
{{ schedule_time }}
{{ chamber_name }}
```

Placeholder wajib diproses melalui service/template renderer yang terkontrol. Jangan menjalankan PHP/eval dari isi template database.

## Mail Queue

Email non-kritis dikirim melalui Laravel Queue.

```text
Domain Service
    ↓
Event / Listener
    ↓
SendConferenceMailJob
    ↓
MailService
    ↓
Laravel Mail
    ↓
SMTP / Mail Provider
```

Job harus:

- implements `ShouldQueue`;
- memiliki retry;
- memiliki timeout;
- menangani failure;
- tidak menggandakan pengiriman bila job di-retry secara tidak tepat;
- mencatat status pengiriman.

## Mail Logs

Sediakan tabel `mail_logs` minimal:

```text
uuid
conference_id nullable
user_id nullable
recipient
cc nullable
bcc nullable
template_code nullable
subject
status
provider_message_id nullable
queued_at nullable
sent_at nullable
failed_at nullable
error_message nullable
created_at
updated_at
```

Status menggunakan Enum:

```php
enum MailStatus: string
{
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case FAILED = 'failed';
}
```

Admin dapat melihat:

- email queued;
- sent;
- failed;
- recipient;
- template;
- waktu pengiriman;
- error message;
- tombol retry bila gagal.

## Konfigurasi

Gunakan konfigurasi Laravel:

```text
MAIL_MAILER
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_ENCRYPTION
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
```

Credential tidak boleh ditaruh di source code/database CMS.

Production dapat menggunakan:

- SMTP;
- Amazon SES;
- Mailgun;
- Postmark;
- provider lain yang didukung Laravel.

Perubahan provider tidak boleh memerlukan perubahan business logic aplikasi.

## Attachment

Mail Service harus mendukung attachment seperti:

- Letter of Acceptance PDF;
- invoice/receipt;
- certificate bila memang diperlukan.

Namun dashboard tetap menjadi sumber utama download dokumen.

Untuk file private, attachment harus diambil melalui storage abstraction dan authorization/business rule yang sesuai.

## Testing Mail

Gunakan Laravel Mail Fake pada automated tests:

```php
Mail::fake();
```

Test minimal:

- payment verified mengirim email yang tepat;
- abstract accepted mengirim acceptance email;
- LoA issued dapat memasukkan attachment/link yang benar;
- rejection email menyertakan alasan;
- queue failure tercatat;
- user tidak menerima duplicate email akibat retry/event berulang.

---

# 25. Database — Entitas Utama

Minimal tables:

```text
users
roles
permissions

conferences
conference_settings
conference_dates
conference_topics
registration_fees

profiles
registrations
payments

submissions
submission_authors
submission_files
submission_status_histories

review_assignments
reviews
review_scores

speakers
venues
conference_days
program_sessions
chambers
program_schedules

attendances

loa_documents
certificates
document_templates

announcements
pages
page_sections
faqs
partners
media

notifications
email_templates
mail_logs

audit_logs
```

Semua table domain menggunakan UUID.

Jangan gunakan status berupa string bebas. Gunakan PHP backed Enum.

---

# 26. Security

Wajib:

- CSRF;
- XSS escaping;
- validation;
- authorization policy;
- rate limiting;
- email verification;
- secure password hashing;
- file MIME validation;
- file size limit;
- random storage filenames;
- private storage untuk manuscript/payment proof;
- signed URL atau authorized download;
- audit log untuk perubahan penting;
- login throttling;
- session regeneration;
- secure cookies di production.

Payment proof, manuscript, review file, dan data peserta **tidak boleh** ditempatkan sebagai public file langsung.

---

# 27. Audit Log

Log minimal:

- user login;
- registration changes;
- payment verification/rejection;
- submission status changes;
- reviewer assignment;
- review decision;
- LoA generation;
- certificate generation;
- program changes;
- user/role changes;
- CMS publish/unpublish.

Audit fields:

- actor;
- action;
- subject type/id;
- before;
- after;
- IP;
- user agent;
- timestamp.

---

# 28. Export dan Reports

Admin dapat export:

- participants XLSX;
- presenters XLSX;
- payment recap XLSX;
- abstract list XLSX;
- accepted paper XLSX;
- reviewer assignment XLSX;
- schedule XLSX/PDF;
- attendance XLSX;
- certificate list XLSX.

Filter export mengikuti filter DataTable.

---

# 29. Public URL Structure

```text
/
 /about
 /speakers
 /topics
 /important-dates
 /registration
 /guide-for-authors
 /program
 /publication
 /venue
 /contact
 /faq
 /announcements
 /announcements/{slug}

 /login
 /register

 /verify/loa/{code}
 /verify/certificate/{code}
```

Participant:

```text
/dashboard
/profile
/registration
/payment
/submissions
/submissions/{uuid}
/loa
/program
/attendance
/certificates
/notifications
```

Admin:

```text
/admin
/admin/conference/*
/admin/participants/*
/admin/payments/*
/admin/submissions/*
/admin/reviews/*
/admin/program/*
/admin/documents/*
/admin/cms/*
/admin/reports/*
/admin/system/*
```

---

# 30. Public Homepage Content

## Hero

**5th ICLEH 2026**  
**International Conference on Law, Economy, and Health**

*Reimagining Law, Economy and Health in the Age of Artificial Intelligence: Advancing Human Dignity, Justice and Sustainable Governance*

**11–12 November 2026**  
Faculty of Law, Universitas 17 Agustus 1945 Semarang  
Hybrid Conference

CTA:

- Register Now
- Submit Abstract
- View Program

## About

ICLEH 2026 is an international academic forum organized by the Faculty of Law, Universitas 17 Agustus 1945 Semarang. The conference brings together academics, researchers, legal practitioners, policymakers, business professionals, health professionals, technology experts, and international stakeholders to discuss the future of law, economy, and health in the age of Artificial Intelligence.

The conference focuses on ensuring that technological transformation remains oriented toward human dignity, justice, accountability, and sustainable governance.

---

# 31. Responsive & UX

Support:

- desktop;
- tablet;
- mobile.

Requirements:

- responsive;
- WCAG-aware contrast;
- keyboard-friendly;
- loading indicators;
- skeleton/empty state;
- server + client validation errors;
- no horizontal overflow;
- tables responsive;
- mobile dashboard navigation;
- confirmation before destructive action.

---

# 32. Search Engine & Social

Public website:

- title/meta description configurable;
- OG image;
- canonical URL;
- sitemap;
- robots;
- semantic markup;
- speaker and event metadata where applicable.

---

# 33. Initial Seed Data

Seeder harus mengisi:

- ICLEH 2026 conference;
- theme;
- 8 topics;
- dates;
- fees;
- speakers;
- venue;
- admin role;
- reviewer role;
- finance role;
- event role;
- participant role.

Seeder tidak boleh membuat password production default yang mudah ditebak.

---

# 34. Definition of Done

Fitur dianggap selesai jika:

1. Public site responsif dan sesuai branding ICLEH.
2. Registration end-to-end bekerja.
3. Login/email verification bekerja.
4. Participant dashboard bekerja.
5. Payment proof upload + admin verification bekerja.
6. Abstract submission + co-author bekerja.
7. Review assignment + review workflow bekerja.
8. Revision workflow bekerja.
9. Abstract acceptance + LoA PDF bekerja.
10. Full paper upload/versioning bekerja.
11. Program/chamber/schedule bekerja.
12. QR attendance bekerja.
13. Certificate PDF + public verification bekerja.
14. CMS public site bekerja.
15. Admin dashboard dan reports bekerja.
16. Semua role dibatasi dengan authorization.
17. File private tidak dapat diakses tanpa authorization.
18. Audit log tersedia.
19. Automated test untuk critical flows tersedia.
20. Mail Service terpusat, queued email, template email, mail log, retry, dan failure handling bekerja.
21. `README.md` mencakup installation, `.env`, migration, seeder, queue, scheduler, mail configuration, storage, dan deployment.

---

# 35. Tahapan Implementasi

## Phase 1 — Foundation
- auth;
- RBAC;
- conference settings;
- admin template integration;
- public layout;
- CMS basics.

## Phase 2 — Registration
- participant profile;
- registration;
- fee;
- payment;
- admin verification.

## Phase 3 — Submission
- abstract;
- author/co-author;
- file versioning;
- review;
- decision;
- revision;
- LoA.

## Phase 4 — Full Paper & Publication
- full paper;
- final manuscript;
- publication preference/status;
- best paper.

## Phase 5 — Program
- speaker;
- chamber;
- schedule;
- participant presentation schedule.

## Phase 6 — Event Operation
- QR;
- attendance;
- certificate;
- verification.

## Phase 7 — Reporting & Hardening
- export;
- dashboard statistics;
- audit;
- security review;
- tests;
- deployment documentation.

---

# 36. Catatan Implementasi Saat Project/Admin Template Diberikan

1. **Jangan mengganti template admin yang disediakan.**
2. Audit struktur asset/template terlebih dahulu.
3. Integrasikan layout menjadi reusable Blade components/layouts.
4. Gunakan aset poster/logo/foto yang disediakan di project.
5. Public website tidak harus meniru visual MINDS secara literal; yang diambil adalah pola informasi dan UX conference website.
6. Warna/branding mengikuti poster ICLEH.
7. Konten faktual mengikuti proposal ICLEH 2026.
8. Bila ada perbedaan antara poster, proposal, dan data yang nantinya diberikan admin, jangan mengubah diam-diam: tandai sebagai konfigurasi yang perlu diputuskan.
9. Program, speaker, fee, timeline, dan venue harus database-driven.
10. Sistem harus siap untuk konferensi tahun berikutnya dengan konsep `conferences`, bukan hanya hardcoded ICLEH 2026.
