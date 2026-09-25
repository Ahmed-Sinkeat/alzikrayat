<?php

declare(strict_types=1);

final class User extends Model
{
    public function validate(array $input): array
    {
        $errors = [];
        $namePattern = '/^\p{L}+$/u';
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $passwordConfirmation = $input['password_confirmation'] ?? '';
        $location = trim($input['location'] ?? '');
        $description = trim($input['description'] ?? '');
        $occupation = trim($input['occupation'] ?? '');

        if ($firstName === '' || preg_match($namePattern, $firstName) !== 1 || mb_strlen($firstName) > 50) {
            $errors['first_name'] = 'First name is required, must use letters only, and must not exceed 50 characters.';
        }

        if ($lastName === '' || preg_match($namePattern, $lastName) !== 1 || mb_strlen($lastName) > 50) {
            $errors['last_name'] = 'Last name is required, must use letters only, and must not exceed 50 characters.';
        }

        if ($email === '' || mb_strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address no longer than 100 characters.';
        } elseif ($this->emailExists($email)) {
            $errors['email'] = 'An account with this email address already exists.';
        }

        if (strlen($password) < 8 || strlen($password) > 255) {
            $errors['password'] = 'Password must contain between 8 and 255 characters.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Password confirmation must match the password.';
        }

        if (mb_strlen($location) > 100) {
            $errors['location'] = 'Location must not exceed 100 characters.';
        }

        if (mb_strlen($occupation) > 100) {
            $errors['occupation'] = 'Occupation must not exceed 100 characters.';
        }

        if (mb_strlen($description) > 2000) {
            $errors['description'] = 'Description must not exceed 2000 characters.';
        }

        return $errors;
    }

    public function create(array $input): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (first_name, last_name, email, password, location, description, occupation)
             VALUES (:first_name, :last_name, :email, :password, :location, :description, :occupation)'
        );
        $statement->bindValue(':first_name', trim($input['first_name']));
        $statement->bindValue(':last_name', trim($input['last_name']));
        $statement->bindValue(':email', strtolower(trim($input['email'])));
        $statement->bindValue(':password', password_hash($input['password'], PASSWORD_DEFAULT));
        $statement->bindValue(':location', $this->nullableText($input['location'] ?? ''));
        $statement->bindValue(':description', $this->nullableText($input['description'] ?? ''));
        $statement->bindValue(':occupation', $this->nullableText($input['occupation'] ?? ''));
        $statement->execute();

        return (int) $this->database->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->bindValue(':email', strtolower(trim($email)));
        $statement->execute();
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $statement->bindValue(':email', strtolower(trim($email)));
        $statement->execute();

        return (int) $statement->fetchColumn() > 0;
    }

    public function countAll(): int
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM users');
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    private function nullableText(string $value): ?string
    {
        $trimmedValue = trim($value);

        return $trimmedValue === '' ? null : $trimmedValue;
    }
}
