# SYSTEM DOCUMENTATION OF PERSONNEL APPOINTMENT MANAGEMENT SYSTEM (PAMS)
### FOR THE DEPED SCHOOLS DIVISION OFFICE OF CAVITE PROVINCE
**TRECE MARTIRES CITY, CAVITE**

---

**Prepared by:**  
ICT Unit – Office of the Schools Division Superintendent  

**Developed and Documented by:**  
Full-Stack System Developer  
*Under the Supervision of: Mrs. Lara Vey C. Morales*  

**System Version:** Personnel Appointment Management System (PAMS) v1.0  
**Date:** July 2026  

---

## TABLE OF CONTENTS

1. [SUMMARY](#1-summary)
2. [SYSTEM REQUIREMENTS](#2-system-requirements)
   - [Server Requirements](#server-requirements)
   - [Supported Browsers](#supported-browsers)
3. [INSTALLATION AND SETUP](#3-installation-and-setup)
4. [USER ROLES AND ACCESS CONTROL](#4-user-roles-and-access-control)
5. [AUTHENTICATION & SECURITY](#5-authentication--security)
6. [SIDEBAR NAVIGATION](#6-sidebar-navigation)
7. [DASHBOARD MODULE](#7-dashboard-module)
8. [APPOINTMENT MANAGEMENT MODULE](#8-appointment-management-module)
9. [TRANSACTION NUMBERS MODULE](#9-transaction-numbers-module)
10. [DOCUMENT GENERATION & EXPORT MODULE](#10-document-generation--export-module)
11. [HISTORY & ARCHIVE MODULE](#11-history--archive-module)
12. [PLANTILLA & DATA IMPORT MODULE](#12-plantilla--data-import-module)
13. [USER MANAGEMENT & INVITATION MODULE (Admin Only)](#13-user-management--invitation-module-admin-only)
14. [DATABASE SCHEMA REFERENCE](#14-database-schema-reference)
15. [POSITION TO SALARY GRADE MAPPING REFERENCE](#15-position-to-salary-grade-mapping-reference)
16. [CLI COMMANDS & UTILITIES REFERENCE](#16-cli-commands--utilities-reference)
17. [TECHNOLOGY STACK](#17-technology-stack)
18. [GLOSSARY OF TERMS](#18-glossary-of-terms)
19. [SYSTEM INTERFACE](#19-system-interface)

---

## 1. SUMMARY

The **Personnel Appointment Management System (PAMS)** is an enterprise web-based application developed specifically for the **Department of Education (DepEd) Schools Division Office of Cavite Province**. It digitizes, automates, and streamlines the end-to-end encoding, validation, tracking, document generation, and archiving of appointment records for teaching, non-teaching, and Senior High School (SHS) personnel across all school districts and administrative units within the division.

The system replaces manual, error-prone spreadsheet tracking and physical paperwork preparation with a unified, centralized, and role-guarded platform. PAMS features automated Plantilla item lookup, dynamic salary grade and step calculation, real-time transaction number duplicate prevention, automated Word/Excel document generation (AFA, Checklist, RAI, Final Deliberation), bulk ZIP archiving, soft-delete recovery, and email invitation workflows for secure onboarding.

---

## 2. SYSTEM REQUIREMENTS

### Server Requirements:
* **PHP:** Version 8.2 or higher
* **PHP Extensions:** `OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`, `Fileinfo`, `Zip`, `Gd` / `Imagick`
* **Composer:** 2.x
* **Node.js & NPM:** Node 18+ and NPM 9+
* **Database Engine:** SQLite (default / development) or MySQL 8.0+ / PostgreSQL 13+ (production)
* **Web Server:** Apache 2.4+ (with `mod_rewrite` enabled) or Nginx 1.20+

### Supported Browsers:
* **Google Chrome** (Recommended, v100+)
* **Mozilla Firefox** (v100+)
* **Microsoft Edge** (v100+)
* **Apple Safari** (v15+)
* **Minimum Screen Resolution:** 1280 × 720 pixels (Responsive layout optimized for Desktop displays)

---

## 3. INSTALLATION AND SETUP

To deploy and configure the Personnel Appointment Management System (PAMS) on a local development server or production environment, follow the steps below:

1. **Clone or Extract Project Files:**
   Clone the repository or extract the source package into your web server directory:
   ```bash
   git clone <repository-url> deped-pams
   cd deped-pams
   ```

2. **Configure Environment File:**
   Copy the example environment configuration file to `.env`:
   ```bash
   copy .env.example .env
   ```

3. **Configure Environment Variables in `.env`:**
   Open `.env` and set the core application and database settings:
   ```env
   APP_NAME="DepEd Cavite PAMS"
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=sqlite
   # If using MySQL:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=deped_pams
   # DB_USERNAME=root
   # DB_PASSWORD=secret

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_username
   MAIL_PASSWORD=your_password
   MAIL_FROM_ADDRESS="no-reply@depedcavite.ph"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

4. **Install Backend PHP Dependencies:**
   Run Composer to install all required framework and document processing libraries:
   ```bash
   composer install
   ```

5. **Generate Application Security Key:**
   Generate the Laravel application key:
   ```bash
   php artisan key:generate
   ```

6. **Run Database Migrations:**
   Execute database migrations to construct the database tables (`users`, `appointments`, `plantilla_items`, `invitations`, `salary_grades`, `salary_steps`, `cache`, `jobs`):
   ```bash
   php artisan migrate
   ```

7. **Import Plantilla Items Data:**
   Import the official `DATA.xlsx` file into the `plantilla_items` database table:
   ```bash
   php artisan import:data-excel
   ```

8. **Seed Default Database Records (Optional):**
   Seed basic admin users, initial roles, or salary scale data:
   ```bash
   php artisan db:seed
   ```

9. **Install Frontend Assets & Build Utilities:**
   Install Node packages and build compiled assets via Vite:
   ```bash
   npm install
   npm run build
   ```

10. **Start Application Server:**
    Launch the local development server:
    ```bash
    php artisan serve
    ```
    Access the system at `http://127.0.0.1:8000`.

---

## 4. USER ROLES AND ACCESS CONTROL

The system enforces strict Role-Based Access Control (RBAC) powered by custom middleware (`EnsureUserHasRole`). The system recognizes two primary user roles: **Administrator (`admin`)** and **HR Officer (`hr`)**.

| Feature / Action | Administrator (`admin`) | HR Officer (`hr`) |
| :--- | :---: | :---: |
| **View Dashboard & Analytics** | Yes | Yes |
| **View Appointments List** | Yes (All Records) | Yes (Own Encoded Records) |
| **Create New Appointment** | Yes | Yes |
| **Edit Existing Appointment** | Yes | Yes |
| **Assign / Update Transaction Number (TN)** | Yes | Yes |
| **Download Official Documents (AFA, Checklist, RAI, Final Deliberation)** | Yes | Yes |
| **Export Data to CSV / Bulk ZIP Download** | Yes | Yes |
| **Conclude Appointment (Move to Concluded History)** | Yes | Yes |
| **View Completed Archive & Concluded History** | Yes | Yes |
| **Soft Delete Appointment (Move to Trash Bin)** | Yes | No |
| **Bulk Soft Delete Appointments** | Yes | No |
| **Restore / Force Delete Trashed Records** | Yes | No |
| **User & Account Management (Approve, Create, Deactivate)** | Yes | No |
| **Send Email Invitations for Registration** | Yes | No |
| **Reset User Passwords Directly** | Yes | No |

---

## 5. AUTHENTICATION & SECURITY

### 5.1 Login Authentication
Users access PAMS via `/login` using their registered email address and password. Upon successful validation:
* Active accounts (`is_active = true`) are logged in and redirected to `/dashboard`.
* Inactive or pending accounts are blocked from accessing protected routes with an authorization notification.

### 5.2 Registration & Email Invitation Workflow
Direct public self-registration is restricted. Account creation follows a secure invitation architecture:
1. **Admin Invitation:** An Admin inputs a candidate's name, email, and requested role on the `/admin/users/create` page.
2. **Token Generation:** The system creates a record in `invitations` with a unique, cryptographically generated token and sets an expiration timestamp.
3. **Confirmation Email:** The system dispatches an email containing a custom confirmation link: `/invitation/accept/{token}`.
4. **Account Creation:** Clicking the link allows the candidate to set their secure password, whereupon the invitation is consumed (`used_at` set) and the formal `users` record is activated.

### 5.3 Password Management & Direct Resets
* **Self-Service:** Authenticated users can modify their passwords in their profile settings.
* **Admin Override:** Administrators can directly reset any user's password via `/admin/passwords` without needing the old password.

### 5.4 Middleware Security & Back History Guarding
* `auth`: Restricts access to authenticated users only.
* `guest`: Prevents logged-in users from viewing the login screen.
* `EnsureUserHasRole:hr,admin`: Validates that the logged-in user possesses the required role to execute specified routes.
* Security headers prevent browser back-button navigation to cached sensitive pages after logout.

---

## 6. SIDEBAR NAVIGATION

The application features a responsive, collapsible sidebar menu that maintains state across page requests. Access to menu items is controlled dynamically according to the user's role:

| Menu Item | Icon / Module | Accessible By | Destination Route | Description |
| :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | Chart Bar | Admin, HR | `dashboard.index` | Overview KPI cards, statistics, and recent activity metrics. |
| **Appointments** | File Text | Admin, HR | `appointments.index` | Active appointment list with date filtering, status filters, and actions. |
| **Create New** | Plus Circle | Admin, HR | `appointments.create` | Form to encode a new appointment with Plantilla item lookup. |
| **Archive** | Archive | Admin, HR | `appointments.archive` | Table of completed appointments marked via the "Mark completed" button on the Appointment Data page. |
| **History** | Clock / Calendar | Admin, HR | `history.index` | Complete history of concluded appointments filtered by date range. |
| **Trash Bin** | Trash | Admin Only | `appointments.trash` | Soft-deleted records management (restore or permanent delete). |
| **User Management** | Users | Admin Only | `admin.users.index` | User account management, activation, role updates, and invitations. |
| **Password Reset** | Key | Admin Only | `admin.passwords.index` | Administrative user password reset panel. |

---

## 7. DASHBOARD MODULE

The **Dashboard (`/dashboard`)** provides real-time monitoring and reporting statistics regarding encoded and processed appointments.

### 7.1 Metrics & KPI Cards
* **Total Encoded Appointments:** Total count of active appointments for the selected date or overall.
* **Completed Appointments:** Count of appointments that have been marked as completed and moved to Archive.
* **Completed Today:** Real-time counter of appointments updated to completed state within the current day.
* **Monthly Volume:** Total number of appointments encoded in the current calendar month.

### 7.2 Filtering Options
* **Date Filter:** Allows switching between specific encoding dates or viewing all dates.
* **Status Filter:** Filter list by `Active`, `In Progress`, `Completed`, or `Concluded`.
* **Nature of Appointment Filter:** Breakdown by `Original`, `Promotion`, `Transfer`, `Reappointment`, or `Reinstatement`.

---

## 8. APPOINTMENT MANAGEMENT MODULE

The Appointment Management Module serves as the primary data-entry and workflow engine of PAMS.

### 8.1 Data Fields & Validation Rules
An appointment record includes comprehensive personal, organizational, and appointment metadata:
* **Personal Information:** Last Name, First Name, Middle Name, Name Extension, Sex (`Male`, `Female`, `Prefer not to say`), Date of Birth, TIN, PWD Status (`Yes`, `No`), Type of Disability, IP Group Member (`Yes`, `No`), Ethnicity.
* **Position & Salary Details:** Position Title, Position From (Date), Position To (Date), Salary Grade, Salary Grade Step, Monthly Salary, Compensation in Words, Compensation in Numbers, Nature of Appointment (`Original`, `Promotion`, `Transfer`, `Reappointment`, `Reinstatement`), Employee Status (`Permanent`, `Temporary`, `Casual`, `Contractual`, `Coterminous`), Reason, Position Level (`First Level`, `Second Level`, `Third Level`), Appointment Status (`Original`, `Renewal`, `Reappointment`).
* **Organizational & Plantilla Metadata:** Department, School District, School, Sector, Agency Name, Plantilla Item Number, Plantilla Page Number, ODC Number, Date Received (Records), Date Received (HR), Previous Incumbent, Incumbent, Publication Mode, Publication Date Range (`From` / `To`), Assessment Date, Deliberation Date, Education, Senior High School indicator (`Yes`, `No`), SHS Strand, Non-Teaching indicator.
* **Eligibility Details:** Eligibility Type, Eligibility Validity, Eligibility First Used (`Yes`, `No`), Date of Original Appointment, Date of Last Promotion, Date of Signing.

### 8.2 Plantilla Item Autocomplete Lookup (`DATA.xlsx`)
When encoding an appointment on `/appointments/create`, users can leverage the **Plantilla Search** feature:
* Interactively searches the imported `plantilla_items` database table via `/appointments/plantilla-search`.
* Search parameters can query by `data` (Item Number), `position`, `school_name`, or `city_municipality`.
* Selecting a search result auto-populates relevant fields such as Position Title, School/Station, City, Position Level, and Eligibility.

### 8.3 Automated Salary Grade and Step Calculator
* **Position-to-Salary-Grade Auto-Assignment:** The model automatically maps over 60 standard DepEd position titles to their official Civil Service Salary Grades (e.g., *Teacher I* $\rightarrow$ SG 11, *Master Teacher I* $\rightarrow$ SG 18, *School Principal IV* $\rightarrow$ SG 22).
* **Dynamic Salary Lookup API (`/appointments/salary`):** Querying a Salary Grade and Step via AJAX contacts `PlantillaItemController@salary`, joining `salary_grades` and `salary_steps` to return exact monthly salary figures and convert numerical values into formal legal words (e.g., `53,873.00` $\rightarrow$ *"FIFTY-THREE THOUSAND EIGHT HUNDRED SEVENTY-THREE PESOS"*).

### 8.4 Record State Lifecycle
An appointment progresses through well-defined record states:
1. **`new` / `active`:** Encoded into the system; awaiting document generation or processing.
2. **`in_progress`:** Initiated once any official document (AFA, Checklist, RAI, or Final Deliberation) is downloaded.
3. **`completed`:** A Transaction Number is assigned to the record.
4. **`concluded`:** Formally closed out (e.g., retired, resigned, transferred, or contract ended) and moved into Concluded History.
5. **`deleted`:** Soft-deleted by an Admin and placed in the Trash Bin.

---

## 9. DOCUMENT GENERATION & EXPORT MODULE

PAMS includes an automated document compilation engine powered by **PHPWord** and **PhpSpreadsheet**.

### 9.1 Supported Document Templates

| Document Type | File Format | Engine | Template Source | Description |
| :--- | :---: | :--- | :--- | :--- |
| **Appointment Form (AFA)** | `.docx` | PHPWord | `SAMPLE APPOINTMENT FORM.docx`<br>`SAMPLE APPOINTMENT FORM FOR HS.docx`<br>`SAMPLE APPOINTMENT FORM FOR SHS.docx` | Official CSC KSS Form 33 / Appointment Form filled dynamically. Template selection: `senior_high_school = 'Yes'` → SHS variant; `senior_high_school = 'No'` + `teaching_level = 'Secondary'` → HS variant; otherwise base template. |
| **Appointment Processing Checklist** | `.xlsx` | PhpSpreadsheet | `Checklist.xlsx` | Official evaluation checklist containing verification items, qualifications, and plantilla verification notes. |
| **Report on Appointment Issued (RAI)** | `.xlsx` | PhpSpreadsheet | `Report on Appointment Issued.xlsx` | CSC-mandated RAI report table populated with personal, plantilla, and eligibility details. |
| **Final Deliberation Document** | `.docx` | PHPWord | `FINAL DELIBERATION_NEW TEMPLATE.docx` | PSB/HRMPSB Final Deliberation minutes and summary sheet. |

### 9.2 Document Download Tracking & Workflow Updates
* Downloading any of the four individual documents updates tracking timestamps: `afa_downloaded_at`, `checklist_downloaded_at`, `rai_downloaded_at`, or `final_deliberation_downloaded_at`.
* Triggers automatic state transition of the appointment record from `active` to `in_progress`.

### 9.3 Bulk Export & ZIP Package Generation
From `/appointments/export/csv`:
* **Bulk Document Bundle (POST `ids[]`):** Compiles all four generated documents (AFA, Checklist, RAI, Final Deliberation) for every selected appointment into a single structured `.zip` archive file for instant download.
* **Data CSV Export (GET):** Streams a complete, uncompressed CSV dataset of active appointment records.
* **Monitoring CSV Export (POST `/appointments/export/monitoring`):** Generates an Excel/CSV monitoring report populated using `SAMPLE MONITORING.xlsx`.

---

## 11. HISTORY & ARCHIVE MODULE

### 11.1 Archive View (`/appointments/archive`)
* Lists all appointments that have reached **`completed`** status via the "Mark completed" button on the Appointment Data page.
* Displays updated timestamp, full candidate name, position, district/school, transaction number, and quick document re-download links.
* Filterable by date range (`from` and `to`) and keyword search.

### 11.2 Concluded Records History (`/history`)
* Encapsulates appointments that have been formally closed out via `AppointmentController@conclude`.
* Preserves conclusion reasons (e.g., *Retired*, *Resigned*, *Transferred*, *End of Contract*) and official `date_concluded`.
* Provides audit tracking of historical appointments without cluttering the active workspace.

### 11.3 Soft Delete & Trash Bin (`/appointments/trash`)
* **Soft Delete:** Only Administrators can soft-delete appointment records, which sets `record_state = 'deleted'` and assigns a `deleted_at` timestamp.
* **Trash Bin View:** Admins can view all soft-deleted records.
* **Restore:** Admins can restore soft-deleted records back to `active` state with full data preservation (`/appointments/{id}/restore`).
* **Permanent Force Delete:** Admins can permanently purge records from the database (`/appointments/{id}/force-delete`).

---

## 12. PLANTILLA & DATA IMPORT MODULE

### 12.1 Excel Import Command (`import:data-excel`)
The system integrates master Plantilla dataset records from `DATA.xlsx` stored in the application root:
* Command: `php artisan import:data-excel`
* Process: Reads rows using `PhpOffice\PhpSpreadsheet\IOFactory`, truncates/refreshes the `plantilla_items` table, and bulk-inserts records in batches of 1,000.
* Captured Fields: Level, School ID, School Name, City/Municipality, Item Number (`data`), Position Title, Sex, Eligibility, First Time Used of Eligibility, Position Level, Nature of Appointment, Status of Appointment.

### 12.2 Autocomplete Integration API
The endpoint `/appointments/plantilla-search` serves real-time JSON responses to frontend search inputs on the create/edit forms, allowing rapid filtering across thousands of plantilla items.

---

## 13. USER MANAGEMENT & INVITATION MODULE (Admin Only)

The **User Management Module (`/admin/users`)** provides control over user accounts, security roles, and onboarding.

### 13.1 User Account Overview
Displays all registered accounts with metrics:
* **All Users Count**
* **Active Users Count** (`is_active = true`)
* **Inactive / Pending Users Count** (`is_active = false`)

### 13.2 Creating & Inviting Users
Administrators can onboard new personnel via two methods:
1. **Direct Add via Invitation (`/admin/users/create`):** Submitting user details dispatches an email invitation token. The user account is formally created and activated only after the invite recipient accepts via `/invitation/accept/{token}`.
2. **Admin Store:** Creates a user record directly in the system.

### 13.3 Role Management & Account Control
* **Assign Role:** Modify user role between `hr` (HR Officer) and `admin` (Administrator).
* **Activate / Deactivate:** Toggle account status. Deactivated users are immediately blocked from logging in.
* **Delete Pending Accounts:** Allows purging unverified or pending user requests.

### 13.4 Password Reset Administration (`/admin/passwords`)
Allows Administrators to securely overwrite lost user passwords with enforced complexity checks.

---

## 14. DATABASE SCHEMA REFERENCE

### 14.1 `users` Table
Stores user credentials, security roles, and account state.

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key, Auto-Increment | Unique user identifier. |
| `name` | `varchar(255)` | Required | Full user name. |
| `email` | `varchar(255)` | Unique, Required | Account email address (login credential). |
| `email_verified_at` | `timestamp` | Nullable | Timestamp when email was verified. |
| `password` | `varchar(255)` | Required | Hashed password string. |
| `role` | `enum('hr','admin')` | Default: `'hr'` | User security role. |
| `requested_role` | `varchar(255)` | Nullable | Role requested during invitation or signup. |
| `is_active` | `boolean` | Default: `true` | Account access status flag. |
| `remember_token` | `varchar(100)` | Nullable | Session "remember me" token. |
| `created_at` | `timestamp` | Nullable | Account creation timestamp. |
| `updated_at` | `timestamp` | Nullable | Account last update timestamp. |

### 14.2 `appointments` Table
Stores complete appointment data, tracking timestamps, and lifecycle state.

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key, Auto-Increment | Unique appointment record identifier. |
| `transaction_number` | `varchar(255)` | Nullable, Unique | Official Records tracking number. |
| `user_id` | `bigint` | Foreign Key (users.id), Nullable | User ID of the HR encoder. |
| `last_name` | `varchar(255)` | Required | Candidate last name. |
| `first_name` | `varchar(255)` | Required | Candidate first name. |
| `middle_name` | `varchar(255)` | Nullable | Candidate middle name. |
| `extension_name` | `varchar(255)` | Nullable | Name extension (Jr., Sr., III). |
| `sex` | `enum` | Nullable | `'Male'`, `'Female'`, `'Prefer not to say'`. |
| `date_of_birth` | `date` | Nullable | Candidate birthdate. |
| `tin` | `varchar(255)` | Nullable | Tax Identification Number. |
| `pwd` | `enum('Yes','No')` | Default: `'No'` | Persons with Disability status. |
| `type_of_disability` | `varchar(255)` | Nullable | Specified disability type if PWD. |
| `ip_group_member` | `enum('Yes','No')` | Default: `'No'` | Indigenous People group member status. |
| `ethnicity` | `varchar(255)` | Nullable | Specified ethnicity group. |
| `position_title` | `varchar(255)` | Required | Title of the appointed position. |
| `position_from` | `varchar(255)` | Nullable | Previous position title / station details. |
| `position_to` | `date` | Nullable | Expected tenure end date. |
| `salary_grade` | `varchar(255)` | Nullable | Civil Service Salary Grade (1–33). |
| `salary_grade_step` | `varchar(255)` | Nullable | Salary step increment (1–8). |
| `monthly_salary` | `decimal(12,2)` | Nullable | Monthly basic salary rate in PHP. |
| `employee_status` | `enum` | Required | `'Permanent'`, `'Temporary'`, `'Casual'`, `'Contractual'`, `'Coterminous'`. |
| `compensation_words` | `varchar(255)` | Nullable | Salary written in formal words. |
| `compensation_numbers` | `decimal(12,2)` | Nullable | Salary formatted numerically. |
| `nature_of_appointment` | `enum` | Required | `'Original'`, `'Promotion'`, `'Transfer'`, `'Reappointment'`, `'Reinstatement'`. |
| `reason` | `varchar(255)` | Nullable | Reason for appointment / vacancy. |
| `position_level` | `enum` | Nullable | `'First Level'`, `'Second Level'`, `'Third Level'`. |
| `appointment_status` | `enum` | Nullable | `'Original'`, `'Renewal'`, `'Reappointment'`. |
| `department` | `varchar(255)` | Nullable | Department / Division assignment. |
| `school_district` | `varchar(255)` | Nullable | School District name. |
| `school` | `varchar(255)` | Nullable | School / Office station name. |
| `sector` | `varchar(255)` | Nullable | Public / Private sector. |
| `agency_name` | `varchar(255)` | Nullable | Agency name (DepEd Cavite). |
| `plantilla_item_number` | `varchar(255)` | Nullable | Official Plantilla Item Number. |
| `plantilla_page_number` | `varchar(255)` | Nullable | Page reference in PSIPOP / Plantilla. |
| `odc_number` | `varchar(255)` | Nullable | Organizational Design Code number. |
| `date_received_records` | `date` | Nullable | Date received by Records Unit. |
| `date_received_hr` | `date` | Nullable | Date received by HR Personnel Unit. |
| `previous_incumbent` | `varchar(255)` | Nullable | Name of prior position holder. |
| `incumbent` | `varchar(255)` | Nullable | Name of current incumbent. |
| `publication_mode` | `enum` | Nullable | Bulletin / Online publication channel. |
| `publication_date_from` | `date` | Nullable | Publication posting start date. |
| `publication_date_to` | `date` | Nullable | Publication posting end date. |
| `assessment_date` | `date` | Nullable | HRMPSB assessment date. |
| `deliberation_date` | `date` | Nullable | PSB final deliberation date. |
| `eligibility_type` | `varchar(255)` | Nullable | Civil Service / RA 1080 eligibility. |
| `eligibility_validity` | `date` | Nullable | License / eligibility expiration date. |
| `eligibility_first_used` | `enum('Yes','No')` | Nullable | First-time eligibility usage indicator. |
| `date_original_appointment` | `date` | Nullable | First government appointment date. |
| `date_last_promotion` | `date` | Nullable | Date of last position promotion. |
| `date_of_signing` | `date` | Nullable | Appointment paper signing date. |
| `senior_high_school` | `varchar(255)` | Nullable | Senior High School flag (`'Yes'`, `'No'`). |
| `senior_high_strand` | `varchar(255)` | Nullable | SHS track / strand assignment. |
| `non_teaching` | `varchar(255)` | Nullable | Non-teaching position indicator. |
| `record_state` | `enum` | Default: `'new'` | `'new'`, `'active'`, `'in_progress'`, `'completed'`, `'archived'`, `'deleted'`, `'concluded'`. |
| `conclusion_reason` | `varchar(255)` | Nullable | Formal reason for concluding record. |
| `date_concluded` | `date` | Nullable | Effective conclusion date. |
| `afa_downloaded_at` | `datetime` | Nullable | Appointment Form download timestamp. |
| `checklist_downloaded_at` | `datetime` | Nullable | Checklist download timestamp. |
| `rai_downloaded_at` | `datetime` | Nullable | RAI report download timestamp. |
| `final_deliberation_downloaded_at` | `datetime` | Nullable | Final deliberation download timestamp. |
| `encoding_personnel` | `varchar(255)` | Nullable | Name of encoding HR officer. |
| `encoded_at` | `timestamp` | Default: `CURRENT` | Initial encoding timestamp. |
| `deleted_at` | `timestamp` | Nullable | Soft delete timestamp (Trash Bin). |
| `created_at` / `updated_at` | `timestamp` | Nullable | Record creation and update timestamps. |

### 14.3 `plantilla_items` Table
Master reference store for plantilla item lookups imported from `DATA.xlsx`.

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key, Auto-Increment | Plantilla item unique ID. |
| `level` | `varchar(255)` | Nullable | Level indicator (Elementary, JHS, SHS). |
| `school_id` | `varchar(255)` | Nullable | DepEd School ID. |
| `school_name` | `varchar(255)` | Nullable | School / Station name. |
| `city_municipality` | `varchar(255)` | Nullable | City or municipality location. |
| `data` | `varchar(255)` | Indexed | Plantilla Item Number. |
| `position` | `varchar(255)` | Nullable | Official position title. |
| `sex` | `varchar(255)` | Nullable | Sex requirement if applicable. |
| `eligibility` | `varchar(255)` | Nullable | Required eligibility qualification. |
| `first_time_used_of_eligibility` | `varchar(255)` | Nullable | Eligibility first usage reference. |
| `position_level` | `varchar(255)` | Nullable | Position level classification. |
| `nature_of_appointment` | `varchar(255)` | Nullable | Nature of appointment classification. |
| `status_of_appointment` | `varchar(255)` | Nullable | Position status classification. |
| `created_at` / `updated_at` | `timestamp` | Nullable | System timestamps. |

### 14.4 `invitations` Table
Manages registration invite tokens sent to new staff members.

| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | `bigint` | Primary Key, Auto-Increment | Invitation record ID. |
| `email` | `varchar(255)` | Indexed, Required | Recipient email address. |
| `name` | `varchar(255)` | Required | Recipient full name. |
| `role` | `varchar(255)` | Required | Security role assigned (`'hr'`, `'admin'`). |
| `password` | `varchar(255)` | Required | Temporary hashed password. |
| `token` | `varchar(255)` | Unique, Indexed | Cryptographic invite token string. |
| `expires_at` | `timestamp` | Nullable | Token expiration timestamp. |
| `used_at` | `timestamp` | Nullable | Invite acceptance timestamp. |
| `created_at` / `updated_at` | `timestamp` | Nullable | Record creation/update timestamps. |

---

## 15. POSITION TO SALARY GRADE MAPPING REFERENCE

PAMS incorporates automated resolution of DepEd standard position titles to Civil Service Salary Grades:

| Position Title | Salary Grade (SG) | Position Title | Salary Grade (SG) |
| :--- | :---: | :--- | :---: |
| **Administrative Aide I** | SG 1 | **Administrative Officer V** | SG 18 |
| **Farm Worker I / Watchman I** | SG 2 | **Master Teacher I** | SG 18 |
| **Administrative Aide III / Security Guard I** | SG 3 | **Planning Officer III / Medical Officer II** | SG 18 |
| **Administrative Aide IV / Dental Aide** | SG 4 | **Assistant School Principal II** | SG 19 |
| **Handicraft Worker II / Security Guard II** | SG 5 | **Accountant III / Engineer III** | SG 19 |
| **Administrative Aide VI / Laboratory Technician I** | SG 6 | **School Principal I / Head Teacher VI** | SG 19 |
| **Administrative Assistant I** | SG 7 | **IT Officer I / Sr. Education Program Specialist** | SG 19 |
| **Administrative Assistant II** | SG 8 | **Assistant School Principal III** | SG 20 |
| **Administrative Assistant III** | SG 9 | **School Principal II** | SG 20 |
| **Administrative Officer I / Legal Assistant I** | SG 10 | **Attorney III / Medical Officer III** | SG 21 |
| **Administrative Officer II / Teacher I** | SG 11 | **School Principal III** | SG 21 |
| **Guidance Counselor I / Project Dev Officer I** | SG 11 | **Education Program Supervisor** | SG 22 |
| **Accountant I / Teacher II** | SG 12 | **Public Schools District Supervisor (PSDS)** | SG 22 |
| **Guidance Counselor II / Special Science Teacher I** | SG 12 | **School Principal IV** | SG 22 |
| **Teacher III / Guidance Counselor III** | SG 13 | **Chief Education Supervisor** | SG 24 |
| **Head Teacher I / Special Education Teacher I** | SG 14 | **Assistant Schools Division Superintendent** | SG 25 |
| **Administrative Officer IV / Master Teacher II** | SG 15 | **Schools Division Superintendent (SDS)** | SG 26 |

---

## 16. CLI COMMANDS & UTILITIES REFERENCE

PAMS includes custom Artisan commands and standalone CLI tools for data management:

| Command / Utility | Execution Syntax | Description |
| :--- | :--- | :--- |
| **Import Plantilla Excel Data** | `php artisan import:data-excel` | Parses root `DATA.xlsx` spreadsheet and bulk-populates the `plantilla_items` table. |
| **Database Migrations** | `php artisan migrate` | Creates or updates all system database tables. |
| **Database Seeder** | `php artisan db:seed` | Populates default system roles, initial admin accounts, and salary matrices. |
| **Key Generation** | `php artisan key:generate` | Sets application encryption key in `.env`. |
| **Route List** | `php artisan route:list` | Displays complete registry of application web and API routes. |
| **User Diagnostics Utility** | `php scripts/check_users.php` | CLI helper script to audit registered users and active statuses. |
| **HR Diagnostics Utility** | `php check_hr.php` | CLI script to verify HR user roles and assigned appointments. |
| **RAI Test Script** | `php test_rai_original.php` | Diagnostic utility for testing Report on Appointment Issued (RAI) template generation. |

---

## 17. TECHNOLOGY STACK

| Component | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| **Backend Framework** | Laravel | 11.x / 12.x | MVC Web Application Framework |
| **Language** | PHP | 8.2+ | Server-side script execution engine |
| **Frontend Framework** | Blade & Alpine.js | 3.x | Reactive UI components & server-side rendering |
| **CSS Styling** | Tailwind CSS | 3.x | Modern utility-first CSS design system |
| **Asset Bundler** | Vite | 5.x / 6.x | High-speed frontend module builder |
| **Word Processing Engine** | PHPWord (`phpoffice/phpword`) | ^1.3 | Generation of `.docx` Appointment Forms and Deliberation templates |
| **Spreadsheet Engine** | PhpSpreadsheet (`phpoffice/phpspreadsheet`) | ^2.1 | Generation of `.xlsx` Checklists, RAI reports, and Excel data exports |
| **Database Engine** | SQLite / MySQL | 8.0+ | Relational data persistence engine |
| **Mail Gateway** | Laravel Mail & SMTP | Standard | Dispatching email invitations and security notifications |

---

## 18. GLOSSARY OF TERMS

* **PAMS:** Personnel Appointment Management System.
* **DepEd:** Department of Education.
* **SDO:** Schools Division Office (Cavite Province).
* **AFA:** Appointment Form Annex / KSS Form 33 (Official CSC Appointment Form).
* **RAI:** Report on Appointment Issued (CSC-mandated report submitted monthly/quarterly).
* **PSB / HRMPSB:** Human Resource Merit Promotion and Selection Board.
* **Plantilla Item Number:** Unique position item number assigned in the official Personal Services Itemization and Plantilla of Personnel (PSIPOP).
* **Transaction Number (TN):** Unique division tracking number issued by the Records Unit upon final validation.
* **SG & Step:** Salary Grade (1–33) and Step Increment (1–8) determining official employee compensation rates under the Salary Standardization Law (SSL).
* **ODC:** Organizational Design Code number.
* **TIN:** Tax Identification Number issued by the Bureau of Internal Revenue (BIR).
* **PWD:** Person with Disability.
* **IP Group:** Indigenous Peoples Group.
* **SHS:** Senior High School (Grades 11–12).
* **SDS:** Schools Division Superintendent.
* **ASDS:** Assistant Schools Division Superintendent.
* **CID:** Curriculum Implementation Division.
* **SGOD:** School Governance and Operations Division.
* **OSDS:** Office of the Schools Division Superintendent.

---

## 19. SYSTEM INTERFACE

### 19.1 Authentication & Login Interface (`/login`)
A clean, secure authentication portal featuring the official DepEd Cavite branding, system logo, and role-guarded sign-in credentials input.

### 19.2 Main Dashboard Interface (`/dashboard`)
Features real-time KPI overview cards (Total Encoded, Needs TN, Completed Today, Monthly Volume), date filters, and quick-action summary indicators.

### 19.3 Appointment List Interface (`/appointments`)
An interactive data table presenting active appointment records. Features real-time keyword search, date filtering, status pill filters, inline transaction number entry, action menus (View, Edit, Conclude, Download Documents), and pagination.

### 19.4 Appointment Encoding Form (`/appointments/create`)
A structured multi-step form divided into clear logical sections:
1. *Personal Information* (Name, Sex, Birthdate, TIN, PWD, IP, Ethnicity)
2. *Position & Salary Details* (Position Title, Salary Grade & Step lookup, Nature of Appointment, Employee Status)
3. *Plantilla & Station Metadata* (Plantilla Item Number autocomplete search, School, District, Incumbent details)
4. *Eligibility & Appointment History* (Eligibility type, validity, first-time used, original signing dates).

### 19.5 Archive Interface (`/appointments/archive`)
A streamlined view listing completed appointments. Displays assigned transaction numbers, encoding dates, candidate details, and direct download buttons for all generated official documents. Appointments are moved to this view when marked as completed via the "Mark completed" button on the Appointment Data page.

### 19.6 User Management Interface (`/admin/users`)
An administrative control center presenting metric cards (Total, Active, Inactive/Pending), tabbed user listings, role modification dropdowns, user activation toggles, and an **Add User (Invite)** modal.

---
*End of System Documentation.*
