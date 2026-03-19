# Clinic Medicine Record Management System

A simple, self-service clinic record system where students log their own information when receiving medicine.

## 🏥 How It Works

1. Student comes to clinic and asks for medicine
2. Staff gives medicine to student
3. Student fills out the form with their info
4. Student leaves
5. Admin can manage all records

## 🎯 Features

### For Students (Public Access)
- **Self-service form** - Quick and easy
- **Medicine dropdown** - Select from available medicines with stock levels
- **View all records** - See complete history
- **No login required** - Fast access

### For Admins (Login Required)
- **Full CRUD operations** - Create, Read, Update, Delete
- **Medicine management** - Add, edit, delete medicines
- **Stock management** - Increase/decrease medicine stock
- **Dashboard** - Overview with statistics
- **Secure login** - Password protected

## 📋 Student Form Fields

1. **Patient Name** - Student's full name
2. **Patient Type** - Student / Faculty / Staff
3. **Contact Number** - Phone number
4. **Medicine** - Dropdown of available medicines (shows stock)
5. **Quantity** - How many units received
6. **Reason / Symptoms** - Why they needed medicine
7. **Date** - Date of visit

## 🎨 Design

- **Colors:** Navy blue, gold, and red (school theme)
- **Style:** Clean, modern, professional
- **No gradients:** Simple and fast
- **Responsive:** Works on all devices

## 🗂️ File Structure

```
recordManagement/
├── public/
│   ├── index.php              # Public form (students)
│   ├── login.php              # Admin login
│   ├── medicine.php           # (Not used - medicine is admin only)
│   ├── admin/                 # Admin-only area
│   │   ├── dashboard.php      # Admin home
│   │   ├── records.php        # Manage records (CRUD)
│   │   └── medicine.php       # Manage medicines (CRUD)
│   └── js/
│       ├── app.js             # Public page JavaScript
│       ├── admin-records.js   # Admin records JavaScript
│       └── admin-medicine.js  # Admin medicine JavaScript
├── app/
│   ├── Models/                # Database models
│   │   ├── PatientRecord.php
│   │   ├── Patient.php
│   │   ├── Medicine.php
│   │   └── Admin.php
│   ├── Controllers/           # Business logic (optional MVC)
│   ├── Config/                # Configuration
│   │   ├── Config.php
│   │   └── Database.php
│   └── includes/
│       └── check_session.php  # Session validation
├── routes/
│   ├── api.php                # Patient records API
│   ├── medicine_api.php       # Medicine API
│   └── auth_api.php           # Authentication API
└── database/
    └── record_management.sql  # Database schema
```

## 🚀 Getting Started

### 1. Database Setup

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create database: `record_management`
3. Import: `database/record_management.sql`

### 2. Access the System

**Public (Students):**
- URL: `http://localhost/Projects/recordManagement/public/index.php`
- No login required
- Fill out form and submit

**Admin:**
- URL: `http://localhost/Projects/recordManagement/public/login.php`
- Username: `admin`
- Password: `admin123`

### 3. Add Medicines (Important!)

Before students can log records:
1. Login as admin
2. Go to Medicine Management
3. Add medicines (e.g., Paracetamol, Ibuprofen, Amoxicillin)
4. Set initial stock levels

## 📊 Database Tables

### `patient`
- `patient_id` (Primary Key)
- `fullname`
- `patient_type` (Student/Faculty/Staff)
- `contact_no`
- `dateCreated`
- `dateDeleted` (for soft delete)

### `patient_record`
- `record_id` (Primary Key)
- `patient_id` (Foreign Key)
- `medicine_id` (Foreign Key)
- `quantity`
- `reason`
- `date_given`
- `dateCreated`
- `dateDeleted` (for soft delete)

### `medicine`
- `medicine_id` (Primary Key)
- `medicine_name`
- `description`
- `current_stock`
- `dateCreated`
- `dateDeleted` (for soft delete)

### `admin`
- `admin_id` (Primary Key)
- `username`
- `password` (hashed)
- `fullname`
- `email`
- `contact_no`
- `dateCreated`
- `dateDeleted` (for soft delete)

## 🔒 Security Features

1. **Password Hashing** - Uses `password_hash()` and `password_verify()`
2. **Session Management** - Secure session handling
3. **SQL Injection Prevention** - PDO with prepared statements
4. **Input Sanitization** - `htmlspecialchars()` and `strip_tags()`
5. **Soft Deletes** - Records never truly deleted
6. **Protected Admin Area** - Session-based access control

## 💡 Usage Tips

### For Clinic Staff:
1. Keep medicines stocked (check admin dashboard regularly)
2. Review records daily
3. Edit/delete incorrect entries as needed
4. Update medicine stock when new supplies arrive

### For Students:
1. Fill out form honestly and completely
2. Select correct medicine from dropdown
3. Enter accurate quantity received
4. Describe symptoms/reason clearly

## 🛠️ Troubleshooting

### Medicine dropdown is empty:
- Login as admin
- Go to Medicine Management
- Add medicines with stock

### Can't login as admin:
- Check username: `admin`
- Check password: `admin123`
- Verify database has admin user

### Records not showing:
- Check browser console for errors (F12)
- Verify database connection in Config/Database.php
- Ensure `dateDeleted` is NULL for active records

### 500 Error:
- Check PHP error logs
- Verify database credentials
- Check file permissions

## 📝 API Endpoints

### Patient Records API (`routes/api.php`)
- `GET ?action=read` - Get all records
- `GET ?action=read_one&id={id}` - Get single record
- `POST ?action=create` - Create new record
- `POST ?action=update` - Update record
- `POST ?action=delete` - Delete record (soft delete)

### Medicine API (`routes/medicine_api.php`)
- `GET ?action=read` - Get all medicines
- `GET ?action=read_one&id={id}` - Get single medicine
- `POST ?action=create` - Create medicine
- `POST ?action=update` - Update medicine
- `POST ?action=delete` - Delete medicine (soft delete)
- `POST ?action=update_stock` - Increase/decrease stock

### Auth API (`routes/auth_api.php`)
- `POST ?action=login` - Admin login
- `GET ?action=logout` - Admin logout
- `GET ?action=check` - Check session status

## 🎉 Features Implemented

- ✅ Self-service student form
- ✅ Medicine dropdown with stock display
- ✅ Admin login/logout
- ✅ Full CRUD for records (admin)
- ✅ Full CRUD for medicines (admin)
- ✅ Stock management
- ✅ Dashboard with statistics
- ✅ Soft deletes
- ✅ Session protection
- ✅ Password hashing
- ✅ Clean school theme
- ✅ Responsive design
- ✅ No "Created By" tracking (students log themselves)

## 📚 Documentation Files

- `README.md` - This file
- `SIMPLIFIED_SYSTEM.md` - Explanation of the simplified workflow
- `MEDICINE_SELECT_UPDATE.md` - Medicine dropdown feature
- `ADMIN_AREA_COMPLETE.md` - Admin area setup guide
- `MVC_PATTERN_GUIDE.md` - MVC architecture (optional)
- `MEDICINE_MANAGEMENT_GUIDE.md` - Medicine system guide

## 🔄 Future Enhancements (Optional)

1. **Stock Alerts** - Email when medicine is low
2. **Reports** - Generate PDF/CSV reports
3. **Charts** - Visual statistics and trends
4. **Search** - Filter records by date, patient, medicine
5. **Backup** - Automatic database backups
6. **Print** - Print prescriptions/receipts

## 👨‍💻 Technical Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP)
- **Architecture:** MVC (Models + Routes)

## 📞 Support

For issues or questions:
1. Check the documentation files
2. Review the troubleshooting section
3. Check browser console for errors
4. Review PHP error logs in XAMPP

## 🎓 Perfect for:

- School clinics
- College health centers
- Small medical facilities
- Student health services
- Quick medicine dispensing

---

**Made with ❤️ for your school clinic**

*Simple. Fast. Secure.*
