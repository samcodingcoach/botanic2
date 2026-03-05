# API Documentation - Botanic Hotel Management System

## Overview

This project uses a **RESTful API** structure built with **PHP (MySQLi)**. Each module follows a consistent CRUD pattern with separate files for each operation.

---

## Project Structure

```
api/
├── cabang/              # Branch management
│   ├── list.php         # READ - Get all branches
│   ├── new.php          # CREATE - Add new branch
│   └── update.php       # UPDATE - Update branch
├── cabangtipekamar/     # Branch room type management
│   ├── list.php         # READ - Get all branch room types
│   ├── new.php          # CREATE - Add new branch room type
│   ├── update.php       # UPDATE - Update branch room type
│   └── delete.php       # DELETE - Remove branch room type
├── fasilitas/           # Facility management
│   ├── list.php         # READ - Get all facilities
│   ├── new.php          # CREATE - Add new facility
│   ├── update.php       # UPDATE - Update facility
│   └── delete.php       # DELETE - Remove facility
├── fo/                  # Front Office management
│   ├── list.php         # READ - Get all FO records
│   └── delete.php       # DELETE - Remove FO record
├── guest/               # Guest management
│   ├── list.php         # READ - Get all guests
│   ├── new.php          # CREATE - Add new guest
│   ├── update.php       # UPDATE - Update guest
│   └── delete.php       # DELETE - Remove guest
├── tipe_kamar/          # Room type management
│   ├── list.php         # READ - Get all room types
│   ├── new.php          # CREATE - Add new room type
│   ├── update.php       # UPDATE - Update room type
│   └── delete.php       # DELETE - Remove room type
└── users/               # User management
    ├── list.php         # READ - Get all users
    ├── new.php          # CREATE - Add new user
    ├── update.php       # UPDATE - Update user
    ├── delete.php       # DELETE - Remove user
    └── cek_login.php    # Authentication endpoint
```

---

## Standard Response Format

All API responses follow this JSON structure:

### Success Response
```json
{
    "success": true,
    "message": "Data berhasil diambil",
    "data": { ... },
    "count": 10
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description"
}
```

---

## Common Headers

All API files include these standard headers:

```php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");  // or POST, PUT, DELETE
header("Access-Control-Allow-Headers: Content-Type");
```

---

## Database Connection

All files require the database connection:

```php
require_once __DIR__ . '/../../config/koneksi.php';
```

**koneksi.php** configuration:
```php
$host = 'localhost';
$username = 'matos';
$password = '1234';
$database = 'botanic2';
date_default_timezone_set("Asia/Makassar");
$conn = new mysqli($host, $username, $password, $database);
```

---

## CRUD Implementation Patterns

### 1. READ (list.php)

**Purpose:** Retrieve all records from a table.

**Standard Pattern:**
```php
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    $query = "SELECT column1, column2, column3 FROM table_name ORDER BY id ASC";
    $result = $conn->query($query);

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data berhasil diambil",
            "data" => $data,
            "count" => count($data)
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Gagal mengambil data: " . $conn->error
        ];
    }
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ];
}

echo json_encode($response);
$conn->close();
?>
```

**Example - Guest List (with type casting):**
```php
$guestList[] = [
    "id_guest" => (int) $row['id_guest'],
    "nama_lengkap" => $row['nama_lengkap'],
    "email" => $row['email'],
    "aktif" => (int) $row['aktif'],
    "total_point" => (float) $row['total_point']
];
```

---

### 2. CREATE (new.php)

**Purpose:** Insert a new record into the database.

**Standard Pattern:**
```php
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// 1. Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ["success" => false, "message" => "Method not allowed"];
    echo json_encode($response);
    exit;
}

// 2. Parse input (support both form-data and JSON)
$inputData = [];
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);
} else {
    $inputData = $_POST;
}

// 3. Validate required fields
if (!isset($inputData['required_field']) || empty(trim($inputData['required_field']))) {
    $response = ["success" => false, "message" => "required_field wajib diisi"];
    echo json_encode($response);
    exit;
}

// 4. Check for duplicates (if applicable)
$checkQuery = "SELECT id FROM table WHERE unique_field = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $value);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response = ["success" => false, "message" => "Data sudah ada, tidak boleh duplikat"];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// 5. Handle file upload (if applicable)
$fileName = null;
$uploadDir = __DIR__ . '/../../images/';
$maxFileSize = 1 * 1024 * 1024; // 1MB

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['image']['size'] > $maxFileSize) {
        $response = ["success" => false, "message" => "Ukuran file terlalu besar. Maksimal 1MB."];
        echo json_encode($response);
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $fileName = 'prefix_' . $timestamp . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        $response = ["success" => false, "message" => "Ekstensi file tidak diperbolehkan"];
        echo json_encode($response);
        exit;
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        $response = ["success" => false, "message" => "Gagal mengupload file"];
        echo json_encode($response);
        exit;
    }
}

// 6. Prepare INSERT query
$query = "INSERT INTO table (column1, column2, column3) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);

// Bind parameters
$field1 = isset($inputData['field1']) ? trim($inputData['field1']) : null;
$field2 = isset($inputData['field2']) ? trim($inputData['field2']) : null;
$stmt->bind_param("sss", $field1, $field2, $fileName);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id" => $newId,
        "field1" => $field1,
        "fileName" => $fileName
    ];

    $response = [
        "success" => true,
        "message" => "Data berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if insert fails
    if ($fileName && file_exists($uploadDir . $fileName)) {
        unlink($uploadDir . $fileName);
    }

    $response = [
        "success" => false,
        "message" => "Gagal menyimpan data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
```

**Special Case - Guest/User Registration (with password hashing):**
```php
// Validate password minimum length
if (strlen($password) < 8) {
    $response = ["success" => false, "message" => "password minimal 8 karakter"];
    echo json_encode($response);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert with hashed password
$query = "INSERT INTO guest (nama_lengkap, email, wa, password, kota, aktif, total_point) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$emailValue = !empty($email) ? $email : null;
$stmt->bind_param("sssssdi", $nama_lengkap, $emailValue, $wa, $hashedPassword, $kota, $total_point, $aktif);
```

---

### 3. UPDATE (update.php)

**Purpose:** Update an existing record.

**Standard Pattern:**
```php
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// 1. Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    $response = ["success" => false, "message" => "Method not allowed"];
    echo json_encode($response);
    exit;
}

// 2. Parse input
$inputData = [];
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);
} else {
    $inputData = $_POST;
}

// 3. Validate required ID
if (!isset($inputData['id']) || empty($inputData['id'])) {
    $response = ["success" => false, "message" => "id wajib diisi"];
    echo json_encode($response);
    exit;
}

$id = (int) $inputData['id'];

// 4. Check if record exists and get old data
$checkQuery = "SELECT id, unique_field, image FROM table WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = ["success" => false, "message" => "Data tidak ditemukan"];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldUniqueField = $oldData['unique_field'];
$oldImage = $oldData['image'];
$stmt->close();

// 5. Validate and check for duplicates (if unique field is being changed)
if (isset($inputData['unique_field'])) {
    $newUniqueField = trim($inputData['unique_field']);

    if (empty($newUniqueField)) {
        $response = ["success" => false, "message" => "unique_field tidak boleh kosong"];
        echo json_encode($response);
        exit;
    }

    // Check duplicate excluding current record
    $checkQuery = "SELECT id FROM table WHERE unique_field = ? AND id != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $newUniqueField, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Data sudah ada, tidak boleh duplikat"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();
    $uniqueField = $newUniqueField;
} else {
    $uniqueField = $oldUniqueField;
}

// 6. Handle file upload
$imageName = null;
$deleteOldImage = false;
$uploadDir = __DIR__ . '/../../images/';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $imageName = 'prefix_' . $timestamp . '.' . $fileExtension;
    $uploadPath = $uploadDir . $imageName;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        $response = ["success" => false, "message" => "Ekstensi file tidak diperbolehkan"];
        echo json_encode($response);
        exit;
    }

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        $response = ["success" => false, "message" => "Gagal mengupload file"];
        echo json_encode($response);
        exit;
    }

    $deleteOldImage = true;
} else {
    $imageName = $oldImage;
}

// 7. Prepare UPDATE query
$query = "UPDATE table
          SET column1 = ?, column2 = ?, unique_field = ?, image = ?
          WHERE id = ?";

$stmt = $conn->prepare($query);

$field1 = isset($inputData['field1']) ? trim($inputData['field1']) : null;
$field2 = isset($inputData['field2']) ? trim($inputData['field2']) : null;

$stmt->bind_param("ssssi", $field1, $field2, $uniqueField, $imageName, $id);

if ($stmt->execute()) {
    // Delete old image if new one uploaded
    if ($deleteOldImage && !empty($oldImage) && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
    }

    $responseData = [
        "id" => $id,
        "unique_field" => $uniqueField,
        "image" => $imageName
    ];

    $response = [
        "success" => true,
        "message" => "Data berhasil diupdate",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if update fails
    if ($deleteOldImage && $imageName && file_exists($uploadDir . $imageName)) {
        unlink($uploadDir . $imageName);
    }

    $response = [
        "success" => false,
        "message" => "Gagal mengupdate data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
```

**Special Case - Guest Update (dynamic fields with password verification):**
```php
// Verify password before update
$checkQuery = "SELECT id_guest, password FROM guest WHERE id_guest = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_guest);
$stmt->execute();
$result = $stmt->get_result();

if (!password_verify($inputPassword, $oldData['password'])) {
    $response = ["success" => false, "message" => "password tidak sesuai"];
    $stmt->close();
    echo json_encode($response);
    exit;
}

// Build dynamic update query
$updateFields = [];
$types = "";
$params = [];

if (isset($inputData['nama_lengkap']) && !empty(trim($inputData['nama_lengkap']))) {
    $updateFields[] = "nama_lengkap = ?";
    $types .= "s";
    $params[] = trim($inputData['nama_lengkap']);
}

if (isset($inputData['new_password']) && !empty(trim($inputData['new_password']))) {
    $newPassword = trim($inputData['new_password']);
    if (strlen($newPassword) < 8) {
        $response = ["success" => false, "message" => "password baru minimal 8 karakter"];
        echo json_encode($response);
        exit;
    }
    $updateFields[] = "password = ?";
    $types .= "s";
    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
}

if (empty($updateFields)) {
    $response = ["success" => false, "message" => "Tidak ada data yang diupdate"];
    echo json_encode($response);
    exit;
}

$query = "UPDATE guest SET " . implode(", ", $updateFields) . " WHERE id_guest = ?";
$types .= "i";
$params[] = $id_guest;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
```

---

### 4. DELETE (delete.php)

**Purpose:** Remove a record from the database.

**Standard Pattern:**
```php
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// 1. Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ["success" => false, "message" => "Method not allowed"];
    echo json_encode($response);
    exit;
}

// 2. Parse input
$inputData = [];
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

if ($jsonData !== null) {
    $inputData = $jsonData;
} else {
    $inputData = $_POST;
}

// 3. Validate required ID
if (!isset($inputData['id']) || empty($inputData['id'])) {
    $response = ["success" => false, "message" => "id wajib diisi"];
    echo json_encode($response);
    exit;
}

$id = (int) $inputData['id'];

// 4. Check if record exists and get image filenames (if applicable)
$checkQuery = "SELECT id, image1, image2 FROM table WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = ["success" => false, "message" => "Data tidak ditemukan"];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldImage1 = $oldData['image1'];
$oldImage2 = $oldData['image2'];
$stmt->close();

// 5. Perform delete
$deleteQuery = "DELETE FROM table WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete associated files
    $uploadDir = __DIR__ . '/../../images/';

    if (!empty($oldImage1)) {
        $imagePath1 = $uploadDir . $oldImage1;
        if (file_exists($imagePath1)) {
            unlink($imagePath1);
        }
    }

    if (!empty($oldImage2)) {
        $imagePath2 = $uploadDir . $oldImage2;
        if (file_exists($imagePath2)) {
            unlink($imagePath2);
        }
    }

    $response = [
        "success" => true,
        "message" => "Data berhasil dihapus",
        "data" => ["id" => $id]
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal menghapus data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
```

**Special Case - Guest Delete (with password verification):**
```php
// Verify password before delete
$checkQuery = "SELECT id_guest, password FROM guest WHERE id_guest = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_guest);
$stmt->execute();
$result = $stmt->get_result();

if (!password_verify($inputPassword, $guestData['password'])) {
    $response = ["success" => false, "message" => "password tidak sesuai"];
    $stmt->close();
    echo json_encode($response);
    exit;
}
```

**Special Case - User Delete (minimum user constraint):**
```php
// Check total users count
$countQuery = "SELECT COUNT(*) as total FROM users";
$countResult = $conn->query($countQuery);
$countData = $countResult->fetch_assoc();
$totalUsers = (int) $countData['total'];

if ($totalUsers <= 1) {
    $response = [
        "success" => false,
        "message" => "Tidak dapat menghapus user. Minimal harus ada 1 user di sistem."
    ];
    echo json_encode($response);
    exit;
}
```

---

### 5. AUTHENTICATION (cek_login.php)

**Purpose:** Authenticate user credentials.

**Pattern:**
```php
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ["success" => false, "message" => "Method not allowed"];
    echo json_encode($response);
    exit;
}

// Parse input
$inputData = [];
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

if (strpos($contentType, 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);
} else {
    $inputData = $_POST;
}

// Validate required fields
if (!isset($inputData['username']) || empty(trim($inputData['username']))) {
    $response = ["success" => false, "message" => "username wajib diisi"];
    echo json_encode($response);
    exit;
}

if (!isset($inputData['password']) || empty(trim($inputData['password']))) {
    $response = ["success" => false, "message" => "password wajib diisi"];
    echo json_encode($response);
    exit;
}

$username = trim($inputData['username']);
$password = trim($inputData['password']);

// Check user credentials (only active users)
$query = "SELECT u.id_users, u.username, u.password FROM users u 
          WHERE u.aktif = 1 AND u.username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "username tidak ditemukan atau user tidak aktif"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    $response = ["success" => false, "message" => "password salah"];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$stmt->close();

// Update last_login
$updateQuery = "UPDATE users SET last_login = NOW() WHERE id_users = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("i", $user['id_users']);
$updateStmt->execute();
$updateStmt->close();

// Login successful
$response = [
    "success" => true,
    "message" => "Login berhasil",
    "data" => [
        "id_users" => (int) $user['id_users'],
        "username" => $user['username']
    ]
];

// Set session
$_SESSION['id_users'] = $user['id_users'];
$_SESSION['username'] = $user['username'];

echo json_encode($response);
$conn->close();
?>
```

---

## Key Features & Best Practices

### 1. Input Handling
- Support both **form-data** and **JSON** request bodies
- Use `file_get_contents('php://input')` for raw JSON
- Fallback to `$_POST` if JSON parsing fails

### 2. Security
- **Prepared statements** for all SQL queries (prevent SQL injection)
- **Password hashing** using `password_hash()` and `password_verify()`
- **File upload validation** (size, type, extension)
- **Duplicate checking** before insert/update

### 3. File Upload
```php
// Standard upload pattern
$uploadDir = __DIR__ . '/../../images/';
$maxFileSize = 1 * 1024 * 1024; // 1MB
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Validate size
    if ($_FILES['image']['size'] > $maxFileSize) {
        // Handle error
    }

    // Create directory
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate filename
    $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $fileName = 'prefix_' . $timestamp . '.' . $fileExtension;

    // Validate extension
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        // Handle error
    }

    // Move file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
        // Handle error
    }
}
```

### 4. Error Handling
- **Try-catch blocks** for READ operations
- **Rollback mechanism** for failed inserts/updates (delete uploaded files)
- **Consistent error messages** in Indonesian

### 5. Type Casting
```php
// Always cast numeric values in response
$responseData = [
    "id" => (int) $row['id'],
    "aktif" => (int) $row['aktif'],
    "total_point" => (float) $row['total_point']
];
```

### 6. Naming Conventions
- **Database:** snake_case (e.g., `id_cabang`, `nama_cabang`)
- **Files:** lowercase with underscores (e.g., `list.php`, `new.php`)
- **Response fields:** match database column names

---

## API Endpoints Summary

| Module | Endpoint | Method | Description |
|--------|----------|--------|-------------|
| **Cabang** | `/api/cabang/list.php` | GET | Get all branches |
| | `/api/cabang/new.php` | POST | Create new branch |
| | `/api/cabang/update.php` | POST/PUT | Update branch |
| **CabangTipeKamar** | `/api/cabangtipekamar/list.php` | GET | Get all branch room types |
| | `/api/cabangtipekamar/new.php` | POST | Create branch room type |
| | `/api/cabangtipekamar/update.php` | POST/PUT | Update branch room type |
| | `/api/cabangtipekamar/delete.php` | POST | Delete branch room type |
| **Fasilitas** | `/api/fasilitas/list.php` | GET | Get all facilities |
| | `/api/fasilitas/new.php` | POST | Create facility |
| | `/api/fasilitas/update.php` | POST/PUT | Update facility |
| | `/api/fasilitas/delete.php` | POST | Delete facility |
| **FO** | `/api/fo/list.php` | GET | Get all front office records |
| | `/api/fo/delete.php` | POST/DELETE | Delete FO record |
| **Guest** | `/api/guest/list.php` | GET | Get all guests |
| | `/api/guest/new.php` | POST | Create guest |
| | `/api/guest/update.php` | POST | Update guest (with password verification) |
| | `/api/guest/delete.php` | POST | Delete guest (with password verification) |
| **Tipe Kamar** | `/api/tipe_kamar/list.php` | GET | Get all room types |
| | `/api/tipe_kamar/new.php` | POST | Create room type |
| | `/api/tipe_kamar/update.php` | POST/PUT | Update room type |
| | `/api/tipe_kamar/delete.php` | POST | Delete room type |
| **Users** | `/api/users/list.php` | GET | Get all users |
| | `/api/users/new.php` | POST | Create user |
| | `/api/users/update.php` | POST/PUT | Update user |
| | `/api/users/delete.php` | POST | Delete user |
| | `/api/users/cek_login.php` | POST | User authentication |

---

## Example Request (using cURL)

### GET - List Branches
```bash
curl -X GET http://localhost/botanic/api/cabang/list.php
```

### POST - Create Branch (JSON)
```bash
curl -X POST http://localhost/botanic/api/cabang/new.php \
  -H "Content-Type: application/json" \
  -d '{
    "kode_cabang": "CAB001",
    "nama_cabang": "Botanic Hotel Jakarta",
    "alamat": "Jl. Sudirman No. 123",
    "gps": "-6.2088,106.8456",
    "hp": "081234567890"
  }'
```

### POST - Create Branch (with image)
```bash
curl -X POST http://localhost/botanic/api/cabang/new.php \
  -F "kode_cabang=CAB001" \
  -F "nama_cabang=Botanic Hotel Jakarta" \
  -F "foto=@/path/to/image.jpg"
```

### POST - Update Branch
```bash
curl -X POST http://localhost/botanic/api/cabang/update.php \
  -H "Content-Type: application/json" \
  -d '{
    "id_cabang": 1,
    "nama_cabang": "Updated Hotel Name",
    "alamat": "Updated Address"
  }'
```

### POST - Login
```bash
curl -X POST http://localhost/botanic/api/users/cek_login.php \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

---

## Notes

1. **All timestamps** use `Asia/Makassar` timezone
2. **File uploads** are stored in `/images/` directory
3. **Maximum file size** is 1MB for all uploads
4. **Password requirements** minimum 8 characters
5. **Active status** (`aktif`) is commonly used (1 = active, 0 = inactive)
6. **Created dates** are auto-generated by the database
7. **Last login** is updated on successful login
