# Secure-Movie-Website


This project is a secure web application built with PHP and MySQL for managing movie reservations. It implements authentication, security best practices, and dynamic data filtering while preventing common web vulnerabilities such as SQL Injection, Cross-Site Scripting (XSS), Cross-Site Request Forgery (CSRF), and Session Hijacking.

Features

User authentication with secure login and registration using hashed passwords
SQL Injection prevention through prepared statements
Session security with session fixation prevention, timeouts, and IP-based session locking
Cross-Site Scripting (XSS) prevention by escaping output
Dynamic filtering for searching and managing movie reservations
Admin dashboard for managing movies, reservations, and users
Technologies Used

Backend: PHP, MySQL
Frontend: HTML, CSS, JavaScript
Security Implementations: password_hash(), password_verify(), prepared statements, session management
Local Development Environment: XAMPP
Security Features

SQL Injection Prevention
All database queries use prepared statements to prevent SQL injection attacks. 
Example:
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");

$stmt->bind_param('s', $username);

$stmt->execute();


Cross-Site Scripting (XSS) Protection
User input is sanitized and escaped before being displayed. 
Example:
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

Secure Password Storage
Passwords are hashed using password_hash() before being stored in the database. The password verification process is done using password_verify() to compare hashed passwords securely.


Session Security
Session fixation prevention using session_regenerate_id(true)
Session timeout after a period of inactivity
Session hijacking prevention by binding sessions to the user's IP address


Cross-Site Request Forgery (CSRF) Protection
Session-based authentication ensures only logged-in users can perform actions.
CSRF tokens are recommended for additional protection.



Installation and Setup:

Clone the repository
git clone https://github.com/RazviRazvi/Secure-Movie-Website
cd Secure-Movie-App
Set up the database
Open XAMPP and start Apache and MySQL
Go to phpMyAdmin and import the Cinema.sql file
Configure config.php with your database credentials
Start the project
Place the PHP pages folder in the htdocs directory (C:\xampp\htdocs\Secure-Movie-App)
Open a browser and go to:
http://localhost/Rezervare_filme/login.php
Usage

Users can register and log in securely.
The dashboard allows users to make and manage movie reservations.
Admin users have additional privileges to modify movie data.
The filtering system enables users to search for movies and reservations dynamically.
Future Improvements

Implementing CSRF tokens for full protection
Adding email verification for new user accounts
Improving the user interface using a modern framework such as Bootstrap or Tailwind CSS
Deploying the project to an online hosting provider
Contact
Email: rgaleseanu@yahoo.com
LinkedIn: https://www.linkedin.com/in/razvan-galeseanu-46a515349/
