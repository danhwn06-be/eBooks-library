<?php
class App
{
    // Controller mặc định
    protected $controller = 'HomeController';
    // Method mặc định
    protected $method = 'index';
    // Mảng tham số
    protected $params = [];

    /**
     * Lấy và phân tích URL từ request
     * @return array - Các thành phần của URL
     */
    protected function getUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }

    public function __construct()
    {
        $url = $this->getUrl();

        // 1. Xử lý Controller: Kiểm tra file controller có tồn tại không
        if (isset($url[0]) && file_exists(APP_ROOT . '/app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }

        // Require file controller và khởi tạo đối tượng
        require_once APP_ROOT . '/app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // 2. Xử lý Method: Kiểm tra method có tồn tại trong controller không
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // 3. Xử lý Params: Lấy các tham số còn lại trong URL
        $this->params = $url ? array_values($url) : [];

        // 4. Gọi method trong controller với các tham số tương ứng
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
}
