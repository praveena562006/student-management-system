# 🎓 EduTrack - Student Management System

EduTrack is a web-based Student Academic Management System developed using PHP, MySQL, HTML, CSS and JavaScript.

The application provides separate Admin and Student portals for managing and accessing academic information.

---

## ✨ Features

### 👨‍💼 Admin Portal

- Secure Admin Login
- Admin Dashboard
- Add Students
- View Students
- Edit Student Information
- Delete Students
- Attendance Management
- Attendance History
- Marks Management
- Automatic Grade Calculation
- Subject Management
- Add, Edit and Delete Subjects
- Subject Search and Filtering
- Reports and Analytics

### 🎓 Student Portal

- Student Login
- Student Dashboard
- View Personal Profile
- View Attendance
- View Marks
- View Grades
- View Academic Performance

---

## 🔐 Role-Based Access

EduTrack provides separate access permissions for administrators and students.

### Admin

Administrators can manage students, attendance, marks, subjects and reports.

### Student

Students can only access their own academic information such as attendance, marks and profile details.

---

## 🛠 Technologies Used

### Frontend

- HTML5
- CSS3
- JavaScript

### Backend

- PHP

### Database

- MySQL

### Development Environment

- XAMPP
- Apache
- phpMyAdmin

### Version Control

- Git
- GitHub

---

## 📚 Subject Management

Administrators can:

- Add Subjects
- Edit Subjects
- Delete Subjects
- Search Subjects
- Filter Subjects
- Manage Semester Information
- Manage Subject Credits

---

## 📅 Attendance Management

The system allows administrators to record and manage student attendance.

Students can securely view their own attendance information through the Student Portal.

---

## 📝 Marks Management

Administrators can enter and manage student marks.

Students can view their marks and academic performance through their individual accounts.

---

## 🚀 How to Run the Project Locally

### 1. Install XAMPP

Install XAMPP and start:

- Apache
- MySQL

### 2. Copy Project

Place the project folder inside:

C:/xampp/htdocs/

### 3. Create Database

Open phpMyAdmin and create a database named:

student_db

### 4. Import Database

Import:

database/student_db.sql

into the student_db database.

### 5. Database Configuration

Configure the database connection inside:

db.php

For a default XAMPP installation:

Host: localhost

Username: root

Password: empty

Database: student_db

### 6. Run Application

Open the project through localhost in your browser.

---

## 🔒 Security

The application uses session-based authentication and role-based access control to separate Administrator and Student functionality.

---

## 👩‍💻 Developer

**Praveena Adabala**

Computer Science and Business Systems

---

## 📌 Project Purpose

This project was developed to demonstrate practical implementation of web technologies including PHP, MySQL, HTML, CSS, JavaScript, database management, authentication and role-based access control.