<?php

declare(strict_types=1);

final class PhotoController extends Controller
{
    private const MAX_UPLOAD_BYTES = 5242880;

    private Photo $photoModel;
    private User $userModel;
    private Comment $commentModel;

    public function __construct()
    {
        $this->photoModel = new Photo();
        $this->userModel = new User();
        $this->commentModel = new Comment();
    }

    public function home(): void
    {
        $this->view('home/index', [
            'pageTitle' => 'Share the moments that matter',
            'latestPhotos' => $this->photoModel->findLatest(3),
            'statistics' => [
                'users' => $this->userModel->countAll(),
                'photos' => $this->photoModel->countAll(),
                'comments' => $this->commentModel->countAll(),
            ],
        ]);
    }

    public function about(): void
    {
        $this->view('home/about', ['pageTitle' => 'About Us']);
    }

    public function index(): void
    {
        $this->view('photos/index', [
            'pageTitle' => 'Photo Gallery',
            'photos' => $this->photoModel->findAll(),
        ]);
    }

    public function show(string $id): void
    {
        $photoId = $this->parsePhotoId($id);

        if ($photoId === null) {
            $this->notFound();
            return;
        }

        $photo = $this->photoModel->findById($photoId);

        if ($photo === null) {
            $this->notFound();
            return;
        }

        $commentState = $_SESSION['comment_form'][$photoId] ?? ['errors' => [], 'comment' => ''];
        unset($_SESSION['comment_form'][$photoId]);

        $this->view('photos/show', [
            'pageTitle' => (string) $photo['title'],
            'photo' => $photo,
            'comments' => $this->commentModel->findByPhotoId($photoId),
            'commentErrors' => is_array($commentState['errors'] ?? null) ? $commentState['errors'] : [],
            'oldComment' => (string) ($commentState['comment'] ?? ''),
        ]);
    }

    public function create(): void
    {
        $this->requireAuthentication();
        $this->view('photos/create', [
            'pageTitle' => 'Upload Photo',
            'errors' => [],
            'input' => ['title' => '', 'description' => ''],
        ]);
    }

    public function store(): void
    {
        $this->requireAuthentication();
        $this->requireValidCsrfToken();

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $input = ['title' => $title, 'description' => $description];
        $errors = $this->photoModel->validate($title, $description);
        $upload = isset($_FILES['photo']) && is_array($_FILES['photo']) ? $_FILES['photo'] : [];
        $extension = $this->validateUploadedImage($upload, $errors);

        if ($errors !== []) {
            $this->view('photos/create', [
                'pageTitle' => 'Upload Photo',
                'errors' => $errors,
                'input' => $input,
            ]);
            return;
        }

        $uploadDirectory = ROOT_PATH . '/public/images/uploads';
        if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
            $errors['photo'] = 'The server upload directory is unavailable or not writable.';
            $this->view('photos/create', [
                'pageTitle' => 'Upload Photo',
                'errors' => $errors,
                'input' => $input,
            ]);
            return;
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file((string) $upload['tmp_name'], $destination)) {
            $errors['photo'] = 'The image could not be moved to the uploads directory.';
            $this->view('photos/create', [
                'pageTitle' => 'Upload Photo',
                'errors' => $errors,
                'input' => $input,
            ]);
            return;
        }

        try {
            $photoId = $this->photoModel->create((int) currentUserId(), $fileName, $title, $description);
        } catch (Throwable $exception) {
            unlink($destination);
            throw $exception;
        }

        setFlash('success', 'Your photo was uploaded successfully.');
        $this->redirect('/photo/' . $photoId);
    }

    public function delete(string $id): void
    {
        $this->requireAuthentication();
        $this->requireValidCsrfToken();
        $photoId = $this->parsePhotoId($id);

        if ($photoId === null) {
            $this->notFound();
            return;
        }

        $photo = $this->photoModel->findById($photoId);

        if ($photo === null) {
            $this->notFound();
            return;
        }

        $userId = (int) currentUserId();
        if ((int) $photo['user_id'] !== $userId) {
            http_response_code(403);
            $this->view('errors/403', ['pageTitle' => 'Delete not allowed']);
            return;
        }

        $originalPath = ROOT_PATH . '/public/images/uploads/' . basename((string) $photo['file_name']);
        $temporaryPath = null;

        if (is_file($originalPath)) {
            $temporaryPath = $originalPath . '.deleting-' . bin2hex(random_bytes(6));
            if (!rename($originalPath, $temporaryPath)) {
                setFlash('danger', 'The photo file could not be removed. Nothing was deleted.');
                $this->redirect('/photo/' . $photoId);
            }
        }

        try {
            $deleted = $this->photoModel->deleteOwned($photoId, $userId);
        } catch (Throwable $exception) {
            if ($temporaryPath !== null && is_file($temporaryPath)) {
                rename($temporaryPath, $originalPath);
            }
            throw $exception;
        }

        if (!$deleted) {
            if ($temporaryPath !== null && is_file($temporaryPath)) {
                rename($temporaryPath, $originalPath);
            }
            http_response_code(403);
            $this->view('errors/403', ['pageTitle' => 'Delete not allowed']);
            return;
        }

        if ($temporaryPath !== null && is_file($temporaryPath) && !unlink($temporaryPath)) {
            error_log('Deleted photo metadata, but temporary upload cleanup failed: ' . basename($temporaryPath));
        }

        setFlash('success', 'The photo and its comments were deleted.');
        $this->redirect('/photos');
    }

    private function validateUploadedImage(array $upload, array &$errors): string
    {
        if ($upload === [] || !isset($upload['error']) || (int) $upload['error'] !== UPLOAD_ERR_OK) {
            $errors['photo'] = $this->uploadErrorMessage((int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE));
            return '';
        }

        $size = (int) ($upload['size'] ?? 0);
        $temporaryName = (string) ($upload['tmp_name'] ?? '');

        if ($size < 1 || $size > self::MAX_UPLOAD_BYTES) {
            $errors['photo'] = 'Choose an image no larger than 5 MB.';
            return '';
        }

        if ($temporaryName === '' || !is_uploaded_file($temporaryName)) {
            $errors['photo'] = 'The server did not receive a valid uploaded file.';
            return '';
        }

        $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryName);
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!is_string($mimeType) || !isset($allowedTypes[$mimeType]) || getimagesize($temporaryName) === false) {
            $errors['photo'] = 'Only genuine JPEG, PNG, GIF, or WebP images are accepted.';
            return '';
        }

        return $allowedTypes[$mimeType];
    }

    private function uploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The selected image is larger than the server allows.',
            UPLOAD_ERR_PARTIAL => 'The image upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE => 'Choose an image to upload.',
            default => 'The image could not be uploaded. Please try again.',
        };
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
