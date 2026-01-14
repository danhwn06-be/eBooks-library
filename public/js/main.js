document.addEventListener("DOMContentLoaded", function () {
  const images = [];

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
  });
});
