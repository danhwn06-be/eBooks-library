<?php
class Cache
{
    private $cacheDir;

    public function __construct()
    {
        // Định nghĩa thư mục chứa file cache
        $this->cacheDir = APP_ROOT . '/app/cache';
        // Tạo thư mục nếu nó chưa tồn tại
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Tạo đường dẫn file cache từ một key.
     * @param string $key Key của cache.
     * @return string Đường dẫn đầy đủ đến file cache.
     */
    private function getCacheFile($key)
    {
        return $this->cacheDir . '/' . sha1($key) . '.cache';
    }

    /**
     * Lấy dữ liệu từ cache.
     * @param string $key Key của cache.
     * @return mixed Dữ liệu đã cache, hoặc false nếu không tìm thấy hoặc đã hết hạn.
     */
    public function get($key)
    {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return false;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);

        // Kiểm tra xem cache đã hết hạn chưa
        if (time() > $data['expires']) {
            // Cache đã hết hạn, xóa file
            unlink($file);
            return false;
        }

        return $data['data'];
    }

    /**
     * Lưu dữ liệu vào cache.
     * @param string $key Key của cache.
     * @param mixed $data Dữ liệu cần cache.
     * @param int $duration Thời gian sống của cache (tính bằng giây). Mặc định 1 giờ.
     */
    public function set($key, $data, $duration = 3600)
    {
        $file = $this->getCacheFile($key);
        $content = [
            'data' => $data,
            'expires' => time() + $duration,
        ];
        file_put_contents($file, serialize($content));
    }

    /**
     * Xóa một mục khỏi cache.
     * @param string $key Key của cache.
     * @return bool True nếu thành công, false nếu thất bại.
     */
    public function delete($key)
    {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    /**
     * Xóa toàn bộ cache.
     */
    public function clear()
    {
        $files = glob($this->cacheDir . '/*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}