<?php
// Lớp Controller cơ sở (Base Controller)
// Các Controller khác sẽ kế thừa lớp này để gọi Model và View
class Controller
{
    /**
     * Hàm gọi và khởi tạo Model
     * @param string $model Tên file Model (ví dụ: 'BookModel')
     * @return object Đối tượng Model mới được khởi tạo
     */
    public function model($model)
    {
        require_once APP_ROOT . "/app/models/" . $model . ".php";
        return new $model;
    }

    /**
     * Hàm gọi View và truyền dữ liệu để hiển thị
     * @param string $view Đường dẫn file View (ví dụ: 'books/index')
     * @param array $data Mảng dữ liệu cần truyền sang View
     */
    public function view($view, $data = [])
    {
        if (file_exists(APP_ROOT . "/app/views/" . $view . ".php")) {
            require_once APP_ROOT . "/app/views/" . $view . ".php";
        } else {
            die("View does not exist");
        }
    }
}