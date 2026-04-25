# 🚍 Bus Management System

Bus Management System is a DBMS-based web application developed using PHP and MySQL.  
It is designed to manage buses, routes, drivers, and schedules efficiently through a web interface.  
This project is suitable for learning full-stack web development using core PHP.

---

## 🧠 Project Overview

The system provides an admin interface to:

- Manage bus details
- Manage routes and schedules
- Add and manage drivers
- Store and retrieve data using MySQL
- Perform CRUD operations (Create, Read, Update, Delete)
- Maintain structured database connectivity

This project demonstrates practical implementation of database management concepts using PHP and MySQL.

---

## ✨ Features

✔ Admin Dashboard  
✔ Bus Management  
✔ Route Management  
✔ Driver Management  
✔ Schedule Handling  
✔ Database Connectivity  
✔ CRUD Operations  
✔ Simple UI using HTML, CSS & JavaScript  

---

## 🗂️ Project Structure

Bus_Mng_System/
│
├── admin/               # Admin management pages  
├── css/                 # Stylesheets  
├── js/                  # JavaScript files  
├── reports/             # Report modules  
├── uploads/             # Uploaded assets  
├── db.php               # Database configuration  
├── db_connect.php       # Database connection file  
├── index.php            # Main entry point  
├── test.php             # Testing file  

---

## 🛠️ Technologies Used

- PHP  
- MySQL  
- HTML  
- CSS  
- JavaScript  
- XAMPP / Apache Server  

---

## 🚀 How to Run the Project

1. Install XAMPP (or WAMP/LAMP).
2. Start Apache and MySQL.
3. Clone the repository:
   git clone https://github.com/Hyper7711/Bus_Mng_System.git
4. Move the project folder to:
   C:\xampp\htdocs\
5. Open phpMyAdmin and create a database (e.g., bus_db).
6. Import the SQL file if available.
7. Open browser and run:
   http://localhost/Bus_Mng_System/

---

## 🔐 Database Configuration

Update your database credentials inside db_connect.php:

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bus_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

---

## 📌 Learning Objectives

- Understand PHP-MySQL connectivity
- Perform CRUD operations
- Implement session handling
- Structure a full-stack DBMS project
- Build a functional admin dashboard

---

## 📄 License

This project is created for educational purposes.
