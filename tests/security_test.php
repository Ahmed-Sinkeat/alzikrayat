<?php

declare(strict_types=1);

define('BASE_URL', '');

require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/core/helpers.php';
require dirname(__DIR__) . '/core/Model.php';
require dirname(__DIR__) . '/models/User.php';
require dirname(__DIR__) . '/models/Photo.php';
require dirname(__DIR__) . '/models/Comment.php';

function assertSecurityValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', received ' . var_export($actual, true));
    }
}

$database = new PDO('sqlite::memory:');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$database->exec('PRAGMA foreign_keys = ON');
$database->exec(
    'CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        location VARCHAR(100),
        description TEXT,
        occupation VARCHAR(100)
    )'
);
$database->exec(
    'CREATE TABLE photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )'
);
$database->exec(
    'CREATE TABLE comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        comment TEXT NOT NULL,
        date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )'
);

$userModel = new User($database);
$photoModel = new Photo($database);
$commentModel = new Comment($database);

$firstUserId = $userModel->create([
    'first_name' => 'Mona',
    'last_name' => 'Ahmed',
    'email' => 'mona@example.com',
    'password' => 'student-pass-1',
    'password_confirmation' => 'student-pass-1',
    'location' => 'Khartoum',
    'description' => '<script>alert(1)</script>',
    'occupation' => 'Student',
]);
$secondUserId = $userModel->create([
    'first_name' => 'Sami',
    'last_name' => 'Ali',
    'email' => 'sami@example.com',
    'password' => 'student-pass-2',
    'password_confirmation' => 'student-pass-2',
    'location' => '',
    'description' => '',
    'occupation' => '',
]);

$storedUser = $userModel->findByEmail('mona@example.com');
assertSecurityValue(true, $storedUser !== null, 'Registered user could not be read.');
assertSecurityValue(true, password_verify('student-pass-1', (string) $storedUser['password']), 'Stored password hash could not be verified.');
assertSecurityValue(false, (string) $storedUser['password'] === 'student-pass-1', 'Raw password was stored.');
assertSecurityValue(null, $userModel->findByEmail("' OR 1=1 --"), 'SQL-like email changed query behavior.');

$photoId = $photoModel->create($firstUserId, 'test.jpg', '<script>title</script>', 'Safe database text');
$commentModel->create($photoId, $secondUserId, '<script>alert(1)</script>');
assertSecurityValue(false, $photoModel->deleteOwned($photoId, $secondUserId), 'A non-owner deleted the photo.');
assertSecurityValue(true, $photoModel->findById($photoId) !== null, 'Photo disappeared after rejected ownership check.');
assertSecurityValue('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'), 'HTML escaping did not neutralize script text.');
assertSecurityValue(true, $photoModel->deleteOwned($photoId, $firstUserId), 'The owner could not delete the photo.');
assertSecurityValue([], $commentModel->findByPhotoId($photoId), 'Cascade delete did not remove comments.');

echo "Security and model tests passed.\n";
