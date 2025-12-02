<p align="center">
	<img src="public/images/emblem.png" alt="School Emblem" width="120" />
</p>

<h1 align="center">E-Matokeo</h1>

<p align="center">
	<strong>Modern school results & hostel management system for Tanzanian schools.</strong><br />
	Built with Laravel, Inertia.js, Vue 3 & Tailwind CSS.
</p>

---

## 1. Overview

E-Matokeo ni mfumo wa shule unaosaidia kusimamia:

- Matokeo ya wanafunzi (exam results, grade distribution, analytics)
- Hostel management (allocations, payments, guardians, joining instructions)
- Dashboards za uongozi (top performers, students needing support, ward ranking - coming soon)
- Notifications (email & SMS settings – structure already prepared)

Mfumo umejengwa ili uwe mwepesi kutumia na uendane na mazingira ya shule za Tanzania (O-Level / A-Level).

## 2. Main Features

- **Results Management**
	- Academic years, exams na exam results kwa kila mwanafunzi
	- Dashboard ya analytics: pass/fail rate, grade distribution (A–F)
	- School overview na statistics

- **Hostel Management**
	- Hostel allocations kwa wanafunzi, kwa mwaka wa masomo na kwa school
	- Rooms & beds management (capacity, occupied, available)
	- Hostel payments (full, partial, unpaid) na receipts za kuchapisha
	- Hostel student report (details + payments + exam results + hostel requirements)
	- Personalized joining instructions per student (items, rules, signatures, login details)

- **Staff Management (Hostel)**
	- Matrons & Patrons (assigned per hostel)
	- Contract preview page (printable contract document)

- **Communication & Notifications**
	- Email settings (SMTP configuration: host, port, encryption, username, password)
	- SMS gateway settings (provider name, API URL, sender ID, callback URL)
	- Test notifications section (UI ready, integration coming soon)

- **UI & Printing**
	- Clean dashboard with cards and analytics
	- Print-optimised pages (headers + document only; nav & sidebar hidden on print)
	- Beautiful receipts, reports & joining instructions suitable for filing

## 3. Tech Stack

- **Backend**: Laravel (PHP)
- **Frontend**: Vue 3 (script setup) + Inertia.js
- **Styling**: Tailwind CSS
- **Build Tool**: Vite
- **Database**: MySQL / MariaDB (or any DB supported by Laravel)

## 4. Project Structure (high level)

- `app/Models`
	- `Student.php`, `School.php`, `AcademicYear.php`
	- `Hostel.php`, `HostelRoom.php`, `HostelAllocation.php`, `HostelPayment.php`, `HostelRequirement.php`
	- `User.php` (including roles like matron/patron)

- `routes/web.php`
	- Dashboard route (`/dashboard`) with results analytics + top/bottom students
	- Hostel routes:
		- `/hostel-rooms-beds`
		- `/hostel-allocations`
		- `/hostel-students` + report + joining instructions
		- `/hostel-payments` + receipts
		- `/hostel-matron-patron` + contract preview

- `resources/js/Layouts`
	- `AuthenticatedLayout.vue` – main app layout (nav, sidebar, content)

- `resources/js/Pages`
	- `Dashboard.vue` – analytics + top/bottom students + ward ranking (coming soon)
	- `HostelRoomsBeds.vue`
	- `HostelAllocations.vue`
	- `HostelStudent.vue`
	- `HostelStudentReport.vue`
	- `HostelJoiningInstruction.vue`
	- `HostelPayments.vue`
	- `HostelPaymentReceipt.vue`
	- `HostelMatronPatron.vue`
	- `HostelMatronContractPreview.vue`
	- `EmailSmsSettings.vue`

## 5. Installation & Setup

### 5.1. Requirements

- PHP 8.2+
- Composer
- Node.js (LTS) + npm or yarn
- MySQL / MariaDB database

### 5.2. Clone & Install Dependencies

```bash
git clone https://github.com/raydanielg/ematokeo.git
cd ematokeo

composer install
npm install
```

### 5.3. Environment Setup

Copy `.env.example` to `.env` na uweke configuration zako:

```bash
cp .env.example .env
php artisan key:generate
```

Important `.env` keys:

- Database:
	- `DB_CONNECTION=mysql`
	- `DB_HOST=127.0.0.1`
	- `DB_PORT=3306`
	- `DB_DATABASE=ematokeo`
	- `DB_USERNAME=...`
	- `DB_PASSWORD=...`

- Mail (for email notifications – optional for now):
	- `MAIL_MAILER=smtp`
	- `MAIL_HOST=...`
	- `MAIL_PORT=587`
	- `MAIL_USERNAME=...`
	- `MAIL_PASSWORD=...`
	- `MAIL_ENCRYPTION=tls`
	- `MAIL_FROM_ADDRESS=...`
	- `MAIL_FROM_NAME="Your School Name"`

### 5.4. Run Migrations & Seeders

```bash
php artisan migrate

# optionally add seeders later for demo data
```

### 5.5. Run Dev Server

In two terminals:

```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite dev server
npm run dev
```

Then visit:

- `http://127.0.0.1:8000` (or URL ya artisan serve).

## 6. Building for Production

```bash
npm run build
php artisan route:cache
php artisan config:cache
```

Kisha weka contents za `public/` na `build assets` kwenye hosting yako (cPanel, VPS, etc) kama kawaida ya Laravel apps.

## 7. Key Modules in Detail

### 7.1. Dashboard

- Cards za fast stats: students, subjects, classes, etc.
- Results analytics kwa mwaka wa sasa:
	- Total candidates
	- Pass / Fail counts & percentages
	- Grade distribution (A–F) with bars
- Top lists:
	- **Top 10 Best Students** (highest average marks per student)
	- **Top 10 Students Needing Support** (lowest averages – kwa follow-up)
- Ward ranking section (UI card: "Ward ranking – coming soon").

### 7.2. Hostel Management

- **Rooms & Beds**
	- Hostels list (boys/girls/mixed), rooms with capacity/occupied/available
	- Add / edit / delete rooms

- **Allocations**
	- Two-step form to allocate student in hostel:
		- Step 1: search student (AJAX, by name / exam number, filtered by school)
		- Step 2: guardian details, hostel & room selection, fee amount, notes

- **Hostel Students Directory**
	- Students with hostel allocations only
	- Shows class, exam number, fee summary, status (Paid/Partial/Unpaid)
	- Actions: report, receipt, joining instructions

- **Payments & Receipts**
	- `/hostel-payments` page: list of allocations with amount, paid, balance, status
	- Add payment modal per allocation (amount, date, method, reference, notes)
	- Printable receipt (`HostelPaymentReceipt.vue`) with school header and payments table

- **Reports & Joining Instructions**
	- `HostelStudentReport.vue`:
		- Student + guardian + hostel details
		- Fee summary & payment status
		- Exam results (by exam & subject) + grades
		- Hostel requirements (items & rules) from `hostel_requirements` table
		- Signature blocks (Head of School, etc.)
	- `HostelJoiningInstruction.vue`:
		- Personalized joining instructions document per student
		- Sections: intro, hostel requirements, fees, rules, conduct, signatures
		- Inline username (exam number) + blank password line for manual filling
		- Print button with clean print-view (no nav/sidebar)

### 7.3. Hostel Staff (Matron/Patron)

- Manage matron/patron staff:
	- Assign to hostel
	- Auto-generate username + default password (configurable)
- Contract preview page:
	- Printable contract with school header, emblem, contact details
	- Sections for duties, remuneration, conduct, sanctions, signatures
	- Some fields content-editable (e.g. dates, amounts) before printing

### 7.4. Email & SMS Settings

- Page `EmailSmsSettings.vue`:
	- Email (SMTP) configuration form
	- SMS gateway configuration form (API URL, sender, callback)
	- Test notifications block for trying demo email/SMS (coming soon)

## 8. Branding & Logo

- Default README in repo inaonyesha placeholder ya logo: `public/images/emblem.png`.
- Unaweza kubadilisha kwa logo halisi ya shule yako kwa kuweka faili hiyo (PNG, 120x120 au kadri upendavyo).
- Header za reports, receipts, na contracts tayari zinatumia emblem hiyo.

## 9. Contributing / Customisation

- Unaweza kubadilisha modules ili zifit:
	- Vigezo vya grading
	- Muonekano wa reports (headers, footers, signatures)
	- Additional roles (e.g. academic dean, discipline master)
- Pull requests & issues zinakaribishwa kwenye GitHub repo.

## 10. License

Project huu unasambazwa chini ya **MIT License**.

Tazama faili [`LICENSE`](LICENSE) kwa maelezo kamili.
