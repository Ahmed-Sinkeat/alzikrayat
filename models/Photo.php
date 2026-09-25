<?php

declare(strict_types=1);

final class Photo extends Model
{
    public function validate(string $title, string $description): array
    {
        $errors = [];
        $trimmedTitle = trim($title);

        if ($trimmedTitle === '' || mb_strlen($trimmedTitle) > 200) {
            $errors['title'] = 'Title is required and must not exceed 200 characters.';
        }

        if (mb_strlen(trim($description)) > 5000) {
            $errors['description'] = 'Description must not exceed 5000 characters.';
        }

        return $errors;
    }

    public function findAll(): array
    {
        $statement = $this->database->prepare(
            'SELECT photos.*, users.first_name, users.last_name, COUNT(comments.id) AS comment_count
             FROM photos
             INNER JOIN users ON users.id = photos.user_id
             LEFT JOIN comments ON comments.photo_id = photos.id
             GROUP BY photos.id, users.first_name, users.last_name
             ORDER BY photos.date_time DESC, photos.id DESC'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findLatest(int $limit = 3): array
    {
        $safeLimit = max(1, min($limit, 12));
        $statement = $this->database->prepare(
            'SELECT photos.*, users.first_name, users.last_name
             FROM photos
             INNER JOIN users ON users.id = photos.user_id
             ORDER BY photos.date_time DESC, photos.id DESC
             LIMIT :photo_limit'
        );
        $statement->bindValue(':photo_limit', $safeLimit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT photos.*, users.first_name, users.last_name
             FROM photos
             INNER JOIN users ON users.id = photos.user_id
             WHERE photos.id = :id
             LIMIT 1'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $photo = $statement->fetch();

        return $photo === false ? null : $photo;
    }

    public function create(int $userId, string $fileName, string $title, string $description): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO photos (user_id, file_name, title, description)
             VALUES (:user_id, :file_name, :title, :description)'
        );
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':file_name', $fileName);
        $statement->bindValue(':title', trim($title));
        $statement->bindValue(':description', trim($description) === '' ? null : trim($description));
        $statement->execute();

        return (int) $this->database->lastInsertId();
    }

    public function deleteOwned(int $id, int $userId): bool
    {
        $statement = $this->database->prepare(
            'DELETE FROM photos WHERE id = :id AND user_id = :user_id'
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() === 1;
    }

    public function countAll(): int
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM photos');
        $statement->execute();

        return (int) $statement->fetchColumn();
    }
}
