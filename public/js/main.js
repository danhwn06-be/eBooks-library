document.addEventListener("DOMContentLoaded", function () {
  const images = ['carousel-1.avif', 'carousel-2.avif', 'carousel-3.webp'];

  // Lấy các phần từ cần thiết từ DOM
  const slideImg = document.querySelector(".slide-content img");
  const prevBtn = document.querySelector(".slider-btn.prev");
  const nextBtn = document.querySelector(".slider-btn.next");

  // Cờ theo dõi
  let currentIndex = 0;

  // Thiết lập đường dẫn tới thư mục ảnh
  const basePath = slideImg.src.substring(0, slideImg.src.lastIndexOf("/") + 1);

  // Hàm cập nhật ảnh
  function updateImage() {
    slideImg.src = basePath + images[currentIndex];
    slideImg.style.opacity = 0;
    setTimeout(() => {
      slideImg.style.opacity = 1;
    }, 100);
  }

  // Xử lý sự kiện nút Next
  if (nextBtn) {
    nextBtn.addEventListener("click", function () {
      currentIndex++;
      if (currentIndex >= images.length) {
        currentIndex = 0;
      }
      updateImage();
    });
  }

  // Xử lý sự kiện nút Prev
  if (prevBtn) {
    prevBtn.addEventListener("click", function () {
      currentIndex--;
      if (currentIndex < 0) {
        currentIndex = images.length - 1;
      }
      updateImage();
    });
  }

  // Tự động chạy
  setInterval(() => {
    if (nextBtn) nextBtn.click();
  }, 5000);

  // Chặn người dùng gõ ký tự chữ (chỉ cho gõ số)
  const yearField = document.querySelector('input[name="year"]');
  if (yearField) {
    yearField.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  }
});
// Chức năng hiển thị/ẩn mật khẩu
document.addEventListener("click", function (e) {
  if (e.target.closest(".toggle-password")) {
    const input = e.target
      .closest(".password-group")
      .querySelector("input");

    input.type = input.type === "password" ? "text" : "password";
  }
});

document.querySelector('.login-form').addEventListener('submit', function(e) {
    const account = document.getElementsByName('email')[0].value.trim();
    const password = document.getElementsByName('password')[0].value;
    const confirm = document.getElementsByName('confirm_password')[0].value;
    
    // Regex định dạng
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^(0|84)[3|5|7|8|9][0-9]{8}$/; 
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    let isEmail = emailRegex.test(account);
    let isPhone = phoneRegex.test(account);

    // Validate định dạng
    if (!isEmail && !isPhone) {
        alert("Please enter a valid email address or phone number.");
        e.preventDefault();
        return;
    }
    // Validate độ dài mật khẩu
    if (password.length < 8) {
        alert("Please enter at least 8 characters.");
        e.preventDefault();
        return;
    }
    //  Validate độ phức tạp mật khẩu
    if (!passwordRegex.test(password)) {
        alert("Password must include: uppercase, lowercase, number and special character.");
        e.preventDefault();
        return;
    }
    if (password !== confirm) {
        alert("Confirm password does not match.");
        e.preventDefault();
    }
});