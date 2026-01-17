<?php

namespace Sulde\Service;

use Intervention\Image\ImageManager;
use Laminas\Diactoros\UploadedFile;

class FileUploader
{
    private string $uploadPath;
    private array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'text/xml',
        'application/xml'
    ];

    public function __construct(string $p_uploadPath)
    {
        $uploadPath=ROOT_PATH.$p_uploadPath;
        if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
            throw new \InvalidArgumentException("Upload path không tồn tại hoặc không ghi được: {$uploadPath}");
        }
        $this->uploadPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param UploadedFile $file
     * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
     */
    public function upload(UploadedFile $file): array
    {
        // Kiểm tra lỗi upload
        $error = $file->getError();
        if ($error !== UPLOAD_ERR_OK) {
            $message = $this->getUploadErrorMessage($error);
            return [
                'success' => false,
                'message' => $message,
                'filename' => null
            ];
        }

        // Kiểm tra định dạng
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return [
                'success' => false,
                'message' => 'File không hợp lệ, chỉ upload file dạng (.jpg,.png,.pdf,.xlsx,.xls).',
                'filename' => null
            ];
        }

        // Lấy tên file gốc, tránh xung đột
//        $originalName = $file->getClientFilename();
//        $filename = uniqid('fav_',true) . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
//        $filename = uniqid('fav_',true);

//        $originalName = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

        // Nếu là ảnh, đổi thành webp
        if (str_starts_with($mimeType, 'image/')) {
            $filename =uniqid('img_',true).'.webp';
        } else {
            // PDF hoặc XML giữ nguyên extension
            $filename =uniqid('file_',true).'.'.$extension;
        }

        try {
            if (str_starts_with($mimeType, 'image/')) {
                // Nếu là ảnh, gọi hàm xử lý ảnh
                $this->processImage($file, $this->uploadPath . $filename);
            } else {
                // Nếu PDF hoặc XML, excel, upload bình thường
                $file->moveTo($this->uploadPath . $filename);
            }

            return [
                'success' => true,
                'message' => 'Upload thành công.',
                'filename' => $filename
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Không thể lưu file: ' . $e->getMessage(),
                'filename' => null
            ];
        }
    }

    /**
     * Xử lý ảnh: resize, crop, tối ưu dung lượng
     */
    private function processImage(UploadedFile $file, string $savePath)
    {
        $tmpPath = $file->getStream()->getMetadata('uri');
        // Sử dụng thư viện Intervention Image
        $manager = new ImageManager(['driver' => 'gd']); // hoặc 'imagick'

        $image = $manager->make($tmpPath);

        // Ví dụ: resize max 1200x1200, giữ tỉ lệ
        $image->resize(1200, 1200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Ví dụ crop trung tâm 800x800 nếu muốn
        //$image->crop(800, 800);

        // Lưu ảnh với chất lượng 85%
//        $image->save($savePath, 85);
        // Lưu ảnh dưới định dạng WebP với chất lượng 85%
        $image->encode('webp', 85)->save($savePath);
    }

    private function getUploadErrorMessage(int $error): string
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File quá lớn (upload_max_filesize)';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File quá lớn (MAX_FILE_SIZE trong form)';
            case UPLOAD_ERR_PARTIAL:
                return 'File chỉ upload một phần';
            case UPLOAD_ERR_NO_FILE:
                return 'Không có file được gửi';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Không có thư mục tạm';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Không ghi được file';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload bị chặn bởi extension';
            default:
                return 'Lỗi upload không xác định';
        }
    }
}