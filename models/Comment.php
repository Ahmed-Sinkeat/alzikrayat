<?php

declare(strict_types=1);

final class Comment extends Model
{
    public function validate(string $comment): array
    {
        $trimmedComment = trim($comment);

        if ($trimmedComment === '') {
            return ['comment' => 'Comment text is required.'];
        }

        if (mb_strlen($trimmedComment) > 1000) {
            return ['comment' => 'Comment must not exceed 1000 characters.'];
        }

        return [];
    }

    public function findByPhotoId(int $photoId): array
    {
        $statement = $this->database->prepare(
            'SELECT comments.*, users.first_name, users.last_name
             FROM comments
             INNER JOIN users ON users.id = comments.user_id
             WHERE comments.photo_id = :photo_id
             ORDER BY comments.date_time ASC, comments.id ASC'
        );
        $statement->bindValue(':photo_id', $photoId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function create(int $photoId, int $userId, string $comment): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO comments (photo_id, user_id, comment)
             VALUES (:photo_id, :user_id, :comment)'
        );
        $statement->bindValue(':photo_id', $photoId, PDO::PARAM_INT);
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':comment', trim($comment));
        $statement->execute();

        return (int) $this->database->lastInsertId();
    }

    public function countAll(): int
    {
        $statement = $this->database->prepare('SELECT COUNT(*) FROM comments');
        $statement->execute();

        return (int) $statement->fetchColumn();
    }
}
