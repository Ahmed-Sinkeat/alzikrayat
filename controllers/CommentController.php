<?php

declare(strict_types=1);

final class CommentController extends Controller
{
    private Comment $commentModel;
    private Photo $photoModel;

    public function __construct()
    {
        $this->commentModel = new Comment();
        $this->photoModel = new Photo();
    }

    public function store(string $id): void
    {
        $this->requireAuthentication();
        $this->requireValidCsrfToken();
        $photoId = $this->parsePhotoId($id);

        if ($photoId === null || $this->photoModel->findById($photoId) === null) {
            $this->notFound();
            return;
        }

        $comment = trim((string) ($_POST['comment'] ?? ''));
        $errors = $this->commentModel->validate($comment);

        if ($errors !== []) {
            $_SESSION['comment_form'][$photoId] = [
                'errors' => $errors,
                'comment' => $comment,
            ];
            $this->redirect('/photo/' . $photoId . '#comment-form');
        }

        $this->commentModel->create($photoId, (int) currentUserId(), $comment);
        setFlash('success', 'Your comment was added.');
        $this->redirect('/photo/' . $photoId . '#comments');
    }

    private function parsePhotoId(string $id): ?int
    {
        if ($id === '' || !ctype_digit($id)) {
            return null;
        }

        $photoId = (int) $id;

        return $photoId > 0 ? $photoId : null;
    }
}
