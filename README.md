# Repository

## 🔐 Password Manager

### 📌 Project Overview
This project is a **secure password manager** that allows users to **store encrypted passwords** and manage them with ease. Built using **PHP & MySQL** for the backend and **JavaScript + CSS** for a clean, responsive frontend.

---

### 🚀 Features
- 🔒 **Encrypted password storage** to protect sensitive information.
- 🧩 **Modular system**: Add, edit, delete, and manage password fields.
- 👤 **Secure authentication** via session or cookie-based login.
- 🌍 **Multilingual interface** (Arabic and English).
- 🎨 **Clean and responsive UI** using modern CSS.
- 📱 **API-ready**: Fully usable with web, mobile, or other client apps.
- 📦 **No external dependencies** (no frameworks required).

---

### 🛠 Requirements
- **PHP 7.4+**
- **MySQL** or MariaDB
- **Apache/Nginx** (via XAMPP, WAMP, or Linux server)
- **Modern browser** (Chrome, Firefox, etc.)

---

### ⚙️ Installation
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/repository.git
   ```
2. Move it to your web directory (e.g., `htdocs` in XAMPP or `/var/www/html` in Linux).
3. Open your browser and navigate to:
   ```
   http://localhost/repository/index.php
   ```
4. Follow the installation wizard.

---

### 📡 API Integration
This password manager provides a flexible API for mobile and desktop integration.

- All data can be managed through HTTP requests (POST only).
- Includes request-based **token authentication**.
- Responses are returned in JSON format.

📥 **Download Postman collection:**  
[📂 RepositoryApi.postman_collection.json](docs/RepositoryApi.postman_collection.json)

---

### 🧪 Testing
The system includes full handling for:
- Session & Cookie token validation
- Key encryption & field validation
- Language switching with fallback defaults
- Invalid database state handling

---

### 📄 License
MIT License — Free for commercial and personal use.

---

### ✨ Contribution
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

---

> 💡 Built to keep sensitive data organized, encrypted, and always at your fingertips.