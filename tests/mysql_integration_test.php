<?php

declare(strict_types=1);

define('BASE_URL', '');

require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/core/helpers.php';
require dirname(__DIR__) . '/core/Model.php';
require dirname(__DIR__) . '/models/User.php';
require dirname(__DIR__) . '/models/Photo.php';
require dirname(__DIR__) . '/models/Comment.php';

function assertMysqlValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected ' . var_export($expected, true) . ', received ' . var_export($actual, true));
    }
}

$database = Database::getConnection();
$userModel = new User($database);
$photoModel = new Photo($database);
$commentModel = new Comment($database);
$startingUserCount = $userModel->countAll();
$testSuffix = bin2hex(random_bytes(5));

$database->beginTransaction();

try {
    $invalidErrors = $userModel->validate([
        'first_name' => 'Name7',
        'last_name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'different',
        'location' => '',
        'description' => '',
        'occupation' => '',
    ]);
    assertMysqlValue(true, count($invalidErrors) >= 4, 'Invalid registration was not rejected.');

    $ownerId = $userModel->create([
        'first_name' => 'Mona',
        'last_name' => 'Ahmed',
        'email' => "owner-{$testSuffix}@example.com",
        'password' => 'correct-password',
        'password_confirmation' => 'correct-password',
        'location' => 'Khartoum',
        'description' => 'Integration test owner',
        'occupation' => 'Student',
    ]);
    $visitorId = $userModel->create([
        'first_name' => 'Sami',
        'last_name' => 'Ali',
        'email' => "visitor-{$testSuffix}@example.com",
        'password' => 'second-password',
        'password_confirmation' => 'second-password',
        'location' => '',
        'description' => '',
        'occupation' => '',
    ]);

    $owner = $userModel->findByEmail("owner-{$testSuffix}@example.com");
    assertMysqlValue(true, $owner !== null, 'Registered MySQL user was not found.');
    assertMysqlValue(true, password_verify('correct-password', (string) $owner['password']), 'Correct password did not verify.');
    assertMysqlValue(false, password_verify('wrong-password', (string) $owner['password']), 'Wrong password verified.');
    assertMysqlValue(null, $userModel->findByEmail("' OR 1=1 --"), 'SQL-like login input changed the prepared query.');

    $photoId = $photoModel->create($ownerId, 'mysql-test.jpg', '<script>Memory</script>', 'Database integration photo');
    assertMysqlValue(true, $photoModel->findById($photoId) !== null, 'MySQL photo detail query failed.');
    assertMysqlValue(true, count($photoModel->findAll()) >= 1, 'MySQL gallery query failed.');
    assertMysqlValue(true, count($photoModel->findLatest(3)) >= 1, 'MySQL latest-photo query failed.');

    assertMysqlValue(['comment' => 'Comment text is required.'], $commentModel->validate('   '), 'Blank comment was accepted.');
    $commentModel->create($photoId, $visitorId, '<script>alert(1)</script>');
    $comments = $commentModel->findByPhotoId($photoId);
    assertMysqlValue(1, count($comments), 'MySQL comment was not stored or joined.');
    assertMysqlValue('&lt;script&gt;alert(1)&lt;/script&gt;', e((string) $comments[0]['comment']), 'Stored script text was not escaped for output.');

    assertMysqlValue(false, $photoModel->deleteOwned($photoId, $visitorId), 'A non-owner deleted a MySQL photo.');
    assertMysqlValue(true, $photoModel->deleteOwned($photoId, $ownerId), 'The owner could not delete a MySQL photo.');
    assertMysqlValue([], $commentModel->findByPhotoId($photoId), 'MySQL cascade did not remove photo comments.');

    $database->rollBack();
} catch (Throwable $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }
    throw $exception;
}

assertMysqlValue($startingUserCount, $userModel->countAll(), 'Integration test data was not rolled back.');

echo "MySQL integration tests passed.\n";
